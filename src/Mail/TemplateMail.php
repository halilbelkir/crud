<?php

namespace crudPackage\Mail;

use crudPackage\Support\MailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $template;

    public function __construct(
        public string $key,
        public array $values = [],
        public ?string $url = null,
    )
    {
        $this->template = MailTemplates::render($key, $values);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'crudPackage::emails.template',
        );
    }
}
