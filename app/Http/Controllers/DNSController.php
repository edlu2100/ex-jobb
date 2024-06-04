<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Website;
use App\Models\dnsChecks;
use Illuminate\Support\Facades\Mail;
use App\Mail\DNSCheckFailed;
use GuzzleHttp\Client;
use Iodev\Whois\Factory;

class DNSController extends Controller
{
    protected $website_id= [];
    protected $originalUrl = [];

    public function dns_scan() {
        // Retrieve all websites from the database
        $websites = Website::all();

        // Loop through each website and send its URL and website_id to the processWebsite function
        foreach ($websites as $website) {
            // Check if the website should be scanned
            if($website->DNS){
                $url = $website->URL;
                $website_id = $website->id;

                // Send URL and website_id to the processWebsite method
                $this->checkDns($url, $website_id);
            }
        }

        // Return the result of scanning
        Log::info("All websites scanned successfully!!!");
    }

    public function checkDns($url, $website_id)
    {
        try {
            $this->website_id = $website_id;
            $this->originalUrl = $url;
            $parsed_url = parse_url($url);

            $domain = $parsed_url['host'];

            // Get DNS records for the domain
            $dnsRecords = $this->getDnsRecords($domain);

            $dns = json_decode($dnsRecords->getContent());
            $dnsChecks = [
                'URL' => $this->originalUrl,
                'Websites_id' => $this->website_id,
                'Dns_records' => $dns->status,
                'NS_servers' => null,
                'Error_message' => $dns->errorMessage
            ];

            $this->storeDNSRecords($dnsChecks);

            // Get Who is information for the domain
            $whois = Factory::get()->createWhois();
            $info = $whois->loadDomainInfo($domain);
            $nsServers = $info->nameServers;

            // Array to store connection status and error message for NS servers
            $nsConnectionStatus = [];

            // Loop through each NS server and test the connection
            foreach ($nsServers as $ns) {
                // Test the connection to the NS server and store the result
                $nsConnectionStatus = $this->testConnectionToNSServers($ns);

                // Decode the JSON response to an associative array
                $nsStatus = json_decode($nsConnectionStatus->getContent());
                $nsData = [
                    'URL' => $this->originalUrl,
                    'Websites_id' => $this->website_id,
                    'Dns_records' => null,
                    'NS_servers' => $nsStatus->status,
                    'Error_message' => $nsStatus->errorMessage
                ];
                $this->storeDNSRecords($nsData);
            }
            return;
        } catch (\Exception $e) {
            Log::error('Error checking DNS records: ' . $e->getMessage());
            $dnsChecks = [
                'URL' => $this->originalUrl,
                'Websites_id' => $this->website_id,
                'Dns_records' => null,
                'NS_servers' => null,
                'Error_message' => $e->getMessage()
            ];
            $this->storeDNSRecords($dnsChecks);
            return;
        }
    }

