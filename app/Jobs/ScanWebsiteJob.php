<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\LinkReport;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ScanWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $company_id;
    protected $visitedUrls = [];
    protected $urlsToCheck = [];
    protected $originalUrl;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($website)
    {
        $this->url = $website->URL;
        $this->company_id = $website->company_id;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Log::info("Initial memory usage: " . memory_get_usage());

        // Empty arrays before new iteration
        $this->visitedUrls = [];
        $this->urlsToCheck = [];
        // Make it global
        $this->originalUrl = $this->url;

        // Make search-time longer (300 seconds = 5 minutes)
        ini_set('max_execution_time', 500);

        // Scan the initial URL
        $this->scanUrl($this->url);
        Log::info("Memory usage after scanUrl: " . memory_get_usage());
        // Check if the visitedUrls array is empty and store all links if it is
        if (!empty($this->visitedUrls)) {
            foreach ($this->visitedUrls as $visitedUrl => $linkData) {
                // Save to database
                $this->storeLinkReport(
                    $linkData['status_code'],
                    $linkData['error_message'],
                    $linkData['url'],
                    $this->company_id
                );
            }
        }
        Log::info("Final memory usage: " . memory_get_usage());
        Log::info("Url scanned successfully: " . $this->url);
    }

    public function scanUrl($url)
    {
        try {
            // Check if the link has already been visited to avoid infinite loops
            if (isset($this->visitedUrls[$url])) {
                return;
            }

            // Skip LinkedIn URLs
            if (stripos($url, 'linkedin') !== false) {
                return;
            }
            // Skip javascript URLs
            if (stripos($url, 'javascript') !== false) {
                return;
            }

            set_time_limit(20);
            // create new HTTP-client
            $client = new Client();

            // Make an async GET-request to url
            $promise = $client->getAsync($url, ['timeout' => 20]);

            $response = $promise->wait();

            $contentTypeHeader = null;

            // Try to find 'Content-Type' header
            foreach ($response->getHeaders() as $headerName => $headerValue) {
                if (strtolower($headerName) === 'content-type') {
                    $contentTypeHeader = $headerValue[0];
                    break;
                }
            }

            if (!$contentTypeHeader) {
                return;
            }
            // Check if html is in content-type header
            if (stripos($contentTypeHeader, 'html') === false ) {

                $statusCode = $response->getStatusCode();
                $errorMessage = $statusCode !== 200 ? 'Error: ' . $statusCode : null;
                $this->visitedUrls[$url] = [
                    'url' => $url,
                    'status_code' => $statusCode,
                    'error_message' => $errorMessage,
                ];
                return;
            }

            // Get the status code of the URL
            $statusCode = $response->getStatusCode();

            // Store the error message based on the status code
            $errorMessage = $statusCode !== 200 ? 'Error: ' . $statusCode : null;

            // Store the visited URL with its status code and error message
            $this->visitedUrls[$url] = [
                'url' => $url,
                'status_code' => $statusCode,
                'error_message' => $errorMessage,
            ];
            log::info($url . " - " . memory_get_usage());


            // Check if link is valid
            if (!$this->isExternal($this->originalUrl, $url)) {

                $htmlContent = $response->getBody()->getContents();

                // Create a DOM crawler from the HTML content
                $crawler = new Crawler($htmlContent);
                // Get all links on the page and check the status of each link
                $crawler->filter('a')->each(function ($node) use ($url) {
                    $link = $node->attr('href');
                    // Normalize the link to ensure it has a full URL

                    $link = $this->normalizeUrl($link, $this->originalUrl);

                    if (isset($this->urlsToCheck[$link]) || isset($this->visitedUrls[$link])) {
                        return;
                    }

                    // If link is not valid
                    if (!$this->isValidLink($link)) {
                        // If the link is invalid, skip to the next iteration of the loop
                        return;
                    }
                    // Save all links to array if it's not already present
                    $this->urlsToCheck[] = $link;

                });

                log::info("After crawler " . $url . " - " . memory_get_usage());
                // Collect garbage
                unset($htmlContent, $crawler, $promise);
                gc_collect_cycles();

                log::info("After empty " . $url . " - " . memory_get_usage());
                foreach ($this->urlsToCheck as $links) {
                    $this->scanUrl($links);
                }
            }
            return $this->visitedUrls;
        } catch (ClientException $e) {
            Log::info("client " . $url);

            // Handle ClientException, e.g., 4xx HTTP status codes
            $this->visitedUrls[$url] = [
                'url' => $url,
                'status_code' => $e->getResponse()->getStatusCode(),
                'error_message' => $e->getMessage(),
            ];
        } catch (ServerException $e) {
            Log::info("server" . $url);
            // Handle ServerException, e.g., 5xx HTTP status codes
            $this->visitedUrls[$url] = [
                'url' => $url,
                'status_code' => $e->getResponse()->getStatusCode(),
                'error_message' => $e->getMessage(),
            ];
        } catch (ConnectException $e) {
            Log::info("connect" . $url);
            // Handle ConnectException, e.g., network problems
            $this->visitedUrls[$url] = [
                'url' => $url,
                'status_code' => 500,
                'error_message' => $e->getMessage(),
            ];
        } catch (TooManyRedirectsException $e) {
            Log::info("too many" . $url);

            // Handle TooManyRedirectsException, e.g., too many redirects
            $this->visitedUrls[$url] = [
                'url' => $url,
                'status_code' => 300,
                'error_message' => $e->getMessage(),
            ];
        } catch (RequestException $e) {
            // Guzzle RequestException is caught here
            $errorMessage = $e->getMessage(); // Get the error message

            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode(); // Get the HTTP status code
            } else {
                $statusCode = 500; // If there's no response, use a generic HTTP status code
            }
            Log::info("request" . $statusCode . $errorMessage . $url);
            // Add the exception to visitedUrls with status code and error message
            $this->visitedUrls[$url] = [
                'url' => $url,
                'status_code' => $statusCode,
                'error_message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::info("exception" . $url);

            // Other exceptions are caught here
            $this->visitedUrls[$url] = [
                'url' => $url,
                'status_code' => 500, // Use a generic HTTP status code
                'error_message' => $e->getMessage(),
            ];
        }
    }

    private function normalizeUrl($link, $baseUrl)
    {
        // Parse the base URL
        $baseParsed = parse_url($baseUrl);

        // Parse the link URL
        $linkParsed = parse_url($link);

        // If the link is empty, ignore it
        if ($link === '' || strpos($link, '#') === 0 || $link . "/" === $baseUrl) {
            return false;
        }

        // If the link is already a full URL, return it as is
        if (isset($linkParsed['scheme']) && isset($linkParsed['host'])) {
            return $link;
        }

        // If the link has no scheme, use the base URL's scheme
        if (!isset($linkParsed['scheme'])) {
            $linkParsed['scheme'] = $baseParsed['scheme'];
        }

        // If the link has no host, use the base URL's host
        if (!isset($linkParsed['host'])) {
            $linkParsed['host'] = $baseParsed['host'];
        }

        // Handle absolute paths
        if (isset($linkParsed['path']) && substr($linkParsed['path'], 0, 1) === '/') {
            $path = $linkParsed['path'];
        } else {
            // Handle relative paths
            $baseDir = isset($baseParsed['path']) ? rtrim(dirname($baseParsed['path']), '/') : '';
            $path = $baseDir . '/' . ltrim($linkParsed['path'], '/');
        }

        // Rebuild the full URL
        return $linkParsed['scheme'] . '://' . $linkParsed['host'] . '/' . ltrim($path, '/');
    }

    private function isExternal($link, $url)
    {
        // Parse the current link URL
        $parsedLink = parse_url($link);

        // Check if the link is internal
        if (isset($parsedLink['host']) && $parsedLink['host'] === parse_url($url, PHP_URL_HOST)) {
            return false;
        }
        // The link is internal
        return true;
    }

    private function isValidLink($link)
    {
        try {
            // Check if the link should be ignored based on certain criteria
            if (strpos($link, 'mailto:') === 0 || strpos($link, 'tel:') === 0 || $link === '/' || strpos($link, '#') === 0) {
                return false; // Ignore the link
            }

            // Validate the URL using PHP's filter_var function
            if (!filter_var($link, FILTER_VALIDATE_URL)) {
                return false; // Ignore invalid URLs
            }

            // If none of the above criteria are met, consider the link as valid
            return true;
        } catch (\Exception $e) {
            // Handle any exceptions here
            return false; // If something goes wrong, consider the link as invalid
        }
    }

    private function storeLinkReport($statusCode, $errorMessage, $url, $company_id)
    {
        // Validate incoming data
        $validatedData = [
            'status_code' => $statusCode,
            'error_message' => $errorMessage,
            'URL' => $url,
            'company_id' => $company_id,
        ];
        $validator = Validator::make($validatedData, [
            'status_code' => 'required',
            'error_message' => 'nullable',
            'URL' => 'required',
            'company_id' => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP status code for Unprocessable Entity
        }

        // Create a new LinkReport
        $LinkReport = LinkReport::create([
            'Status_code' => $statusCode,
            'Error_message' => $errorMessage,
            'URL' => $url,
            'company_id' => $company_id,
        ]);


        if ($statusCode != 200) {
            log::info("ska skicka mail");
          //  $this->sendEmailNotification($LinkReport);
        }
        return;
    }

    private function sendEmailNotification($LinkReport)
    {
        // Get the recipient email address from the settings or configuration
        $recipientEmail = config('mail.link_error_recipient');

        // Send email notification
        Mail::to($recipientEmail)->send(new \App\Mail\LinkErrorNotification($LinkReport));

        // Send notification to Slack
        $slackWebhookUrl = config('services.slack.webhook_url');
        $client = new Client();
        $client->post($slackWebhookUrl, [
            'json' => [
                'text' => "
                :warning: *Link Error Notification* :warning:\n\n
                *URL:* {$LinkReport->URL}\n
                *Status Code:* {$LinkReport->Status_code}\n
                *Error Message:* {$LinkReport->Error_message}\n\n
                ------------------------------------------------------------------------------------------------\n\n
                "
            ]
        ]);

        // Log the notification
        Log::info('Link error notification sent to email and Slack successfully: ' . $LinkReport->URL);
    }
}
