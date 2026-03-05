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
 * English language strings for local_contentbuilder.
 *
 * @package    local_contentbuilder
 * @copyright  2026 University of Glasgow LISU {@link https://www.gla.ac.uk}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin identity.
$string['pluginname']          = 'Section Content Builder';
$string['pluginname_desc']     = 'Build and push styled HTML content into a course page or section summary.';
$string['pushcontent']         = 'Push content to course sections';

// Step 1 — course selector.
$string['selectcourse']        = 'Select a course';
$string['selectcourse_desc']   = 'Choose the course you want to push content into. Only courses where you have editing rights are available.';
$string['choosecourse']        = 'Choose course';
$string['next']                = 'Next: choose section';

// Step 2 — main form.
$string['contenttype']         = 'Add content as';
$string['contenttype_help']    = 'Page creates a new Page activity in the chosen section. Section summary updates the section description directly.';
$string['type_page']           = 'Page activity';
$string['type_sectionsummary'] = 'Section summary';
$string['selectsection']       = 'Section';
$string['pagetitle']           = 'Title';
$string['pagetitle_help']      = 'Used as the page or section name in Moodle.';
$string['backtocourse']        = 'Change course';

// Block structure.
$string['addblock']            = 'Add another block';
$string['blocklabel']          = 'Block {$a}';
$string['blocktype']           = 'Block type';
$string['blocktype_text']      = 'Text';
$string['blocktype_imagetext'] = 'Image + Text';
$string['blocktype_imagefull'] = 'Full-width image';
$string['blocktype_callout']   = 'Callout box';
$string['blocktype_banner']    = 'Banner (full-width image with text)';

// Block fields — text.
$string['heading']             = 'Heading';
$string['heading_help']        = 'Optional heading for this block. Leave blank for no heading.';
$string['headinglevel']        = 'Heading level';
$string['bodycontent']         = 'Body content';
$string['bodycontent_help']    = 'Main content. Use the editor toolbar to add H5P, links, tables, and formatting.';

// Block fields — image.
$string['blockimage']                  = 'Image';
$string['blockimage_help']             = 'Upload an image from your device.';
$string['imagelayout']                 = 'Image position and size';
$string['imagelayout_left4']           = 'Left — Small (image takes roughly one third of the width)';
$string['imagelayout_left5']           = 'Left — Medium (image takes roughly two fifths of the width)';
$string['imagelayout_left6']           = 'Left — Equal split (image and text share the width equally)';
$string['imagelayout_left7']           = 'Left — Large (image takes roughly three fifths of the width)';
$string['imagelayout_right4']          = 'Right — Small (image takes roughly one third of the width)';
$string['imagelayout_right5']          = 'Right — Medium (image takes roughly two fifths of the width)';
$string['imagelayout_right6']          = 'Right — Equal split (image and text share the width equally)';
$string['imagelayout_right7']          = 'Right — Large (image takes roughly three fifths of the width)';
$string['imagecaption']                = 'Caption';
$string['imagecaption_help']   = 'Optional caption displayed below the image.';
$string['imagealt']            = 'Alt text';
$string['imagealt_help']       = 'Describe the image for screen readers. Leave blank if purely decorative.';

// Block fields — callout.
$string['callouttext']         = 'Callout text';
$string['callouttext_help']    = 'Text to display inside the callout box.';
$string['calloutstyle']        = 'Callout style';
$string['callout_info']        = 'Info (blue)';
$string['callout_warning']     = 'Warning (amber)';
$string['callout_success']     = 'Success (green)';
$string['callout_tip']         = 'Tip (teal)';

// Heading levels.
$string['heading_h3']          = 'H3 — Section heading';
$string['heading_h4']          = 'H4 — Sub-heading';
$string['heading_h5']          = 'H5 — Minor heading';
$string['heading_h6']          = 'H6 — Small heading';

// Actions.
$string['pushbutton']          = 'Push to course';
$string['pushbutton_andview']  = 'Push and view course';

// Feedback.
$string['successmsg']          = 'Content pushed successfully to {course}.';
$string['nocourses']           = 'No courses found where you have editing rights.';

// Privacy.
$string['privacy:metadata']    = 'The Section Content Builder plugin does not store any personal data.';

// Block types — group 1 additions.
$string['blocktype_quote']    = 'Pull quote';
$string['blocktype_stats']    = 'Key statistics';
$string['blocktype_steps']    = 'Step-by-step process';
$string['blocktype_columns']  = 'Text columns';
$string['blocktype_iconlist'] = 'Icon bullet list';

// Pull quote fields.
$string['quotetext']          = 'Quote text';
$string['quotetext_help']     = 'The quote or highlighted text to feature.';
$string['quoteattrib']        = 'Attribution';
$string['quoteattrib_help']   = 'Who said it — name, title or source. Leave blank to omit.';
$string['quoteaccent']        = 'Accent colour';
$string['quoteaccent_blue']   = 'Blue';
$string['quoteaccent_green']  = 'Green';
$string['quoteaccent_amber']  = 'Amber';
$string['quoteaccent_red']    = 'Red';
$string['quoteaccent_purple'] = 'Purple';

// Key statistics fields.
$string['stats_intro']        = 'Enter up to 4 statistics. Leave the value blank to omit a statistic.';
$string['statvalue']          = 'Value';
$string['statlabel']          = 'Label';

// Step-by-step fields.
$string['steps_intro']        = 'Enter up to 6 steps. Leave the step title blank to omit a step.';
$string['steptitle']          = 'Step title';
$string['stepdesc']           = 'Step description';

// Text columns fields.
$string['colcount']           = 'Number of columns';
$string['colcount_2']         = '2 columns';
$string['colcount_3']         = '3 columns';
$string['colcontent']         = 'Column';

// Icon bullet list fields.
$string['iconitems']          = 'List items (one per line)';
$string['iconitems_help']     = 'Type each list item on its own line.';
$string['icontype']           = 'Icon style';
$string['icontype_check']     = 'Checkmark ✓';
$string['icontype_arrow']     = 'Arrow →';
$string['icontype_star']      = 'Star ★';
$string['icontype_info']      = 'Info ℹ';
$string['icontype_warning']   = 'Warning ⚠';
$string['iconcolour']         = 'Icon colour';
$string['iconcolour_blue']    = 'Blue';
$string['iconcolour_green']   = 'Green';
$string['iconcolour_amber']   = 'Amber';
$string['iconcolour_red']     = 'Red';

// Block types — group 2 additions.
$string['blocktype_tabs']       = 'Tabs';
$string['blocktype_accordion']  = 'Accordion';
$string['blocktype_flashcards'] = 'Flashcards';

// Tabs fields.
$string['tabs_intro']           = 'Start with 2 tabs. Use the button to add more (up to 6).';
$string['tablabel']             = 'Tab label';
$string['tabbody']              = 'Tab content';
$string['addtab']               = 'Add another tab';

// Accordion fields.
$string['acc_intro']            = 'Start with 2 items. Use the button to add more (up to 8).';
$string['acctitle']             = 'Item title';
$string['accbody']              = 'Item content';
$string['addaccitem']           = 'Add another item';

// Flashcard fields.
$string['cardcount']            = 'Number of cards';
$string['cardcount_1']          = '1 card (full width)';
$string['cardcount_2']          = '2 cards';
$string['cardcount_3']          = '3 cards';
$string['cardcolour']           = 'Card colour';
$string['cardcolour_blue']      = 'Blue';
$string['cardcolour_green']     = 'Green';
$string['cardcolour_purple']    = 'Purple';
$string['cardcolour_amber']     = 'Amber';
$string['cardfront']            = 'Front — title';
$string['cardsub']              = 'Front — subtitle';
$string['cardback']             = 'Back — text';

