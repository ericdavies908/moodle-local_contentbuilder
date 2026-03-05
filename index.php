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
 * Main controller for local_contentbuilder.
 *
 * Step 1: No courseid supplied — renders the course picker.
 * Step 2: courseid supplied — loads sections server-side and renders the block builder form.
 *
 * On form submission, pushes the assembled HTML to the chosen target:
 *   page           — creates a new Page activity in the chosen section.
 *   sectionsummary — updates the section summary HTML directly.
 *
 * @package    local_contentbuilder
 * @copyright  2026 University of Glasgow LISU {@link https://www.gla.ac.uk}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/course/lib.php');

// ── Parameters ────────────────────────────────────────────────────────────────
$courseid = optional_param('courseid', 0, PARAM_INT);
$context  = $courseid ? context_course::instance($courseid) : context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/contentbuilder/index.php',
    $courseid ? ['courseid' => $courseid] : []));
$PAGE->set_title(get_string('pluginname', 'local_contentbuilder'));
$PAGE->set_heading(get_string('pluginname', 'local_contentbuilder'));
$PAGE->set_pagelayout('standard');
require_login();

// ══════════════════════════════════════════════════════════════════════════════
// STEP 1 — Course picker
// ══════════════════════════════════════════════════════════════════════════════
if (!$courseid) {
    $courses = get_user_capability_course('local/contentbuilder:pushcontent', $USER->id, true, 'fullname');

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_contentbuilder'));
    echo '<p>' . get_string('selectcourse_desc', 'local_contentbuilder') . '</p>';

    if (empty($courses)) {
        echo $OUTPUT->notification(get_string('nocourses', 'local_contentbuilder'), 'warning');
        echo $OUTPUT->footer();
        exit;
    }

    echo '<form method="get" action="' .
         (new moodle_url('/local/contentbuilder/index.php'))->out(false) . '">';
    echo '<div class="form-group row">';
    echo '<label class="col-sm-3 col-form-label" for="courseid">' .
         get_string('choosecourse', 'local_contentbuilder') . '</label>';
    echo '<div class="col-sm-6">';
    echo '<select name="courseid" id="courseid" class="form-control custom-select">';
    echo '<option value="">— ' . get_string('choosecourse', 'local_contentbuilder') . ' —</option>';
    foreach ($courses as $c) {
        echo '<option value="' . (int)$c->id . '">' . s($c->fullname) . '</option>';
    }
    echo '</select></div></div>';
    echo '<div class="form-group row"><div class="col-sm-6 offset-sm-3">';
    echo '<button type="submit" class="btn btn-primary">' .
         get_string('next', 'local_contentbuilder') . '</button>';
    echo '</div></div></form>';
    echo $OUTPUT->footer();
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 2 — Block builder
// ══════════════════════════════════════════════════════════════════════════════
require_capability('local/contentbuilder:pushcontent', $context);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

$sectionrecords = $DB->get_records('course_sections',
    ['course' => $courseid], 'section ASC', 'section, name');

$sectionoptions = [];
foreach ($sectionrecords as $sec) {
    $label = !empty(trim($sec->name))
        ? trim($sec->name)
        : get_string('section') . ' ' . $sec->section;
    $sectionoptions[$sec->section] = $label;
}
if (empty($sectionoptions)) {
    $sectionoptions[0] = get_string('section') . ' 0';
}

$customdata = [
    'courseid'   => $courseid,
    'coursename' => $course->fullname,
    'sections'   => $sectionoptions,
    'context'    => $context,
];

$form = new local_contentbuilder\form\content_form(null, $customdata);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/contentbuilder/index.php'));
}

// Detect which submit button was pressed before get_data() processes the form.
// 'pushandview' present means the author wants to go straight to the course page.
$pushandview = optional_param('pushandview', false, PARAM_BOOL);

