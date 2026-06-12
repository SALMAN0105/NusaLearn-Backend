<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherAccessCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $teacher;
    public $accessCode;

    /**
     * Create a new message instance.
     */
    public function __construct($teacher, $accessCode)
    {
        $this->teacher = $teacher;
        $this->accessCode = $accessCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Akses Guru - NusaLearn',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher_access_code',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
