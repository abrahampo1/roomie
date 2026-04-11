<?php

namespace App\Services\Email;

use App\Models\CampaignRecipient;
use DOMDocument;
use DOMXPath;

/**
 * Rewrites every trackable `<a href="...">` in an email body so it points
 * through our click-tracking redirect, preserving the original target as a
 * base64-encoded query parameter. Uses DomDocument (a PHP built-in) instead
 * of regex so inline styles, mailto:, tel:, anchors and Blade placeholders
 * are all handled correctly.
 */
class LinkRewriter
{
    public function __construct(
        private readonly EmailTrackingService $tracking,
    ) {}

    public function rewrite(string $bodyHtml, CampaignRecipient $recipient): string
    {
        if (trim($bodyHtml) === '') {
            return $bodyHtml;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><div id="roomie-wrap">'.$bodyHtml.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//a[@href]') as $anchor) {
            $href = $anchor->getAttribute('href');

            if ($this->isNonTrackable($href)) {
                continue;
            }

            $anchor->setAttribute('href', $this->tracking->clickUrl($recipient, $href));

            $currentRel = trim($anchor->getAttribute('rel'));
            $newRel = trim(($currentRel !== '' ? $currentRel.' ' : '').'noopener nofollow');
            $anchor->setAttribute('rel', $newRel);
        }

        $wrap = $dom->getElementById('roomie-wrap');
        if ($wrap === null) {
            return $bodyHtml;
        }

        $inner = '';
        foreach ($wrap->childNodes as $child) {
            $inner .= $dom->saveHTML($child);
        }

        return $inner;
    }

    private function isNonTrackable(string $href): bool
    {
        return $href === ''
            || str_starts_with($href, '#')
            || str_starts_with($href, 'mailto:')
            || str_starts_with($href, 'tel:')
            || str_starts_with($href, 'javascript:')
            || str_starts_with($href, '{{');
    }
}
