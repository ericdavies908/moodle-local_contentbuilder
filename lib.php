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
 * Library functions for local_contentbuilder.
 *
 * @package    local_contentbuilder
 * @copyright  2026 University of Glasgow LISU {@link https://www.gla.ac.uk}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds a link to the Section Content Builder in the course administration menu.
 *
 * @param settings_navigation $settingsnav The settings navigation object.
 * @param context $context The current context.
 */
function local_contentbuilder_extend_settings_navigation(
    settings_navigation $settingsnav,
    context $context
): void {

    if ($context->contextlevel !== CONTEXT_COURSE) {
        return;
    }

    if (!has_capability('local/contentbuilder:pushcontent', $context)) {
        return;
    }

    $coursenode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
    if (!$coursenode) {
        return;
    }

    $url = new moodle_url('/local/contentbuilder/index.php', ['courseid' => $context->instanceid]);

    $coursenode->add(
        get_string('pluginname', 'local_contentbuilder'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'contentbuilder',
        new pix_icon('i/edit', '')
    );
}

/**
 * Serves files uploaded through the block image file manager.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object (not used, plugin-level file area).
 * @param context $context The context object.
 * @param string $filearea The file area name.
 * @param array $args Extra arguments (item ID, then path components).
 * @param bool $forcedownload Whether to force file download.
 * @param array $options Additional options.
 * @return bool False if file not found, otherwise serves the file and exits.
 */
function local_contentbuilder_pluginfile(
    $course,
    $cm,
    context $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options = []
): bool {

    if ($filearea !== 'blockimage') {
        return false;
    }

    require_login($course);

    $itemid   = (int) array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs   = get_file_storage();
    $file = $fs->get_file(
        $context->id,
        'local_contentbuilder',
        'blockimage',
        $itemid,
        $filepath,
        $filename
    );

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
