<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Page to export forum discussions.
 *
 * @package    mod_forum
 * @copyright  2019 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/dataformatlib.php');
require_once($CFG->dirroot . '/mod/forum/export_form.php');

$forumid = optional_param('id', 0, PARAM_INT);
$doexport = optional_param('export', false, PARAM_BOOL);
$pagetitle = get_string('export', 'mod_forum');

$vaultfactory = mod_forum\local\container::get_vault_factory();
$forumvault = $vaultfactory->get_forum_vault();
$forum = $forumvault->get_from_id($forumid);

if (empty($forum)) {
    throw new \moodle_exception('Unable to find forum with id ' . $forumid);
}

$context = $forum->get_context();
$url = new moodle_url("/mod/forum/export.php");
$course = $forum->get_course_record();
$coursemodule = $forum->get_course_module_record();
$cm = \cm_info::create($coursemodule);

require_course_login($course, true, $cm);

if ($doexport == false) {
    $PAGE->set_context($context);
    $PAGE->set_url($url);
    $PAGE->set_title($pagetitle);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_heading($pagetitle);
}

$form = new export($url->out(false), ['course' => $course, 'cm' => $cm, 'context' => $context, 'forum' => $forum]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/forum/export.php'));
} else if ($data = $form->get_data()) {
    require_sesskey();
    global $DB;
    $userid = $USER->id;
    $forumid = $data->id;

//    $discussion = $data->discussion;

    $dataformat = $data->formats;

    $vaultfactory = mod_forum\local\container::get_vault_factory();
    $managerfactory = mod_forum\local\container::get_manager_factory();
    $builderfactory = mod_forum\local\container::get_builder_factory();

    $discussionvault = $vaultfactory->get_discussion_vault();
    
    $forumvault = $vaultfactory->get_forum_vault();
    $forum = $forumvault->get_from_id($forumid);
    $lastdiscussion = $discussionvault->get_last_discussion_in_forum($forum);
    $discussion = $lastdiscussion->get_id();
    $postvault = $vaultfactory->get_post_vault();

    $capabilitymanager = $managerfactory->get_capability_manager($forum);
    $exportedpostsbuilder = $builderfactory->get_exported_posts_builder();

    $discussion = $discussionvault->get_from_id($discussion);

    $forum = $forumvault->get_from_id($discussion->get_forum_id());
    $posts = $postvault->get_from_discussion_id(
        $USER,
        $discussion->get_id(),
        $capabilitymanager->can_view_any_private_reply($USER)
    );

    $exportedposts = $exportedpostsbuilder->build(
        $USER,
        [$forum],
        [$discussion],
        $posts
    );
    $fields = ['id', 'subject', 'message'];

    $exportdata = new ArrayObject($exportedposts);
    $iterator = $exportdata->getIterator();
    require_once($CFG->libdir.'/dataformatlib.php');
    $filename = clean_filename('discussion');
    download_as_dataformat($filename, $dataformat, $fields, $iterator);
    die();
}
if ($doexport == false) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($pagetitle);

    $form->display();

    echo $OUTPUT->footer();
}

