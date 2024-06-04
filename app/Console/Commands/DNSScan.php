<?php



namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DNSController;

class DNSScan extends Command
{
    protected $signature = 'app:dns_scan';

    protected $description = 'Scan websites for DNS checks';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $dnsController = new DNSController();
        $dnsController->dns_scan();

        $this->info('Websites scanned successfully!');
    }
}
