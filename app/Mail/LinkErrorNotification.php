<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\LinkReport;
use Illuminate\Support\Facades\Log;


class LinkErrorNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $LinkReport;

    /**
     * Create a new message instance.
     *
     * @param LinkReport $LinkReport
     * @return void
     */
    public function __construct(LinkReport $LinkReport)
    {
        $this->LinkReport = $LinkReport;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $recipientEmail = config('mail.link_error_recipient');

        return $this->subject('Link Error Notification')
                    ->to($recipientEmail)
                    ->view('emails.link-error-notification')
                    ->with([
                        'url' => $this->LinkReport->URL,
                        'statusCode' => $this->LinkReport->Status_code,
                        'errorMessage' => $this->LinkReport->Error_message,
                    ]);
    }
}
