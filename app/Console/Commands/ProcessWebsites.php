<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WebsiteController;

class ProcessWebsites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-websites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process websites';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Create new instance of Website-controller
        $websiteController = new WebsiteController();

        // Call processWebsites to scan websites
        $websiteController->processWebsites();

        // Send message when process is done
        $this->info('Websites processed successfully.');

        return 0;
    }
}