if ($data = $form->get_data()) {

    $blockcount    = min((int)($data->cb_blockcount ?? 1), 10);
    $contenttype   = $data->contenttype ?? 'page';
    $sectionnumber = (int)$data->sectionnumber;
    $title         = trim($data->pagetitle ?? '');
    $now           = time();
    $fs            = get_file_storage();

    $blocks = [];
    for ($i = 0; $i < $blockcount; $i++) {

        // Resolve uploaded image.
        $imageurl    = '';
        $draftitemid = $data->blockimage[$i] ?? 0;
        if (!empty($draftitemid)) {
            file_save_draft_area_files(
                $draftitemid,
                $context->id,
                'local_contentbuilder',
                'blockimage',
                $courseid * 1000 + $i,
                ['subdirs' => 0, 'maxfiles' => 1]
            );
            $files = $fs->get_area_files(
                $context->id,
                'local_contentbuilder',
                'blockimage',
                $courseid * 1000 + $i,
                'filename',
                false
            );
            if (!empty($files)) {
                $file     = reset($files);
                $imageurl = moodle_url::make_pluginfile_url(
                    $context->id,
                    'local_contentbuilder',
                    'blockimage',
                    $courseid * 1000 + $i,
                    '/',
                    $file->get_filename()
                )->out(false);
            }
        }

        $bodyraw     = $data->bodycontent[$i] ?? '';
        $body        = is_array($bodyraw) ? ($bodyraw['text'] ?? '') : $bodyraw;

        $calloutraw  = $data->callouttext[$i] ?? '';
        $callouttext = is_array($calloutraw) ? ($calloutraw['text'] ?? '') : $calloutraw;

        // Collect column editor content — each value may be an editor array or plain string.
        $colcontent = [];
        for ($c = 0; $c < 3; $c++) {
            $colraw = $data->colcontent[$i][$c] ?? '';
            $colcontent[$c] = is_array($colraw) ? ($colraw['text'] ?? '') : $colraw;
        }

        // Collect stats and steps — simple nested arrays from fixed form fields.
        $statvalue = $data->statvalue[$i] ?? [];
        $statlabel = $data->statlabel[$i] ?? [];
        $steptitle = $data->steptitle[$i] ?? [];
        $stepdesc  = $data->stepdesc[$i]  ?? [];

        $blocks[] = [
            'type'          => $data->blocktype[$i]      ?? 'text',
            'heading'       => $data->heading[$i]        ?? '',
            'headinglevel'  => $data->headinglevel[$i]   ?? 'h3',
            'body'          => $body,
            'imageurl'      => $imageurl,
            'imagealt'      => $data->imagealt[$i]       ?? '',
            'imagecaption'  => $data->imagecaption[$i]   ?? '',
            'imagelayout'   => $data->imagelayout[$i]    ?? 'left-5',
            'callouttext'   => $callouttext,
            'calloutstyle'  => $data->calloutstyle[$i]   ?? 'info',
            // Group 1 new block fields.
            'quotetext'     => $data->quotetext[$i]      ?? '',
            'quoteattrib'   => $data->quoteattrib[$i]    ?? '',
            'quoteaccent'   => $data->quoteaccent[$i]    ?? 'blue',
            'statvalue'     => $statvalue,
            'statlabel'     => $statlabel,
            'steptitle'     => $steptitle,
            'stepdesc'      => $stepdesc,
            'colcount'      => $data->colcount[$i]       ?? 2,
            'colcontent'    => $colcontent,
            'iconitems'     => $data->iconitems[$i]      ?? '',
            'icontype'      => $data->icontype[$i]       ?? 'check',
            'iconcolour'    => $data->iconcolour[$i]     ?? 'blue',
            // Group 2 — tabs, accordion, flashcards.
            'tabcount'      => (int)($data->tabcount[$i]   ?? 2),
            'tablabel'      => $data->tablabel[$i]       ?? [],
            'tabbody'       => array_map(
                function($v) { return is_array($v) ? ($v['text'] ?? '') : $v; },
                (array)($data->tabbody[$i] ?? [])
            ),
            'acccount'      => (int)($data->acccount[$i]   ?? 2),
            'acctitle'      => $data->acctitle[$i]       ?? [],
            'accbody'       => array_map(
                function($v) { return is_array($v) ? ($v['text'] ?? '') : $v; },
                (array)($data->accbody[$i] ?? [])
            ),
            'cardcount'     => (int)($data->cardcount[$i]  ?? 2),
            'cardcolour'    => $data->cardcolour[$i]     ?? 'blue',
            'cardfront'     => $data->cardfront[$i]      ?? [],
            'cardsub'       => $data->cardsub[$i]        ?? [],
            'cardback'      => $data->cardback[$i]       ?? [],
        ];
    }

    $html = local_contentbuilder\html_builder::build($blocks);

    switch ($contenttype) {

        // ── Section summary ───────────────────────────────────────────────────
        case 'sectionsummary':
            $sectionrecord = $DB->get_record('course_sections',
                ['course' => $courseid, 'section' => $sectionnumber], '*', MUST_EXIST);
            $upd = new stdClass();
            $upd->id            = $sectionrecord->id;
            $upd->summary       = $html;
            $upd->summaryformat = FORMAT_HTML;
            $upd->timemodified  = $now;
            if (!empty($title)) {
                $upd->name = $title;
            }
            course_update_section($course, $sectionrecord, $upd);
            rebuild_course_cache($courseid, true);
            break;

        // ── Page activity — direct DB insert ─────────────────────────────────
        case 'page':
        default:
            $moduleid = $DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);

            $pagerecord                 = new stdClass();
            $pagerecord->course         = $courseid;
            $pagerecord->name           = $title;
            $pagerecord->intro          = '';
            $pagerecord->introformat    = FORMAT_HTML;
            $pagerecord->content        = $html;
            $pagerecord->contentformat  = FORMAT_HTML;
            $pagerecord->timemodified   = $now;
            $pagerecord->timecreated    = $now;
            $pagerecord->revision       = 1;
            $pagerecord->displayoptions = serialize(['printheading' => 1, 'printintro' => 0]);
            $pagerecord->display        = 5;
            $pageinstanceid = $DB->insert_record('page', $pagerecord);

            $cm                       = new stdClass();
            $cm->course               = $courseid;
            $cm->module               = $moduleid;
            $cm->instance             = $pageinstanceid;
            $cm->section              = $DB->get_field('course_sections', 'id',
                                           ['course' => $courseid, 'section' => $sectionnumber],
                                           MUST_EXIST);
            $cm->visible              = 1;
            $cm->visibleold           = 1;
            $cm->visibleoncoursepage  = 1;
            $cm->groupmode            = 0;
            $cm->groupingid           = 0;
            $cm->completion           = 0;
            $cm->added                = $now;
            $cmid = $DB->insert_record('course_modules', $cm);

            $sectionrow = $DB->get_record('course_sections',
                ['course' => $courseid, 'section' => $sectionnumber], '*', MUST_EXIST);
            $sequence = trim($sectionrow->sequence ?? '');
            $sectionrow->sequence = $sequence === '' ? (string)$cmid : $sequence . ',' . $cmid;
            $DB->update_record('course_sections', $sectionrow);

            rebuild_course_cache($courseid, true);
            break;
    }

    // Redirect based on which button was pressed.
    // 'Push and view course' goes straight to the course page.
    // 'Push to course' returns to the builder for another push.
    $successmsg = get_string('successmsg', 'local_contentbuilder', ['course' => $course->fullname]);
    if ($pushandview) {
        redirect(
            new moodle_url('/course/view.php', ['id' => $courseid]),
            $successmsg,
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/contentbuilder/index.php', ['courseid' => $courseid]),
            $successmsg,
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

// ── Render form ───────────────────────────────────────────────────────────────
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_contentbuilder'));
echo '<p>' . get_string('pluginname_desc', 'local_contentbuilder') . '</p>';
$form->display();

// Load the block builder AMD module.
// The compiled build file (amd/build/blockbuilder.min.js) is generated
// by running grunt in the Moodle root after plugin installation.
$PAGE->requires->js_call_amd('local_contentbuilder/blockbuilder', 'init');

echo $OUTPUT->footer();
