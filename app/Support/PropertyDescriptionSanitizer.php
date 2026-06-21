<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class PropertyDescriptionSanitizer
{
    private const ROOT_ID = '__property_description_root__';

    /**
     * @var array<string, bool>
     */
    private array $allowedTags = [
        'p' => true,
        'br' => true,
        'strong' => true,
        'b' => true,
        'em' => true,
        'i' => true,
        'u' => true,
        's' => true,
        'ul' => true,
        'ol' => true,
        'li' => true,
        'h1' => true,
        'h2' => true,
        'h3' => true,
    ];

    /**
     * @var array<string, bool>
     */
    private array $dangerousTags = [
        'script' => true,
        'style' => true,
        'iframe' => true,
        'object' => true,
        'embed' => true,
        'link' => true,
        'meta' => true,
        'form' => true,
        'input' => true,
        'button' => true,
        'textarea' => true,
        'select' => true,
    ];

    public function sanitize(?string $html): string
    {
        $raw = trim((string) ($html ?? ''));
        if ($raw === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="' . self::ROOT_ID . '">' . $raw . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $this->sanitizeChildren($this->resolveRoot($dom));

        $sanitized = '';
        foreach ($this->resolveRoot($dom)->childNodes as $child) {
            $sanitized .= $dom->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim($sanitized);
    }

    public function hasVisibleContent(?string $html): bool
    {
        $text = html_entity_decode(strip_tags((string) ($html ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);

        return trim($text) !== '';
    }

    private function resolveRoot(DOMDocument $dom): DOMElement
    {
        /** @var DOMElement $root */
        $root = $dom->getElementById(self::ROOT_ID);

        return $root;
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        $children = [];
        foreach ($parent->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            $this->sanitizeNode($child);
        }
    }

    private function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMText) {
            return;
        }

        if (!$node instanceof DOMElement) {
            $node->parentNode?->removeChild($node);
            return;
        }

        $tag = strtolower($node->tagName);

        if (isset($this->dangerousTags[$tag])) {
            $node->parentNode?->removeChild($node);
            return;
        }

        if (!isset($this->allowedTags[$tag])) {
            $this->unwrapElement($node);
            return;
        }

        $this->sanitizeAttributes($node);
        $this->sanitizeChildren($node);
    }

    private function sanitizeAttributes(DOMElement $element): void
    {
        $attributes = [];
        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $attributeName) {
            if ($attributeName === 'style' && $this->supportsTextAlignment($element)) {
                $style = $this->sanitizeAlignmentStyle((string) $element->getAttribute('style'));

                if ($style !== null) {
                    $element->setAttribute('style', $style);
                    continue;
                }
            }

            $element->removeAttribute($attributeName);
        }
    }

    private function supportsTextAlignment(DOMElement $element): bool
    {
        return in_array(strtolower($element->tagName), ['p', 'h1', 'h2', 'h3'], true);
    }

    private function sanitizeAlignmentStyle(string $style): ?string
    {
        if (!preg_match('/text-align\s*:\s*(left|center|right|justify)\s*;?/i', $style, $matches)) {
            return null;
        }

        return 'text-align: ' . strtolower($matches[1]) . ';';
    }

    private function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
