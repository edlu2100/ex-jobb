<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\dnsChecks;
use Illuminate\Support\Facades\Log;

class DNSCheckFailed extends Mailable
{
    use Queueable, SerializesModels;

    public $dnsChecks;

    /**
     * Create a new message instance.
     * 
     * @param dnsChecks $dnsChecks
     * @return void
     */
    public function __construct(dnsChecks $dnsChecks)
    {
        $this->dnsChecks = $dnsChecks;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $recipientEmail = config('mail.link_error_recipient');
        return $this->subject('Dns error notification')
            ->to($recipientEmail)
            ->view('emails.dns-check-failed')
            ->with([
                'URL' => $this->dnsChecks['URL'],
                'Websites_id' => $this->dnsChecks->Websites_id,
                'DNS_records' => $this->dnsChecks->Dns_records,
                'NS_servers' => $this->dnsChecks->NS_servers,
                'Error_message' => $this->dnsChecks->Error_message,
            ]);
                
    }
}

