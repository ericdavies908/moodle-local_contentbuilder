<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * HTML builder for local_contentbuilder block output.
 *
 * @package    local_contentbuilder
 * @copyright  2026 Eric Davies
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_contentbuilder;

defined('MOODLE_INTERNAL') || die();

/**
 * Converts an array of block data into a styled HTML string.
 */
class html_builder {

    /**
     * Builds the full HTML output from an array of content blocks.
     *
     * A trailing non-breaking space paragraph is appended so that Moodle's
     * editor always has a clickable insertion point after the last block.
     *
     * @param array $blocks Array of block data arrays, each containing type and type-specific fields.
     * @return string The rendered HTML.
     */
    public static function build(array $blocks): string {
        $html = '';

        foreach ($blocks as $block) {
            switch ($block['type'] ?? 'text') {
                case 'text':
                    $html .= self::render_text($block);
                    break;
                case 'imagetext':
                    $html .= self::render_image_text($block);
                    break;
                case 'imagefull':
                    $html .= self::render_image_full($block);
                    break;
                case 'banner':
                    $html .= self::render_banner($block);
                    break;
                case 'callout':
                    $html .= self::render_callout($block);
                    break;
                case 'quote':
                    $html .= self::render_quote($block);
                    break;
                case 'stats':
                    $html .= self::render_stats($block);
                    break;
                case 'steps':
                    $html .= self::render_steps($block);
                    break;
                case 'columns':
                    $html .= self::render_columns($block);
                    break;
                case 'iconlist':
                    $html .= self::render_iconlist($block);
                    break;
                case 'tabs':
                    $html .= self::render_tabs($block);
                    break;
                case 'accordion':
                    $html .= self::render_accordion($block);
                    break;
                case 'flashcards':
                    $html .= self::render_flashcards($block);
                    break;
            }
        }

        // Trailing editable space so authors can click after the last block
        // when editing the page directly in Moodle's editor.
        $html .= '<p>&nbsp;</p>' . "\n";

        return $html;
    }

