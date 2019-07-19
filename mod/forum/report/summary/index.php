<?php

//Testing table
//Looking at /var/www/html/moodle_forum-project/admin/tool/dataprivacy/datarequests.php


require_once("../../../../config.php");

//require_login(null, false);

$perpage = optional_param('perpage', 10, PARAM_INT); //TODO: Change to 25
$forumname = optional_param('forumname', 'all', PARAM_ALPHANUMEXT); //TODO: Should match what sanitizing is done to forum names

if ($forumname === 'all' || empty($forumname)) {
    $forumname = 'all forums';
}

$url = new moodle_url('/mod/forum/report/summary');

$title = get_string('summarytitle', 'forumreport_summary', $forumname);

//TODO: Update this to not be using something from tool\dataprivacy
\tool_dataprivacy\page_helper::setup($url, $title, '', 'tool/dataprivacy:managedatarequests');

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

//TODO - Check permissions somewhere here so we know what to restrict

$table = new \forumreport_summary\summary_table(1); //TODO - replace the forum with passed in values
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
