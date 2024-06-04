<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\sslChecks;
use Illuminate\Support\Facades\Validator;
use App\Mail\SSLCertChecker;
use Spatie\SslCertificate\SslCertificate;
use App\Models\Website;


class SSLController extends Controller
{
    protected $url = "";
    protected $websiteId = "";

    public function ssl_check()
    {

        // Retrieve all websites from the database
        $websites = Website::all();
        // Array to store the result of scanning

        // Loop through each website and send its URL and website_id to the processWebsite function
        foreach ($websites as $website) {
            // Check if the website should be scanned
            if($website->SSL){
                $url = $website->URL;
                $website_id = $website->id;

                // Send URL and website_id to the processWebsite method
                $this->SSLCheck($url, $website_id);
            }
        }

        // Return the result of scanning
        log::info("All websites scanned successfully!!!");
    }


    public function SSLCheck($url, $website_id)
    {
        log::info($url);
        $this->websiteId = $website_id;
        // Get URL from the form and analyze it
        $urlParts = parse_url($url);

        // Extract host from URL
        $host = $urlParts['host'];

        // Create a connection to the server and fetch the SSL certificate
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
            ]
        ]);
        // Try to connect to the server and fetch the SSL certificate
        $stream = stream_socket_client("ssl://{$host}:443", $error_code, $error_message, 30, STREAM_CLIENT_CONNECT, $context);

        // Check if the connection succeeded
        if ($stream === false) {
            // If the connection failed, return an error message
            log::warning("Stream_socket_client failed ");
        }

        // Fetch information about the SSL certificate
        $params = stream_context_get_params($stream);
        $cert = $params['options']['ssl']['peer_certificate'];

        // Get the validity period of the certificate
        $certInfo = openssl_x509_parse($cert);
        $validTo = date('Y-m-d', $certInfo['validTo_time_t']);

        // Create an SSL certificate object for the given host name
        $certificate = SslCertificate::createForHostName($host);
        // Check if the SSL certificate is currently valid
        $valid = $certificate->isValid();

        $this->saveSSLCert($url, $valid, $validTo, $this->websiteId);
        return;
    }

    public function saveSSLCert($url, $valid, $validTo, $websiteId)
    {
        // Validate incoming data
        $validatedData = [
            'URL' => $url,
            'Valid' => $valid,
            'Expiration_date' => $validTo,
            'Websites_id' => $websiteId,
        ];
        $validator = Validator::make($validatedData, [
            'URL' => 'required',
            'Valid' => 'required',
            'Expiration_date' => 'required',
            'Websites_id' => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP status code for Unprocessable Entity
        }

        // Create a new sslCert
        $ssl = sslChecks::create([
            'URL' => $url,
            'Valid' => $valid,
            'Expiration_date' => $validTo,
            'Websites_id' => $websiteId,
        ]);

        $validToTimestamp = strtotime($validTo);
        if($valid == false){
            $mes= "The certificate is not valid.";
            $this->sendMail($url, $websiteId, false, $validTo, $mes);
        }else{
            if ($validToTimestamp !== false) {
                // Calculate the number of days until the certificate expires
                $daysUntilExpiration = floor(($validToTimestamp - time()) / 86400);
                // Send mail when 30 days left
                if ($daysUntilExpiration <100) {
                    $mes= "Informative: The certificate expires in 30 days.";
                    $this->sendMail($url, $websiteId, true, $validTo, $mes);

                } elseif ($daysUntilExpiration < 5) {
                    $mes= "Warning: The certificate expires in less than 5 days.";
                    $this->sendMail($url, $websiteId, true, $validTo, $mes);
                } else{
                    return;
                }
            } else {
                $mes= "Error: Could not read the certificate expiration date.";
                $this->sendMail($url, $websiteId, false, $validTo, $mes);
            }
        }
        return;
    }

    public function sendMail($url, $websiteId, $valid, $validTo, $mes)
    {
        // Send email
        Mail::to('recipient@example.com')->send(new SSLCertChecker($url, $websiteId, $valid, $validTo, $mes));
        // Convert to string for slack
        if($valid === false){
            $valid = "False";
        }elseif($valid === true){
            $valid = "True";
        }else{
            $valid = "NUll";
        }
        // Send Slack notification using your preferred method
        $slackWebhookUrl = 'https://hooks.slack.com/services/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';

        $client = new Client();
        // Slack-message
        $client->post($slackWebhookUrl, [
            'json' => [
                'text' => "
                :warning: *SSL Certificate Notification' : 'SSL Certificate Warning\n\n
                Website URL: $url
                Website ID: $websiteId
                Valid: $valid
                Expiration Date: $validTo
                Message: $mes
                ------------------------------------------------------------------------------------------------\n\n
                "
            ]
        ]);
        return;
    }
}
