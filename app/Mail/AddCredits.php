<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Email;

class AddCredits extends Mailable
{
    use Queueable, SerializesModels;

    public $words;
    public $minutes;
    public $chars;
    public $dalle_images;
    public $sd_images;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($words, $minutes, $chars, $dalle_images, $sd_images)
    {
        $this->words = $words;
        $this->minutes = $minutes;
        $this->chars = $chars;
        $this->dalle_images = $dalle_images;
        $this->sd_images = $sd_images;

        $current = Email::where('id', 11)->first();
        $this->email = $current;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->email->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.add-credits',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
