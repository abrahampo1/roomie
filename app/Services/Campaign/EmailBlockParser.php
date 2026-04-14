<?php

namespace App\Services\Campaign;

use DOMDocument;
use DOMNode;

class EmailBlockParser
{
    /**
     * Parse email body_html into an array of typed blocks.
     *
     * @return list<array{id: string, type: string, content?: string, items?: list<string>, image_id?: int, alt?: string, hotel_name?: string, height?: int}>
     */
    public function parse(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $dom = new DOMDocument;
        $wrapped = '<div>'.mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8').'</div>';
        @$dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);

        $root = $dom->getElementsByTagName('div')->item(0);
        if (! $root) {
            return [];
        }

        $blocks = [];
        $counter = 0;

        foreach ($root->childNodes as $node) {
            $block = $this->nodeToBlock($node, $counter);
            if ($block !== null) {
                $blocks[] = $block;
                $counter++;
            }
        }

        return $blocks;
    }

    private function nodeToBlock(DOMNode $node, int $counter): ?array
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = trim($node->textContent);

            return $text !== '' ? $this->makeBlock($counter, 'text-body', ['content' => $text]) : null;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return null;
        }

        $tag = strtolower($node->nodeName);
        $style = $node->getAttribute('style') ?? '';
        $text = trim($node->textContent);

        if ($tag === 'blockquote') {
            $content = str_replace(["\u{201C}", "\u{201D}", '"'], '', $text);

            return $this->makeBlock($counter, 'pull-quote', ['content' => trim($content)]);
        }

        if ($tag === 'table') {
            $items = $this->extractListItems($node);
            if (! empty($items)) {
                return $this->makeBlock($counter, 'highlight-list', ['items' => $items]);
            }

            return null;
        }

        if ($tag === 'img' || ($tag === 'p' && $node->getElementsByTagName('img')->length > 0)) {
            $img = $tag === 'img' ? $node : $node->getElementsByTagName('img')->item(0);
            $src = $img->getAttribute('src') ?? '';
            $alt = $img->getAttribute('alt') ?? '';

            if (preg_match('/\{\{image:(\d+)\}\}/', $src, $m)) {
                return $this->makeBlock($counter, 'image', ['image_id' => (int) $m[1], 'alt' => $alt]);
            }

            return $this->makeBlock($counter, 'image', ['image_id' => 0, 'alt' => $alt]);
        }

        if ($tag === 'div' && $text === '') {
            $height = 20;
            if (preg_match('/height\s*:\s*(\d+)/', $style, $m)) {
                $height = (int) $m[1];
            }

            return $this->makeBlock($counter, 'spacer', ['height' => $height]);
        }

        if ($tag === 'p' || $tag === 'div') {
            if ($this->isDivider($text)) {
                return $this->makeBlock($counter, 'divider');
            }

            if ($this->isCaption($style)) {
                return $this->makeBlock($counter, 'caption', ['content' => $text]);
            }

            if ($this->isSignoff($style)) {
                $clean = preg_replace('/^[\s\x{2014}\-]+/u', '', $text);

                return $this->makeBlock($counter, 'signoff', ['content' => trim($clean)]);
            }

            if ($this->isLead($style)) {
                return $this->makeBlock($counter, 'text-lead', ['content' => $text]);
            }

            return $text !== '' ? $this->makeBlock($counter, 'text-body', ['content' => $text]) : null;
        }

        return $text !== '' ? $this->makeBlock($counter, 'text-body', ['content' => $text]) : null;
    }

    private function makeBlock(int $counter, string $type, array $extra = []): array
    {
        return array_merge(['id' => 'b'.($counter + 1), 'type' => $type], $extra);
    }

    private function isDivider(string $text): bool
    {
        $cleaned = preg_replace('/[\s\x{2014}\-\x{2022}\x{2726}\x{2727}\x{2728}\x{2729}\x{272A}✦·—\-]/u', '', $text);

        return $cleaned === '' && mb_strlen(trim($text)) > 0;
    }

    private function isCaption(string $style): bool
    {
        return str_contains(strtolower($style), 'text-transform') && str_contains(strtolower($style), 'uppercase');
    }

    private function isSignoff(string $style): bool
    {
        $lower = strtolower($style);

        return str_contains($lower, 'font-style') && str_contains($lower, 'italic')
            && str_contains($lower, 'font-size') && preg_match('/font-size\s*:\s*1[45]px/', $lower);
    }

    private function isLead(string $style): bool
    {
        return (bool) preg_match('/font-size\s*:\s*(1[89]|2[0-4])px/', strtolower($style));
    }

    /**
     * @return list<string>
     */
    private function extractListItems(DOMNode $table): array
    {
        $items = [];
        $tds = $table->getElementsByTagName('td');
        for ($i = 0; $i < $tds->length; $i++) {
            $td = $tds->item($i);
            $text = trim($td->textContent);
            $clean = preg_replace('/^[\s\x{2014}\-\x{00A0}&mdash;\s]+/u', '', $text);
            $clean = trim($clean);
            if ($clean !== '') {
                $items[] = $clean;
            }
        }

        return $items;
    }
}
