<?php

namespace App\Mail;

use App\Models\Warning;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WarningNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Warning $warning,
        public int $absentCount,
        public ?int $maxAllowed,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'إشعار غياب - ' . ($this->warning->course->course_name ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.warning',
            with: [
                'absentCount' => $this->absentCount,
                'maxAllowed'  => $this->maxAllowed,
            ],
        );
    }
}
