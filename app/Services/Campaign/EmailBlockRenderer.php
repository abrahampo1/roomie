<?php

namespace App\Services\Campaign;

class EmailBlockRenderer
{
    /**
     * Render an array of blocks into email-safe body_html.
     *
     * Inline styles match CampaignPipeline::creativeDesignGuide() exactly.
     *
     * @param  list<array{type: string, content?: string, items?: list<string>, image_id?: int, alt?: string, height?: int}>  $blocks
     */
    public function render(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $html .= match ($block['type'] ?? '') {
                'text-lead' => $this->renderTextLead($block),
                'text-body' => $this->renderTextBody($block),
                'pull-quote' => $this->renderPullQuote($block),
                'highlight-list' => $this->renderHighlightList($block),
                'caption' => $this->renderCaption($block),
                'image' => $this->renderImage($block),
                'signoff' => $this->renderSignoff($block),
                'divider' => $this->renderDivider(),
                'spacer' => $this->renderSpacer($block),
                default => '',
            };
        }

        return $html;
    }

    private function renderTextLead(array $block): string
    {
        $content = e($block['content'] ?? '');

        return '<p style="margin:0 0 24px;font-family:Georgia,\'Times New Roman\',serif;font-size:20px;line-height:1.5;color:#1a1a2e;">'.$content.'</p>'."\n";
    }

    private function renderTextBody(array $block): string
    {
        $content = e($block['content'] ?? '');

        return '<p style="margin:0 0 20px;font-family:Georgia,\'Times New Roman\',serif;font-size:17px;line-height:1.7;color:#1a1a2eb3;">'.$content.'</p>'."\n";
    }

    private function renderPullQuote(array $block): string
    {
        $content = e($block['content'] ?? '');

        return '<blockquote style="margin:32px 0;padding:4px 0 4px 24px;border-left:2px solid #c8956c;font-family:Georgia,\'Times New Roman\',serif;font-style:italic;font-size:22px;line-height:1.4;color:#1a1a2e;">&ldquo;'.$content.'&rdquo;</blockquote>'."\n";
    }

    private function renderHighlightList(array $block): string
    {
        $items = $block['items'] ?? [];
        $rows = '';
        foreach ($items as $i => $item) {
            $padding = $i < count($items) - 1 ? '0 0 12px 0' : '0';
            $rows .= '<tr><td style="padding:'.$padding.';font-family:Georgia,serif;font-size:17px;line-height:1.6;color:#1a1a2eb3;"><span style="color:#c8956c;">&mdash;&nbsp;</span>'.e($item).'</td></tr>'."\n";
        }

        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">'."\n".$rows.'</table>'."\n";
    }

    private function renderCaption(array $block): string
    {
        $content = e($block['content'] ?? '');

        return '<p style="margin:24px 0 8px;font-family:\'Courier New\',Courier,monospace;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:#1a1a2e66;">'.$content.'</p>'."\n";
    }

    private function renderImage(array $block): string
    {
        $imageId = $block['image_id'] ?? 0;
        $alt = e($block['alt'] ?? '');
        $src = $imageId > 0 ? '{{image:'.$imageId.'}}' : '';

        if (! $src) {
            return '';
        }

        return '<img src="'.$src.'" alt="'.$alt.'" style="display:block;max-width:100%;height:auto;margin:0 0 20px;border-radius:8px;">'."\n";
    }

    private function renderSignoff(array $block): string
    {
        $content = e($block['content'] ?? '');

        return '<p style="margin:28px 0 0;font-family:Georgia,\'Times New Roman\',serif;font-style:italic;font-size:15px;color:#1a1a2e66;">&mdash; '.$content.'</p>'."\n";
    }

    private function renderDivider(): string
    {
        return '<p style="margin:24px 0;text-align:center;font-family:Georgia,serif;font-size:14px;color:#e2d1c3;">&mdash; &#10022; &mdash;</p>'."\n";
    }

    private function renderSpacer(array $block): string
    {
        $height = max(8, min(64, (int) ($block['height'] ?? 20)));

        return '<div style="height:'.$height.'px;"></div>'."\n";
    }
}