    /**
     * Renders a plain text block with optional heading and body content.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if block has no content.
     */
    private static function render_text(array $b): string {
        $body = trim($b['body'] ?? '');
        if (empty($body) && empty(trim($b['heading'] ?? ''))) {
            return '';
        }
        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        if (!empty($body)) {
            $html .= $body . "\n";
        }
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders an image-and-text block using Bootstrap 5 responsive grid columns.
     *
     * Uses Bootstrap row/col classes (always available in Moodle 4+) rather than
     * table or float layout. This ensures full accessibility compliance — layout
     * tables cause screen readers to announce spurious table structure, which fails
     * WCAG 1.3.1. The Bootstrap grid stacks to full width on mobile and sits
     * side by side on medium+ screens.
     *
     * The combined imagelayout value encodes both side and Bootstrap column count,
     * e.g. 'left-5' means image on the left taking 5 of 12 columns (text gets 7).
     * Valid image column counts: 4, 5, 6, 7. Text column = 12 - image column.
     *
     * For right-positioned images, order-md-2 / order-md-1 visually swap columns
     * on desktop while preserving logical DOM order (text first) on mobile, so
     * keyboard and screen reader order is always content-first.
     *
     * @param array $b Block data.
     * @return string Rendered HTML.
     */
    private static function render_image_text(array $b): string {
        // Parse combined layout value e.g. 'left-5' into side and column count.
        $layout  = $b['imagelayout'] ?? 'left-5';
        $parts   = explode('-', $layout, 2);
        $side    = ($parts[0] ?? 'left') === 'right' ? 'right' : 'left';
        $imgcols = in_array((int)($parts[1] ?? 5), [4, 5, 6, 7], true)
                       ? (int)$parts[1] : 5;
        $txtcols = 12 - $imgcols;

        $imghtml = self::img_tag($b, 'width:100%;height:auto;display:block;');
        $body    = trim($b['body'] ?? '');
        $caption = self::caption($b);

        // On mobile both cols stack full width in DOM order (text first, image second).
        // On md+ screens right-positioned images swap visually via Bootstrap order classes.
        $imgorder = $side === 'right' ? ' order-md-2' : '';
        $txtorder = $side === 'right' ? ' order-md-1' : '';

        // Padding is applied directly to columns rather than using Bootstrap's g-*
        // gutter class. Gutters work by adding negative margins to .row which causes
        // horizontal overflow inside Moodle's content area, producing a scrollbar.
        $padding = $side === 'right' ? 'padding-right:1.5rem;' : 'padding-left:1.5rem;';

        $html  = '<div style="margin-bottom:3em;">' . "\n";
        // mx-0 zeroes Bootstrap's default negative row margins (--bs-gutter-x)
        // which cause horizontal overflow inside Moodle's content area.
        $html .= '<div class="row mx-0">' . "\n";

        // Text column — heading sits inside the column so it stays grouped with body.
        $html .= '<div class="col-12 col-md-' . $txtcols . $txtorder . '">' . "\n";
        $html .= self::heading($b);
        $html .= $body . "\n";
        $html .= '</div>' . "\n";

        // Image column — padding creates the gap without negative-margin overflow.
        $html .= '<div class="col-12 col-md-' . $imgcols . $imgorder . '" style="' . $padding . '">' . "\n";
        $html .= $imghtml;
        $html .= $caption;
        $html .= '</div>' . "\n";

        $html .= '</div>' . "\n"; // end .row
        $html .= '</div>' . "\n"; // end .mb-4
        return $html;
    }

    /**
     * Renders a full-width image block.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if block has no content.
     */
    private static function render_image_full(array $b): string {
        $imghtml = self::img_tag($b, 'width:100%;height:auto;display:block;');
        if (empty($imghtml) && empty(trim($b['heading'] ?? ''))) {
            return '';
        }
        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        $html .= $imghtml . "\n";
        $html .= self::caption($b);
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a banner block — full-width image followed by optional heading and body text.
     *
     * Image appears first at full width. Caption appears directly below the image.
     * Heading and body text follow beneath, allowing the banner to introduce a section.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if block has no content.
     */
    private static function render_banner(array $b): string {
        $imghtml = self::img_tag($b, 'width:100%;height:auto;display:block;');
        $body    = trim($b['body'] ?? '');
        $caption = self::caption($b);

        if (empty($imghtml) && empty(trim($b['heading'] ?? '')) && empty($body)) {
            return '';
        }

        $html  = '<div style="margin-bottom:3em;">' . "
";
        $html .= $imghtml;
        $html .= $caption;
        if (!empty($imghtml) && (!empty(trim($b['heading'] ?? '')) || !empty($body))) {
            $html .= '<div style="padding-top:0.75em;">' . "
";
            $html .= self::heading($b);
            if (!empty($body)) {
                $html .= $body . "
";
            }
            $html .= '</div>' . "
";
        } else {
            $html .= self::heading($b);
            if (!empty($body)) {
                $html .= $body . "
";
            }
        }
        $html .= '</div>' . "
";
        return $html;
    }

    /**
     * Renders a styled callout box block.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if block has no content.
     */
    private static function render_callout(array $b): string {
        $stylemap = [
            'info'    => ['background:#e8f4fd;border-left:5px solid #3498db;color:#1a5276;', '&#8505; '],
            'warning' => ['background:#fef9e7;border-left:5px solid #f39c12;color:#7d6608;', '&#9888; '],
            'success' => ['background:#eafaf1;border-left:5px solid #27ae60;color:#1e8449;', '&#10003; '],
            'tip'     => ['background:#e8f8f5;border-left:5px solid #1abc9c;color:#148f77;', '&#9998; '],
        ];
        $key = $b['calloutstyle'] ?? 'info';
        [$boxstyle, $icon] = $stylemap[$key] ?? $stylemap['info'];
        $text = trim($b['callouttext'] ?? '');

        if (empty($text) && empty(trim($b['heading'] ?? ''))) {
            return '';
        }

        $html  = '<div style="' . $boxstyle . 'padding:1em 1.25em;border-radius:3px;margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        if (!empty($text)) {
            $html .= '<p style="margin:0;">' . $icon . $text . '</p>' . "\n";
        }
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a pull quote block with accent border and optional attribution.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no quote text.
     */
    private static function render_quote(array $b): string {
        $text = trim($b['quotetext'] ?? '');
        if (empty($text)) {
            return '';
        }
        $attrib  = trim($b['quoteattrib'] ?? '');
        $colours = [
            'blue'   => '#3498db',
            'green'  => '#27ae60',
            'amber'  => '#f39c12',
            'red'    => '#e74c3c',
            'purple' => '#8e44ad',
        ];
        $colour = $colours[$b['quoteaccent'] ?? 'blue'] ?? $colours['blue'];

        $html  = '<blockquote style="border-left:5px solid ' . $colour . ';'
               . 'margin:0 0 3em 0;padding:1.5em 2em;background:#f9f9f9;">' . "\n";
        $html .= '<p style="font-size:1.2em;font-style:italic;margin:0 0 0.5em 0;">'
               . s($text) . '</p>' . "\n";
        if (!empty($attrib)) {
            $html .= '<footer style="font-size:0.9em;color:#666;">&#8212; '
                   . s($attrib) . '</footer>' . "\n";
        }
        $html .= '</blockquote>' . "\n";
        return $html;
    }

    /**
     * Renders a key statistics block — up to 4 large figures in a Bootstrap row.
     *
     * Empty stat pairs are silently skipped so authors can use 1–4 stats freely.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no stats provided.
     */
    private static function render_stats(array $b): string {
        $stats = [];
        for ($i = 0; $i < 4; $i++) {
            $val   = trim($b['statvalue'][$i] ?? '');
            $label = trim($b['statlabel'][$i] ?? '');
            if (!empty($val)) {
                $stats[] = ['val' => $val, 'label' => $label];
            }
        }
        if (empty($stats)) {
            return '';
        }
        $col = 'col-12 col-sm-6 col-md-' . (12 / min(count($stats), 4));

        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        $html .= '<div class="row mx-0 text-center">' . "\n";
        foreach ($stats as $s) {
            $html .= '<div class="' . $col . ' mb-3" style="padding:1em 0.5em;">' . "\n";
            $html .= '<div style="font-size:3em;font-weight:700;line-height:1;letter-spacing:-0.02em;">'
                   . s($s['val']) . '</div>' . "\n";
            if (!empty($s['label'])) {
                $html .= '<div style="font-size:0.9em;color:#666;margin-top:0.5em;line-height:1.4;">'
                       . s($s['label']) . '</div>' . "\n";
            }
            $html .= '</div>' . "\n";
        }
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a numbered step-by-step process block — up to 6 steps.
     *
     * Steps with an empty title are silently skipped.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no steps provided.
     */
    private static function render_steps(array $b): string {
        $steps = [];
        for ($i = 0; $i < 6; $i++) {
            $title = trim($b['steptitle'][$i] ?? '');
            $desc  = trim($b['stepdesc'][$i]  ?? '');
            if (!empty($title)) {
                $steps[] = ['title' => $title, 'desc' => $desc];
            }
        }
        if (empty($steps)) {
            return '';
        }

        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        foreach ($steps as $n => $step) {
            $num   = $n + 1;
            $html .= '<div style="display:flex;align-items:flex-start;margin-bottom:1.75em;">' . "\n";
            $html .= '<div style="flex-shrink:0;width:2.5em;height:2.5em;border-radius:50%;font-size:1.05em;'
                   . 'background:#3498db;color:#fff;display:flex;align-items:center;'
                   . 'justify-content:center;font-weight:700;font-size:1em;margin-right:1em;">'
                   . $num . '</div>' . "\n";
            $html .= '<div style="flex:1;">' . "\n";
            $html .= '<strong>' . s($step['title']) . '</strong>' . "\n";
            if (!empty($step['desc'])) {
                $html .= '<p style="margin:0.25em 0 0 0;color:#555;">'
                       . s($step['desc']) . '</p>' . "\n";
            }
            $html .= '</div>' . "\n";
            $html .= '</div>' . "\n";
        }
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a two or three column text block using Bootstrap grid.
     *
     * mx-0 prevents negative row margins causing horizontal scrollbar.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no column content.
     */
    private static function render_columns(array $b): string {
        $count = in_array((int)($b['colcount'] ?? 2), [2, 3], true)
                     ? (int)$b['colcount'] : 2;
        $cols  = [];
        for ($i = 0; $i < $count; $i++) {
            $raw  = $b['colcontent'][$i] ?? '';
            $text = is_array($raw) ? ($raw['text'] ?? '') : $raw;
            if (!empty(trim($text))) {
                $cols[] = $text;
            }
        }
        if (empty($cols)) {
            return '';
        }
        $colclass = $count === 3 ? 'col-12 col-md-4' : 'col-12 col-md-6';

        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        $html .= '<div class="row mx-0">' . "\n";
        foreach ($cols as $col) {
            $html .= '<div class="' . $colclass . '" style="padding:0 1.25em 0 0;">' . "\n";
            $html .= $col . "\n";
            $html .= '</div>' . "\n";
        }
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a polished icon bullet list block.
     *
     * Each item has a coloured circular badge containing the icon, separated
     * from the text by comfortable spacing. Items sit on a lightly shaded
     * background strip with a left accent border for visual cohesion.
     *
     * Items are entered one per line in a plain textarea.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no items provided.
     */
    private static function render_iconlist(array $b): string {
        $raw = trim($b['iconitems'] ?? '');
        if (empty($raw)) {
            return '';
        }
        $items = array_filter(array_map('trim', explode("\n", $raw)));
        if (empty($items)) {
            return '';
        }

        $icons = [
            'check'   => '&#10003;',
            'arrow'   => '&#8594;',
            'star'    => '&#9733;',
            'info'    => '&#8505;',
            'warning' => '&#9888;',
        ];

        // Each colour has a main colour, a light background tint, and a border tint.
        $palettes = [
            'blue'  => ['main' => '#2980b9', 'bg' => '#eaf4fb', 'border' => '#aed6f1'],
            'green' => ['main' => '#27ae60', 'bg' => '#eafaf1', 'border' => '#a9dfbf'],
            'amber' => ['main' => '#d68910', 'bg' => '#fef9e7', 'border' => '#f9e79f'],
            'red'   => ['main' => '#c0392b', 'bg' => '#fdedec', 'border' => '#f1948a'],
        ];

        $icon    = $icons[$b['icontype'] ?? 'check'] ?? $icons['check'];
        $palette = $palettes[$b['iconcolour'] ?? 'blue'] ?? $palettes['blue'];
        $main    = $palette['main'];
        $bg      = $palette['bg'];
        $border  = $palette['border'];

        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        $html .= '<ul style="list-style:none;padding:0;margin:0;">' . "\n";

        foreach ($items as $item) {
            $html .= '<li style="'
                   . 'display:flex;'
                   . 'align-items:center;'
                   . 'background:' . $bg . ';'
                   . 'border-left:4px solid ' . $main . ';'
                   . 'border-radius:4px;'
                   . 'padding:0.75em 1em;'
                   . 'margin-bottom:0.5em;'
                   . '">' . "\n";

            // Icon badge — filled circle with white icon inside.
            $html .= '<span style="'
                   . 'display:inline-flex;'
                   . 'align-items:center;'
                   . 'justify-content:center;'
                   . 'flex-shrink:0;'
                   . 'width:2em;height:2em;'
                   . 'border-radius:50%;'
                   . 'background:' . $main . ';'
                   . 'color:#fff;'
                   . 'font-size:0.9em;'
                   . 'font-weight:700;'
                   . 'margin-right:0.85em;'
                   . '">' . $icon . '</span>' . "\n";

            // Item text.
            $html .= '<span style="font-size:1em;color:#2c3e50;">'
                   . s($item) . '</span>' . "\n";

            $html .= '</li>' . "\n";
        }

        $html .= '</ul>' . "\n";
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a Bootstrap 5 tabs block.
     *
     * Tab JS is already loaded by Moodle's Boost theme so no extra JS needed.
     * Unique IDs generated from a hash prevent conflicts when multiple tab
     * blocks appear on the same page. Empty tab labels are skipped.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no tabs provided.
     */
    private static function render_tabs(array $b): string {
        $tabs = [];
        for ($i = 0; $i < 6; $i++) {
            $label = trim($b['tablabel'][$i] ?? '');
            $raw   = $b['tabbody'][$i] ?? '';
            $body  = is_array($raw) ? ($raw['text'] ?? '') : $raw;
            if (!empty($label)) {
                $tabs[] = ['label' => $label, 'body' => $body];
            }
        }
        if (empty($tabs)) {
            return '';
        }

        $uid = 'cbtab' . substr(md5($b['heading'] . count($tabs)), 0, 8);

        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);

        // Nav tabs.
        $html .= '<ul class="nav nav-tabs mb-0" role="tablist" '
               . 'style="border-bottom:2px solid #dee2e6;">' . "\n";
        foreach ($tabs as $t => $tab) {
            $active  = $t === 0 ? 'active' : '';
            $ariasel = $t === 0 ? 'true' : 'false';
            $html .= '<li class="nav-item" role="presentation">'
                   . '<button class="nav-link ' . $active . '"'
                   . ' id="' . $uid . '-tab-' . $t . '"'
                   . ' data-bs-toggle="tab"'
                   . ' data-bs-target="#' . $uid . '-pane-' . $t . '"'
                   . ' type="button" role="tab"'
                   . ' aria-controls="' . $uid . '-pane-' . $t . '"'
                   . ' aria-selected="' . $ariasel . '"'
                   . ' style="font-weight:600;padding:0.65em 1.3em;">'
                   . s($tab['label'])
                   . '</button></li>' . "\n";
        }
        $html .= '</ul>' . "\n";

        // Tab panels.
        $html .= '<div class="tab-content" style="border:1px solid #dee2e6;border-top:none;'
               . 'padding:1.75em 2em;border-radius:0 0 6px 6px;background:#fff;">' . "\n";
        foreach ($tabs as $t => $tab) {
            $active = $t === 0 ? 'show active' : '';
            $html .= '<div class="tab-pane fade ' . $active . '"'
                   . ' id="' . $uid . '-pane-' . $t . '"'
                   . ' role="tabpanel"'
                   . ' aria-labelledby="' . $uid . '-tab-' . $t . '"'
                   . ' tabindex="0">'
                   . (!empty($tab['body']) ? $tab['body'] : '')
                   . '</div>' . "\n";
        }
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a Bootstrap 5 accordion block.
     *
     * Accordion JS is already loaded by Moodle's Boost theme. First item is
     * open by default. Empty titles are skipped silently.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no items provided.
     */
    private static function render_accordion(array $b): string {
        $items = [];
        for ($i = 0; $i < 8; $i++) {
            $title = trim($b['acctitle'][$i] ?? '');
            $raw   = $b['accbody'][$i] ?? '';
            $body  = is_array($raw) ? ($raw['text'] ?? '') : $raw;
            if (!empty($title)) {
                $items[] = ['title' => $title, 'body' => $body];
            }
        }
        if (empty($items)) {
            return '';
        }

        $uid  = 'cbacc' . substr(md5($b['heading'] . count($items)), 0, 8);

        $html  = '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        $html .= '<div class="accordion accordion-flush" id="' . $uid . '" '
               . 'style="border:1px solid #dee2e6;border-radius:6px;overflow:hidden;">' . "\n";

        foreach ($items as $n => $item) {
            $collapsed = $n === 0 ? '' : ' collapsed';
            $expanded  = $n === 0 ? 'true' : 'false';
            $show      = $n === 0 ? ' show' : '';
            $iid       = $uid . '-item-' . $n;

            $html .= '<div class="accordion-item" '
                   . 'style="border:none;' . ($n > 0 ? 'border-top:1px solid #dee2e6;' : '') . '">' . "\n";
            $html .= '<h2 class="accordion-header" id="' . $iid . '-hdr">'
                   . '<button class="accordion-button' . $collapsed . '"'
                   . ' type="button" data-bs-toggle="collapse"'
                   . ' data-bs-target="#' . $iid . '-body"'
                   . ' aria-expanded="' . $expanded . '"'
                   . ' aria-controls="' . $iid . '-body"'
                   . ' style="font-weight:600;font-size:1em;padding:1.1em 1.5em;">'
                   . s($item['title'])
                   . '</button></h2>' . "\n";
            $html .= '<div id="' . $iid . '-body"'
                   . ' class="accordion-collapse collapse' . $show . '"'
                   . ' aria-labelledby="' . $iid . '-hdr"'
                   . ' data-bs-parent="#' . $uid . '">'
                   . '<div class="accordion-body" style="padding:1.25em 1.75em;line-height:1.7;">'
                   . (!empty($item['body']) ? $item['body'] : '')
                   . '</div></div>' . "\n";
            $html .= '</div>' . "\n";
        }

        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Renders a flashcard block — 1, 2 or 3 cards that flip on hover.
     *
     * Pure CSS flip using perspective and rotateY — no JavaScript required.
     * Front: coloured background with white title and subtitle.
     * Back: white background with coloured border — softer and easier to read.
     * Cards stack to full width on mobile. Keyboard accessible via tabindex
     * and focus-within so the flip works without a mouse.
     *
     * @param array $b Block data.
     * @return string Rendered HTML or empty string if no cards provided.
     */
    private static function render_flashcards(array $b): string {
        $count = in_array((int)($b['cardcount'] ?? 2), [1, 2, 3], true)
                     ? (int)$b['cardcount'] : 2;

        $palettes = [
            'blue'   => ['main' => '#2980b9', 'border' => '#2980b9', 'light' => '#eaf4fb'],
            'green'  => ['main' => '#27ae60', 'border' => '#27ae60', 'light' => '#eafaf1'],
            'purple' => ['main' => '#8e44ad', 'border' => '#8e44ad', 'light' => '#f5eef8'],
            'amber'  => ['main' => '#d68910', 'border' => '#d68910', 'light' => '#fef9e7'],
        ];
        $palette = $palettes[$b['cardcolour'] ?? 'blue'] ?? $palettes['blue'];

        $cards = [];
        for ($i = 0; $i < $count; $i++) {
            $front = trim($b['cardfront'][$i] ?? '');
            $sub   = trim($b['cardsub'][$i]   ?? '');
            $back  = trim($b['cardback'][$i]   ?? '');
            if (!empty($front) || !empty($back)) {
                $cards[] = ['front' => $front, 'sub' => $sub, 'back' => $back];
            }
        }
        if (empty($cards)) {
            return '';
        }

        $colclass = count($cards) === 1 ? 'col-12'
                  : (count($cards) === 2 ? 'col-12 col-md-6' : 'col-12 col-md-4');

        $main   = $palette['main'];
        $border = $palette['border'];

        // Inline scoped CSS for the CSS flip — no external stylesheet needed.
        $css = '<style>'
             . '.cb-card-wrap{perspective:1000px;height:220px;cursor:pointer;}'
             . '.cb-card-inner{position:relative;width:100%;height:100%;'
             .   'transition:transform 0.55s cubic-bezier(.4,0,.2,1);'
             .   'transform-style:preserve-3d;}'
             . '.cb-card-wrap:hover .cb-card-inner,'
             . '.cb-card-wrap:focus-within .cb-card-inner{transform:rotateY(180deg);}'
             . '.cb-card-front,.cb-card-back{'
             .   'position:absolute;width:100%;height:100%;'
             .   'backface-visibility:hidden;-webkit-backface-visibility:hidden;'
             .   'border-radius:8px;display:flex;flex-direction:column;'
             .   'align-items:center;justify-content:center;'
             .   'padding:1.5em;text-align:center;box-sizing:border-box;}'
             . '.cb-card-back{transform:rotateY(180deg);}'
             . '.cb-flip-hint{position:absolute;bottom:0.6em;right:0.8em;'
             .   'font-size:0.7em;opacity:0.5;}'
             . '</style>' . "\n";

        $html  = $css;
        $html .= '<div style="margin-bottom:3em;">' . "\n";
        $html .= self::heading($b);
        $html .= '<div class="row mx-0" style="gap:1.25em;">' . "\n";

        foreach ($cards as $card) {
            $html .= '<div class="' . $colclass . ' p-0" style="margin-bottom:0;">' . "\n";
            $html .= '<div class="cb-card-wrap" tabindex="0">'
                   . '<div class="cb-card-inner">' . "\n";

            // Front — solid colour, white text.
            $html .= '<div class="cb-card-front" style="background:' . $main . ';color:#fff;">'
                   . '<div style="font-size:1.25em;font-weight:700;line-height:1.3;margin-bottom:0.4em;">'
                   . s($card['front']) . '</div>';
            if (!empty($card['sub'])) {
                $html .= '<div style="font-size:0.9em;opacity:0.88;">' . s($card['sub']) . '</div>';
            }
            $html .= '<span class="cb-flip-hint">hover to reveal &#8635;</span>'
                   . '</div>' . "\n";

            // Back — white with coloured border.
            $html .= '<div class="cb-card-back" style="background:#fff;color:#2c3e50;'
                   . 'border:3px solid ' . $border . ';'
                   . 'box-shadow:0 4px 18px rgba(0,0,0,0.08);">'
                   . '<div style="font-size:0.98em;line-height:1.6;">'
                   . nl2br(s($card['back']))
                   . '</div></div>' . "\n";

            $html .= '</div></div>' . "\n"; // inner, wrap
            $html .= '</div>' . "\n";       // col
        }

        $html .= '</div>' . "\n"; // row
        $html .= '</div>' . "\n"; // outer
        return $html;
    }

    /**
     * Renders a heading element at the author-chosen level (h3–h6).
     *
     * @param array $b Block data.
     * @return string Rendered heading HTML or empty string if no heading text.
     */
    private static function heading(array $b): string {
        $text = trim($b['heading'] ?? '');
        if (empty($text)) {
            return '';
        }
        $level = in_array($b['headinglevel'] ?? 'h3', ['h3', 'h4', 'h5', 'h6'], true)
                    ? $b['headinglevel'] : 'h3';
        return '<' . $level . '>' . s($text) . '</' . $level . '>' . "\n";
    }

    /**
     * Renders an img element for the given block's image URL.
     *
     * @param array $b Block data.
     * @param string $style Inline CSS style string for the img element.
     * @return string Rendered img HTML or empty string if no URL is set.
     */
    private static function img_tag(array $b, string $style = ''): string {
        $url = trim($b['imageurl'] ?? '');
        if (empty($url)) {
            return '';
        }
        return '<img src="' . s($url) . '" alt="' . s($b['imagealt'] ?? '') . '" style="' . $style . '" />' . "\n";
    }

    /**
     * Renders an image caption paragraph.
     *
     * @param array $b Block data.
     * @return string Rendered caption HTML or empty string if no caption is set.
     */
    private static function caption(array $b): string {
        $cap = trim($b['imagecaption'] ?? '');
        if (empty($cap)) {
            return '';
        }
        return '<p style="font-size:0.85em;color:#666;margin:0.3em 0 0 0;">' . s($cap) . '</p>' . "\n";
    }
}
