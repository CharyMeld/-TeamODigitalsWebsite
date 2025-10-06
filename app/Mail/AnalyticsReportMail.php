<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class AnalyticsReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $period;
    public $excelFile;
    public $filename;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $period, $excelFile, $filename)
    {
        $this->data = $data;
        $this->period = $period;
        $this->excelFile = $excelFile;
        $this->filename = $filename;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Analytics Report - ' . ucfirst($this->period) . ' - ' . Carbon::now()->format('M d, Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.analytics-report',
            with: [
                'data' => $this->data,
                'period' => $this->period,
                'generatedAt' => $this->data['generated_at']
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->excelFile, $this->filename)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}

