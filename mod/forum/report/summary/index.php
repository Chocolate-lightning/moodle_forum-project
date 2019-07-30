<?php

//Looking at /var/www/html/moodle_forum-project/admin/tool/dataprivacy/datarequests.php

require_once("../../../../config.php");

$forumid = optional_param('forumid', 0, PARAM_INT);
// This course ID will be ignored if a forum ID is provided.
$courseid = optional_param('courseid', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT); //TODO: Change to 25
$url = new moodle_url("/mod/forum/report/summary/");

if ($forumid > 0) {
    $forumobject = $DB->get_record("forum", array("id" => $forumid));

    if (empty($forumobject)) {
        throw new \moodle_exception("Unable to find forum with ID {$forumid}.");
    }

    $forumname = $forumobject->name;
    $courseid = $forumobject->course;
    $url->param('forumid', $forumid);

} else if ($courseid > 0) {
    $forumname = get_string('allforums', 'forumreport_summary');
    $url->param('courseid', $courseid);
} else {
    throw new \moodle_exception("Forum ID or course ID must be provided to generate a forum summary report.");
}

require_login($courseid, false);
//$hascourseaccess = ($PAGE->course->id == SITEID) || can_access_course($PAGE->course, $userid);

$course = get_course($courseid);
$coursename = $course->fullname;
$forumtitle = get_string('summarytitle', 'forumreport_summary', $forumname);

//TODO: Update this to be using the correct capability, will need a new one so can be controlled
//Risk: if students can view others' totals, can determine if there are private replies by comparing the totals with visible posts
\forumreport_summary\page_helper::setup($url, $coursename, $forumtitle, '', 'mod/forum:viewdiscussion');

echo $OUTPUT->header();
echo $OUTPUT->heading($forumtitle);

//TODO - Check permissions somewhere here so we know what to restrict -- needs to be done in the class

$table = new \forumreport_summary\summary_table($courseid, $forumid);
$table->baseurl = $url;

//$table->add_filter($table::FILTER_DATEFROM, ['1564033886']);

//$requestlist = new tool_dataprivacy\output\data_requests_page($table, $filtersapplied);
//$requestlistoutput = $PAGE->get_renderer('tool_dataprivacy');

//echo $requestlistoutput->render($requestlist);

$table->out($perpage, false);
echo $OUTPUT->footer();
