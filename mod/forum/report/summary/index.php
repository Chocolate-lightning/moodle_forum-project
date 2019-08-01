<?php
require_once("../../../../config.php");

$forumid = optional_param('forumid', 0, PARAM_INT);
// This course ID will be ignored if a forum ID is provided.
$courseid = optional_param('courseid', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT); // TODO: Change to 25.
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

\forumreport_summary\page_helper::setup($url, $courseid, $forumid, $forumname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('summarytitle', 'forumreport_summary', $forumname));

$table = new \forumreport_summary\summary_table($courseid, $forumid);
$table->baseurl = $url;

//$table->add_filter($table::FILTER_DATEFROM, ['1564033886']);

//$requestlist = new tool_dataprivacy\output\data_requests_page($table, $filtersapplied);
//$requestlistoutput = $PAGE->get_renderer('tool_dataprivacy');

//echo $requestlistoutput->render($requestlist);

$table->out($perpage, false);
echo $OUTPUT->footer();