    private function getDnsRecords($domain)
    {
        try {
            $dnsRecords = [];

            // Get the DNS records for the domain
            $records = dns_get_record($domain, DNS_A);
            // Check if $records is false, indicating an error
            if ($records === false) {
                return response()->json([
                    'status' => 'false',
                    'errorMessage' => 'Failed to retrieve DNS records for domain: '. $domain
                ]);
            }

            // Organize the records by type
            foreach ($records as $record) {
                $type = $record['type'];
                $dnsRecords[$type][] = $record;
            }

            // Check if an A record exists for each IP address
            foreach ($dnsRecords['A'] as $record) {
                $ipAddress = $record['ip'];
                $ipExists = $this->ipExists($ipAddress);

                // If the IP address does not exist, log a warning message
                if (!$ipExists) {
                    Log::warning('IP address not found: ' . $ipAddress);
                    return response()->json([
                        'status' => 'false',
                        'errorMessage' => 'IP address not found: '. $domain,
                    ]);
                }
            }
            return response()->json([
                'status' => 'true',
                'errorMessage' => '',
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving DNS records: ' . $e->getMessage());
            return response()->json([
                'status' => 'false',
                'errorMessage' => 'Error retrieving DNS records: ' . $e->getMessage(),
            ]);
        }
    }

    private function ipExists($ipAddress)
    {
        if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            // Check if an A record exists for the specified IP address
            return checkdnsrr($ipAddress, 'A');
        } else {
            // Invalid IP address
            return false;
        }
    }

    private function testConnectionToNSServers($ns)
    {
        // Open name server port 53
        $sock = fsockopen($ns, 53);
        // Check if the connection was successful
        if (!is_resource($sock)) {
            Log::warning('Failed to connect to NS server: ' . $ns);
            return response()->json([
                'status' => 'false',
                'errorMessage' => 'Failed to connect to NS server: ' . $ns
            ]);
        }
        fclose($sock);
        return response()->json([
            'status' => 'true',
            'errorMessage' => ''
        ]);
    }

    private function storeDNSRecords($dnsChecks)
    {
        try {
            Log::info($dnsChecks);

            $Dns_records = "";
            if ($dnsChecks['Dns_records'] === null) {
                $Dns_records = null;
            }
            if ($dnsChecks['NS_servers'] === null) {
                $NS_records = null;
            }
            if ($dnsChecks['Dns_records'] === true) {
                $Dns_records = 1;
            }

            if ($dnsChecks['Dns_records'] === 'false') {
                $Dns_records = 0;
            }

            if ($dnsChecks['NS_servers'] === 'true') {
                $NS_records = 1;
            }
            if ($dnsChecks['NS_servers'] === 'false') {
                $NS_records = 0;
            }

            // Create a new DNSChecks record.
            $dnsChecks = dnsChecks::create([
                'URL' => $dnsChecks['URL'],
                'Websites_id' => $dnsChecks['Websites_id'],
                'DNS_records' => $Dns_records,
                'NS_servers' => $NS_records,
                'Error_message' => $dnsChecks['Error_message'],
            ]);

            // Check if dns_records or NS_servers is not working
            if ($dnsChecks['Dns_records'] === null || $dnsChecks['NS_servers'] === null) {
                $this->SendEmailAndSlack($dnsChecks);
            }
            return;

        } catch (\Exception $e) {
            // Log error message...
            Log::error('Error storing DNS records: ' . $e->getMessage());
        }
    }

    public function sendEmailAndSlack($dnsChecks)
    {
        // Check if dns_records or NS_servers is not true
        if ($dnsChecks['Dns_records'] === null) {
            // Send email
            Mail::to('recipient@example.com')->send(new DNSCheckFailed($dnsChecks));

            // Send Slack notification using your preferred method
            $slackWebhookUrl = config('services.slack.webhook_url');

            $client = new Client();

            $client->post($slackWebhookUrl, [
                'json' => [
                    'text' => "
                    :warning: *DNS Error Notification* :warning:\n\n
                    *URL:* {$dnsChecks['URL']}\n
                    *Website id:* {$dnsChecks['Websites_id']}\n
                    *DNS from dns_get_record not working* \n
                    *Error Message:* {$dnsChecks['Error_message']}\n\n
                    ------------------------------------------------------------------------------------------------\n\n
                    "
                ]
            ]);
            // Log the notification
            Log::info('DNS check failed. Email and Slack notification sent.');
        }
        // Check if dns_records or NS_servers is not true
        if ($dnsChecks['NS_servers'] === null) {
            // Send email
            Mail::to('recipient@example.com')->send(new DNSCheckFailed($dnsChecks));

            // Send Slack notification using your preferred method
            $slackWebhookUrl = config('services.slack.webhook_url');
            $client = new Client();
            $client->post($slackWebhookUrl, [
                'json' => [
                    'text' => "
                    :warning: *DNS Error Notification* :warning:\n\n
                    *URL:* {$dnsChecks['URL']}\n
                    *Website id:* {$dnsChecks['Websites_id']}\n
                    *Name server from whois is not working* \n
                    *Error Message:* {$dnsChecks['Error_message']}\n\n
                    ------------------------------------------------------------------------------------------------\n\n
                    "
                ]
            ]);
            // Log the notification
            Log::info('DNS check failed. Email and Slack notification sent.');
        }
    }
}
