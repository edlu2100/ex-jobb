<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Company;
use App\Models\Website;
use App\Models\LinkReport;
use Illuminate\Support\Facades\Mail;
use App\Mail\LinkErrorNotification;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use App\Jobs\ScanWebsiteJob; // Lägg till denna rad



class WebsiteController extends Controller
{
    protected $visitedUrls = [];
    protected $urlsToCheck = [];
    protected $originalUrl;
    protected $company_id= [];

    // Method to retrieve all websites and send their URLs one by one to the scanUrl function
    public function processWebsites()
    {
        // Retrieve all websites from the database
        $websites = Website::all();

        // Loop through each website and dispatch a job to process it
        foreach ($websites as $website) {
            // Check if the website should be scanned
            if ($website->Scan) {
                // Dispatch the job to scan the website
                ScanWebsiteJob::dispatch($website)->onQueue('web-scans');
                gc_collect_cycles();
            }
        }

        // Return the result of scanning
        Log::info("All websites scanned successfully!!!");
    }



}

