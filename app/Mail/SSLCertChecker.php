<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SSLCertChecker extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $websiteId;
    public $valid;
    public $validTo;
    public $mes;

    /**
     * Create a new mes instance.
     *
     * @param string $url
     * @param int $websiteId
     * @param bool $valid
     * @param string $validTo
     * @param string $mes
     */
    public function __construct($url, $websiteId, $valid, $validTo, $mes)
    {
        $this->url = $url;
        $this->websiteId = $websiteId;
        $this->valid = $valid;
        $this->validTo = $validTo;
        $this->mes = $mes;
    }

    /**
     * Build the mes.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->valid ? 'SSL Certificate Notification' : 'SSL Certificate Warning';

        return $this->subject($subject)
            ->view('emails.ssl-cert-checker')
            ->with([
                'url' => $this->url,
                'websiteId' => $this->websiteId,
                'valid' => $this->valid,
                'validTo' => $this->validTo,
                'mes' => $this->mes,
            ]);
    }
}
