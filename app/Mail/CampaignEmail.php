<?php

namespace App\Mail;

use App\Models\CampaignRecipient;
use App\Services\Email\EmailTrackingService;
use App\Services\Email\LinkRewriter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class CampaignEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $creative
     * @param  array<string, mixed>  $strategy
     */
    public function __construct(
        public CampaignRecipient $recipient,
        public array $creative,
        public array $strategy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->creative['subject_line'] ?? 'Una propuesta de Roomie',
        );
    }

    /**
     * RFC 8058 one-click unsubscribe. Gmail and Apple Mail render a native
     * unsubscribe button when they see both headers, and POST directly to
     * the URL when the user clicks it.
     */
    public function headers(): Headers
    {
        $tracking = app(EmailTrackingService::class);

        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.$tracking->unsubscribeUrl($this->recipient).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    public function content(): Content
    {
        $tracking = app(EmailTrackingService::class);
        $rewriter = app(LinkRewriter::class);

        $rewrittenBody = $rewriter->rewrite(
            (string) ($this->creative['body_html'] ?? ''),
            $this->recipient,
        );

        return new Content(
            view: 'emails.campaign',
            with: [
                'recipient' => $this->recipient,
                'creative' => $this->creative,
                'strategy' => $this->strategy,
                'rewrittenBody' => $rewrittenBody,
                'openPixelUrl' => $tracking->openPixelUrl($this->recipient),
                'unsubscribeUrl' => $tracking->unsubscribeUrl($this->recipient),
                'ctaUrl' => $tracking->clickUrl($this->recipient, url('/')),
                'hotelName' => $this->strategy['recommended_hotel']['name'] ?? 'Eurostars',
            ],
        );
    }
}
