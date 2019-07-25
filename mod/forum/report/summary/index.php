<?php

//Testing table
//Looking at /var/www/html/moodle_forum-project/admin/tool/dataprivacy/datarequests.php


require_once("../../../../config.php");

//require_login(null, false);


//TEST
/*$cmid = 2;
$vaultfactory = mod_forum\local\container::get_vault_factory();
$forumvault = $vaultfactory->get_forum_vault();
$forum = $forumvault->get_from_course_module_id($cmid);*/

//print_object($forum);exit;
//END TEST

$courseid = 2; //TODO: Fetch the course ID and name - either automatically, or through a param. Is required, not optional.
$perpage = optional_param('perpage', 10, PARAM_INT); //TODO: Change to 25
$forumid = optional_param('id', 0, PARAM_INT);
$url = new moodle_url('/mod/forum/report/summary');
$coursename = 'TODO course name';

if ($forumid > 0) {
    //TODO: Fetch the forum name using the ID.
} else {
    $forumname = get_string('allforums', 'forumreport_summary');
}

$forumtitle = get_string('summarytitle', 'forumreport_summary', $forumname);

//TODO: Update this to not be using something from tool\dataprivacy
\forumreport_summary\page_helper::setup($url, $coursename, $forumtitle, '', 'tool/dataprivacy:managedatarequests');

echo $OUTPUT->header();
echo $OUTPUT->heading($forumtitle);

//TODO - Check permissions somewhere here so we know what to restrict

$table = new \forumreport_summary\summary_table($courseid, 0); //new \forumreport_summary\summary_table($courseid, $forumid);
$table->add_filter($table::FILTER_DATEFROM, ['1564033886']);

$table->baseurl = $url;

//$perpage = 10;//25;
if (!empty($perpage)) {
    //set_user_preference(\tool_dataprivacy\local\helper::PREF_REQUEST_PERPAGE, $perpage);
} else {
    //$perpage = $table->get_requests_per_page_options()[0];
}
//$table->set_requests_per_page($perpage);




//$requestlist = new tool_dataprivacy\output\data_requests_page($table, $filtersapplied);
//$requestlistoutput = $PAGE->get_renderer('tool_dataprivacy');

//echo $requestlistoutput->render($requestlist);

//ob_start();
$table->out($perpage, false);
//$tablehtml = ob_get_contents();
//ob_end_clean();

echo $OUTPUT->footer();
