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
 * Content builder moodleform.
 *
 * @package    local_contentbuilder
 * @copyright  2026 Eric Davies
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_contentbuilder\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Step 2 form: block builder.
 *
 * All MAX_BLOCKS slots are pre-built server-side so every TinyMCE editor
 * receives a fully initialised context including repository configuration.
 * This is required for the multimedia embed button to function correctly.
 * Using repeat_elements() instead breaks TinyMCE context initialisation.
 *
 * Inline JS (injected via js_init_code in index.php) handles:
 *   - Showing/hiding relevant field groups per block type selection.
 *   - Revealing hidden slots one at a time on "Add another block".
 *   - Scrolling to and focusing the new slot after reveal.
 *
 * Required $customdata keys:
 *   'courseid'   => int             Course ID.
 *   'coursename' => string          Course full name for display.
 *   'sections'   => array           Section number => label map.
 *   'context'    => context_course  Course context for file areas and editors.
 */
class content_form extends \moodleform {

    /** @var int Maximum number of content blocks per push. */
    const MAX_BLOCKS = 10;

    /**
     * Form definition.
     */
    public function definition() {

        $mform      = $this->_form;
        $courseid   = $this->_customdata['courseid'];
        $sections   = $this->_customdata['sections'];
        $coursename = $this->_customdata['coursename'];
        $context    = $this->_customdata['context'];

        $editoropts = [
            'context'   => $context,
            'subdirs'   => false,
            'maxfiles'  => -1,
            'trusttext' => false,
            'noclean'   => false,
        ];

        $fileopts = [
            'subdirs'        => 0,
            'maxfiles'       => 1,
            'accepted_types' => ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'],
            'context'        => $context,
        ];

        $headinglevels = [
            'h3' => get_string('heading_h3', 'local_contentbuilder'),
            'h4' => get_string('heading_h4', 'local_contentbuilder'),
            'h5' => get_string('heading_h5', 'local_contentbuilder'),
            'h6' => get_string('heading_h6', 'local_contentbuilder'),
        ];

        $blocktypes = [
            'text'      => get_string('blocktype_text',      'local_contentbuilder'),
            'imagetext' => get_string('blocktype_imagetext', 'local_contentbuilder'),
            'imagefull' => get_string('blocktype_imagefull', 'local_contentbuilder'),
            'banner'    => get_string('blocktype_banner',    'local_contentbuilder'),
            'callout'   => get_string('blocktype_callout',   'local_contentbuilder'),
            'quote'     => get_string('blocktype_quote',     'local_contentbuilder'),
            'stats'     => get_string('blocktype_stats',     'local_contentbuilder'),
            'steps'     => get_string('blocktype_steps',     'local_contentbuilder'),
            'columns'   => get_string('blocktype_columns',   'local_contentbuilder'),
            'iconlist'  => get_string('blocktype_iconlist',  'local_contentbuilder'),
            'tabs'      => get_string('blocktype_tabs',      'local_contentbuilder'),
            'accordion' => get_string('blocktype_accordion', 'local_contentbuilder'),
            'flashcards'=> get_string('blocktype_flashcards','local_contentbuilder'),
        ];

        // Flashcard options.
        $cardcounts = [
            '1' => get_string('cardcount_1', 'local_contentbuilder'),
            '2' => get_string('cardcount_2', 'local_contentbuilder'),
            '3' => get_string('cardcount_3', 'local_contentbuilder'),
        ];
        $cardcolours = [
            'blue'   => get_string('cardcolour_blue',   'local_contentbuilder'),
            'green'  => get_string('cardcolour_green',  'local_contentbuilder'),
            'purple' => get_string('cardcolour_purple', 'local_contentbuilder'),
            'amber'  => get_string('cardcolour_amber',  'local_contentbuilder'),
        ];

        // Pull quote options.
        $quoteaccents = [
            'blue'   => get_string('quoteaccent_blue',   'local_contentbuilder'),
            'green'  => get_string('quoteaccent_green',  'local_contentbuilder'),
            'amber'  => get_string('quoteaccent_amber',  'local_contentbuilder'),
            'red'    => get_string('quoteaccent_red',    'local_contentbuilder'),
            'purple' => get_string('quoteaccent_purple', 'local_contentbuilder'),
        ];

        // Icon list options.
        $icontypes = [
            'check'   => get_string('icontype_check',   'local_contentbuilder'),
            'arrow'   => get_string('icontype_arrow',   'local_contentbuilder'),
            'star'    => get_string('icontype_star',    'local_contentbuilder'),
            'info'    => get_string('icontype_info',    'local_contentbuilder'),
            'warning' => get_string('icontype_warning', 'local_contentbuilder'),
        ];

        $iconcolours = [
            'blue'  => get_string('iconcolour_blue',  'local_contentbuilder'),
            'green' => get_string('iconcolour_green', 'local_contentbuilder'),
            'amber' => get_string('iconcolour_amber', 'local_contentbuilder'),
            'red'   => get_string('iconcolour_red',   'local_contentbuilder'),
        ];

        // Text columns options.
        $colcounts = [
            '2' => get_string('colcount_2', 'local_contentbuilder'),
            '3' => get_string('colcount_3', 'local_contentbuilder'),
        ];

        $imagelayouts = [
            'left-5'  => get_string('imagelayout_left5',  'local_contentbuilder'),
            'left-4'  => get_string('imagelayout_left4',  'local_contentbuilder'),
            'left-6'  => get_string('imagelayout_left6',  'local_contentbuilder'),
            'left-7'  => get_string('imagelayout_left7',  'local_contentbuilder'),
            'right-5' => get_string('imagelayout_right5', 'local_contentbuilder'),
            'right-4' => get_string('imagelayout_right4', 'local_contentbuilder'),
            'right-6' => get_string('imagelayout_right6', 'local_contentbuilder'),
            'right-7' => get_string('imagelayout_right7', 'local_contentbuilder'),
        ];

        $calloutstyles = [
            'info'    => get_string('callout_info',    'local_contentbuilder'),
            'warning' => get_string('callout_warning', 'local_contentbuilder'),
            'success' => get_string('callout_success', 'local_contentbuilder'),
            'tip'     => get_string('callout_tip',     'local_contentbuilder'),
        ];

        // Hidden fields.
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        // Tracks how many slots are currently visible; incremented by JS.
        $mform->addElement('hidden', 'cb_blockcount', 1);
        $mform->setType('cb_blockcount', PARAM_INT);

        // Course display and section/type selectors.
        $mform->addElement('static', 'courseinfo',
            get_string('choosecourse', 'local_contentbuilder'),
            '<strong>' . s($coursename) . '</strong> &nbsp; ' .
            '<a href="index.php">' . get_string('backtocourse', 'local_contentbuilder') . '</a>');

        $mform->addElement('select', 'sectionnumber',
            get_string('selectsection', 'local_contentbuilder'), $sections);
        $mform->setType('sectionnumber', PARAM_INT);
        $mform->addRule('sectionnumber', null, 'required', null, 'client');

        $mform->addElement('select', 'contenttype',
            get_string('contenttype', 'local_contentbuilder'), [
                'page'           => get_string('type_page',           'local_contentbuilder'),
                'sectionsummary' => get_string('type_sectionsummary', 'local_contentbuilder'),
            ]);
        $mform->setType('contenttype', PARAM_ALPHA);
        $mform->setDefault('contenttype', 'page');
        $mform->addHelpButton('contenttype', 'contenttype', 'local_contentbuilder');

        $mform->addElement('text', 'pagetitle',
            get_string('pagetitle', 'local_contentbuilder'), ['size' => 60]);
        $mform->setType('pagetitle', PARAM_TEXT);
        $mform->addRule('pagetitle', null, 'required', null, 'client');
        $mform->addHelpButton('pagetitle', 'pagetitle', 'local_contentbuilder');

        // Block slots.
        for ($i = 0; $i < self::MAX_BLOCKS; $i++) {

            $wrapstyle = $i > 0 ? 'display:none;' : '';
            $blocklabel = get_string('blocklabel', 'local_contentbuilder', $i + 1);

            $mform->addElement('html',
                '<div id="cb-slot-' . $i . '" class="cb-slot card mb-3" style="' . $wrapstyle . '">'
                . '<div class="card-body">'
                . '<h5 class="card-title text-muted mb-3">' . s($blocklabel) . '</h5>'
            );

            $mform->addElement('select', 'blocktype[' . $i . ']',
                get_string('blocktype', 'local_contentbuilder'), $blocktypes);
            $mform->setType('blocktype[' . $i . ']', PARAM_ALPHA);
            $mform->getElement('blocktype[' . $i . ']')->updateAttributes(['id' => 'blocktype_' . $i]);

            // Heading group.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-heading">');
            $mform->addElement('text', 'heading[' . $i . ']',
                get_string('heading', 'local_contentbuilder'), ['size' => 60, 'id' => 'heading_' . $i]);
            $mform->setType('heading[' . $i . ']', PARAM_TEXT);
            $mform->addElement('select', 'headinglevel[' . $i . ']',
                get_string('headinglevel', 'local_contentbuilder'), $headinglevels);
            $mform->setType('headinglevel[' . $i . ']', PARAM_ALPHA);
            $mform->setDefault('headinglevel[' . $i . ']', 'h3');
            $mform->addElement('html', '</div>');

            // Body editor group.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-body">');
            $mform->addElement('editor', 'bodycontent[' . $i . ']',
                get_string('bodycontent', 'local_contentbuilder'), ['rows' => 8], $editoropts);
            $mform->setType('bodycontent[' . $i . ']', PARAM_CLEANHTML);
            $mform->addElement('html', '</div>');

            // Image upload group.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-image">');
            $mform->addElement('filemanager', 'blockimage[' . $i . ']',
                get_string('blockimage', 'local_contentbuilder'), null, $fileopts);
            $mform->setType('blockimage[' . $i . ']', PARAM_INT);
            $mform->addElement('text', 'imagealt[' . $i . ']',
                get_string('imagealt', 'local_contentbuilder'), ['size' => 60]);
            $mform->setType('imagealt[' . $i . ']', PARAM_TEXT);
            $mform->addElement('text', 'imagecaption[' . $i . ']',
                get_string('imagecaption', 'local_contentbuilder'), ['size' => 60]);
            $mform->setType('imagecaption[' . $i . ']', PARAM_TEXT);
            $mform->addElement('html', '</div>');

            // Image layout group (imagetext blocks only) — combined side + width selector.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-imagepos">');
            $mform->addElement('select', 'imagelayout[' . $i . ']',
                get_string('imagelayout', 'local_contentbuilder'), $imagelayouts);
            $mform->setType('imagelayout[' . $i . ']', PARAM_ALPHANUMEXT);
            $mform->setDefault('imagelayout[' . $i . ']', 'left-5');
            $mform->addElement('html', '</div>');

            // Callout text group.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-callout">');
            $mform->addElement('editor', 'callouttext[' . $i . ']',
                get_string('callouttext', 'local_contentbuilder'), ['rows' => 4], $editoropts);
            $mform->setType('callouttext[' . $i . ']', PARAM_CLEANHTML);
            $mform->addElement('html', '</div>');

            // Callout style group.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-calloutstyle">');
            $mform->addElement('select', 'calloutstyle[' . $i . ']',
                get_string('calloutstyle', 'local_contentbuilder'), $calloutstyles);
            $mform->setType('calloutstyle[' . $i . ']', PARAM_ALPHA);
            $mform->addElement('html', '</div>');

            // Quote group.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-quote">');
            $mform->addElement('textarea', 'quotetext[' . $i . ']',
                get_string('quotetext', 'local_contentbuilder'), ['rows' => 4, 'cols' => 60]);
            $mform->setType('quotetext[' . $i . ']', PARAM_TEXT);
            $mform->addHelpButton('quotetext[' . $i . ']', 'quotetext', 'local_contentbuilder');
            $mform->addElement('text', 'quoteattrib[' . $i . ']',
                get_string('quoteattrib', 'local_contentbuilder'), ['size' => 60]);
            $mform->setType('quoteattrib[' . $i . ']', PARAM_TEXT);
            $mform->addHelpButton('quoteattrib[' . $i . ']', 'quoteattrib', 'local_contentbuilder');
            $mform->addElement('select', 'quoteaccent[' . $i . ']',
                get_string('quoteaccent', 'local_contentbuilder'), $quoteaccents);
            $mform->setType('quoteaccent[' . $i . ']', PARAM_ALPHA);
            $mform->addElement('html', '</div>');

            // Stats group — 4 fixed value/label pairs.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-stats">');
            $mform->addElement('html', '<p class="text-muted small">'
                . get_string('stats_intro', 'local_contentbuilder') . '</p>');
            for ($s = 0; $s < 4; $s++) {
                $mform->addElement('html', '<div class="row mb-2"><div class="col-md-4">');
                $mform->addElement('text', 'statvalue[' . $i . '][' . $s . ']',
                    get_string('statvalue', 'local_contentbuilder') . ' ' . ($s + 1), ['size' => 20]);
                $mform->setType('statvalue[' . $i . '][' . $s . ']', PARAM_TEXT);
                $mform->addElement('html', '</div><div class="col-md-8">');
                $mform->addElement('text', 'statlabel[' . $i . '][' . $s . ']',
                    get_string('statlabel', 'local_contentbuilder') . ' ' . ($s + 1), ['size' => 40]);
                $mform->setType('statlabel[' . $i . '][' . $s . ']', PARAM_TEXT);
                $mform->addElement('html', '</div></div>');
            }
            $mform->addElement('html', '</div>');

            // Steps group — 6 fixed title/description pairs.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-steps">');
            $mform->addElement('html', '<p class="text-muted small">'
                . get_string('steps_intro', 'local_contentbuilder') . '</p>');
            for ($st = 0; $st < 6; $st++) {
                $mform->addElement('html', '<div class="border-start border-3 ps-3 mb-3">');
                $mform->addElement('text', 'steptitle[' . $i . '][' . $st . ']',
                    get_string('steptitle', 'local_contentbuilder') . ' ' . ($st + 1), ['size' => 60]);
                $mform->setType('steptitle[' . $i . '][' . $st . ']', PARAM_TEXT);
                $mform->addElement('text', 'stepdesc[' . $i . '][' . $st . ']',
                    get_string('stepdesc', 'local_contentbuilder') . ' ' . ($st + 1), ['size' => 60]);
                $mform->setType('stepdesc[' . $i . '][' . $st . ']', PARAM_TEXT);
                $mform->addElement('html', '</div>');
            }
            $mform->addElement('html', '</div>');

            // Columns group — colcount selector + 3 pre-built editors (JS hides col3 for 2-col).
            $mform->addElement('html', '<div id="cb-group-' . $i . '-columns">');
            $mform->addElement('select', 'colcount[' . $i . ']',
                get_string('colcount', 'local_contentbuilder'), $colcounts);
            $mform->setType('colcount[' . $i . ']', PARAM_INT);
            $mform->getElement('colcount[' . $i . ']')->updateAttributes(['id' => 'colcount_' . $i]);
            for ($c = 0; $c < 3; $c++) {
                $mform->addElement('html',
                    '<div id="cb-col-' . $i . '-' . $c . '"'
                    . ($c === 2 ? ' style="display:none;"' : '') . '>');
                $mform->addElement('editor', 'colcontent[' . $i . '][' . $c . ']',
                    get_string('colcontent', 'local_contentbuilder') . ' ' . ($c + 1),
                    ['rows' => 8], $editoropts);
                $mform->setType('colcontent[' . $i . '][' . $c . ']', PARAM_CLEANHTML);
                $mform->addElement('html', '</div>');
            }
            $mform->addElement('html', '</div>');

            // Icon list group.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-iconlist">');
            $mform->addElement('textarea', 'iconitems[' . $i . ']',
                get_string('iconitems', 'local_contentbuilder'), ['rows' => 6, 'cols' => 60]);
            $mform->setType('iconitems[' . $i . ']', PARAM_TEXT);
            $mform->addHelpButton('iconitems[' . $i . ']', 'iconitems', 'local_contentbuilder');
            $mform->addElement('select', 'icontype[' . $i . ']',
                get_string('icontype', 'local_contentbuilder'), $icontypes);
            $mform->setType('icontype[' . $i . ']', PARAM_ALPHA);
            $mform->addElement('select', 'iconcolour[' . $i . ']',
                get_string('iconcolour', 'local_contentbuilder'), $iconcolours);
            $mform->setType('iconcolour[' . $i . ']', PARAM_ALPHA);
            $mform->addElement('html', '</div>');

            // Tabs group — 6 pre-built slots, 2 visible by default.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-tabs">');
            $mform->addElement('html', '<p class="text-muted small">'
                . get_string('tabs_intro', 'local_contentbuilder') . '</p>');
            $mform->addElement('hidden', 'tabcount[' . $i . ']', 2);
            $mform->setType('tabcount[' . $i . ']', PARAM_INT);
            for ($t = 0; $t < 6; $t++) {
                $hidden = $t >= 2 ? ' style="display:none;"' : '';
                $mform->addElement('html',
                    '<div id="cb-tab-' . $i . '-' . $t . '" class="border rounded p-3 mb-2"' . $hidden . '>');
                $mform->addElement('text', 'tablabel[' . $i . '][' . $t . ']',
                    get_string('tablabel', 'local_contentbuilder') . ' ' . ($t + 1), ['size' => 40]);
                $mform->setType('tablabel[' . $i . '][' . $t . ']', PARAM_TEXT);
                $mform->addElement('editor', 'tabbody[' . $i . '][' . $t . ']',
                    get_string('tabbody', 'local_contentbuilder') . ' ' . ($t + 1),
                    ['rows' => 6], $editoropts);
                $mform->setType('tabbody[' . $i . '][' . $t . ']', PARAM_CLEANHTML);
                $mform->addElement('html', '</div>');
            }
            $mform->addElement('html',
                '<div class="mb-2"><button type="button" id="cb-add-tab-' . $i . '"'
                . ' class="btn btn-sm btn-outline-secondary">'
                . get_string('addtab', 'local_contentbuilder') . ' (2 of 6)'
                . '</button></div>');
            $mform->addElement('html', '</div>');

            // Accordion group — 8 pre-built slots, 2 visible by default.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-accordion">');
            $mform->addElement('html', '<p class="text-muted small">'
                . get_string('acc_intro', 'local_contentbuilder') . '</p>');
            $mform->addElement('hidden', 'acccount[' . $i . ']', 2);
            $mform->setType('acccount[' . $i . ']', PARAM_INT);
            for ($a = 0; $a < 8; $a++) {
                $hidden = $a >= 2 ? ' style="display:none;"' : '';
                $mform->addElement('html',
                    '<div id="cb-acc-' . $i . '-' . $a . '" class="border rounded p-3 mb-2"' . $hidden . '>');
                $mform->addElement('text', 'acctitle[' . $i . '][' . $a . ']',
                    get_string('acctitle', 'local_contentbuilder') . ' ' . ($a + 1), ['size' => 60]);
                $mform->setType('acctitle[' . $i . '][' . $a . ']', PARAM_TEXT);
                $mform->addElement('editor', 'accbody[' . $i . '][' . $a . ']',
                    get_string('accbody', 'local_contentbuilder') . ' ' . ($a + 1),
                    ['rows' => 5], $editoropts);
                $mform->setType('accbody[' . $i . '][' . $a . ']', PARAM_CLEANHTML);
                $mform->addElement('html', '</div>');
            }
            $mform->addElement('html',
                '<div class="mb-2"><button type="button" id="cb-add-acc-' . $i . '"'
                . ' class="btn btn-sm btn-outline-secondary">'
                . get_string('addaccitem', 'local_contentbuilder') . ' (2 of 8)'
                . '</button></div>');
            $mform->addElement('html', '</div>');

            // Flashcards group — up to 3 cards, count driven by selector.
            $mform->addElement('html', '<div id="cb-group-' . $i . '-flashcards">');
            $mform->addElement('select', 'cardcount[' . $i . ']',
                get_string('cardcount', 'local_contentbuilder'), $cardcounts);
            $mform->setType('cardcount[' . $i . ']', PARAM_INT);
            $mform->setDefault('cardcount[' . $i . ']', 2);
            $mform->getElement('cardcount[' . $i . ']')->updateAttributes(['id' => 'cardcount_' . $i]);
            $mform->addElement('select', 'cardcolour[' . $i . ']',
                get_string('cardcolour', 'local_contentbuilder'), $cardcolours);
            $mform->setType('cardcolour[' . $i . ']', PARAM_ALPHA);
            for ($c = 0; $c < 3; $c++) {
                $hidden = $c >= 2 ? ' style="display:none;"' : '';
                $mform->addElement('html',
                    '<div id="cb-card-' . $i . '-' . $c . '" class="border rounded p-3 mb-2"' . $hidden . '>');
                $mform->addElement('html',
                    '<h6 class="text-muted">' . get_string('cardcount_' . ($c + 1), 'local_contentbuilder') . '</h6>');
                $mform->addElement('text', 'cardfront[' . $i . '][' . $c . ']',
                    get_string('cardfront', 'local_contentbuilder'), ['size' => 60]);
                $mform->setType('cardfront[' . $i . '][' . $c . ']', PARAM_TEXT);
                $mform->addElement('text', 'cardsub[' . $i . '][' . $c . ']',
                    get_string('cardsub', 'local_contentbuilder'), ['size' => 60]);
                $mform->setType('cardsub[' . $i . '][' . $c . ']', PARAM_TEXT);
                $mform->addElement('textarea', 'cardback[' . $i . '][' . $c . ']',
                    get_string('cardback', 'local_contentbuilder'), ['rows' => 4, 'cols' => 60]);
                $mform->setType('cardback[' . $i . '][' . $c . ']', PARAM_TEXT);
                $mform->addElement('html', '</div>');
            }
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '</div></div>');
        }

        $mform->addElement('html',
            '<div class="mb-3">'
            . '<button type="button" id="cb-add-block-btn" class="btn btn-secondary">'
            . get_string('addblock', 'local_contentbuilder') . ' (1 of ' . self::MAX_BLOCKS . ')'
            . '</button></div>'
        );

        // Two submit buttons — standard Moodle save / save-and-display pattern.
        $buttonarray   = [];
        $buttonarray[] = $mform->createElement('submit', 'pushbutton',
            get_string('pushbutton', 'local_contentbuilder'));
        $buttonarray[] = $mform->createElement('submit', 'pushandview',
            get_string('pushbutton_andview', 'local_contentbuilder'));
        $mform->addGroup($buttonarray, 'actionbuttonsgroup', '', [' '], false);
    }
}
