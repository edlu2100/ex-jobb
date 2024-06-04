<?php



namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SSLController;

class SSLCheck extends Command
{
    protected $signature = 'app:ssl_Check';

    protected $description = 'Scan websites for SSL checks';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sslController = new SSLController();
        $sslController->ssl_check();

        $this->info('Websites scanned successfully!');
    }
}

