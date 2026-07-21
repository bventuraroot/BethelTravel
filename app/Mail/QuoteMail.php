<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $quote;
    public $subjectStr;
    public $bodyContent;
    protected $pdfData;
    protected $pdfFilename;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Quote $quote, $pdfData, $pdfFilename, $subjectStr, $bodyContent)
    {
        $this->quote = $quote;
        $this->pdfData = $pdfData;
        $this->pdfFilename = $pdfFilename;
        $this->subjectStr = $subjectStr;
        $this->bodyContent = $bodyContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.quote')
            ->subject($this->subjectStr)
            ->with([
                'quote' => $this->quote,
                'bodyContent' => $this->bodyContent,
            ])
            ->attachData($this->pdfData, $this->pdfFilename, [
                'mime' => 'application/pdf',
            ]);
    }
}
