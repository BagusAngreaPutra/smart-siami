<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SiamiNotificationMail extends Mailable
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $targetUrl,
        public readonly string $institution,
        public readonly string $recipientName,
        public readonly string $notificationType,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SMART SIAMI] '.$this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.audit-notification',
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
