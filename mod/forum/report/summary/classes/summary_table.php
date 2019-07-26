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
 * @package   forumreport_summary
 * @copyright 2019 Michael Hawkins <michaelh@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace forumreport_summary;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use coding_exception;
use html_writer;
use table_sql;

/**
 * The class for displaying the forum report table.
 *
 * @copyright  2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class summary_table extends table_sql {

    /**
     * Constants to define filter types available
     * Forum can be set via constructor since it affects the context of records being accessed,
     * but is included here so it is injected into queries and can be fetched consistently with other filters.
     */
    const FILTER_FORUM = 1;
    const FILTER_DATEFROM = 2;
    const FILTER_DATETO = 3;

    /** @var stdClass The various SQL segments that will be combined to form queries to fetch various information. */
    public $sql;

    /** @var int The number of rows to be displayed per page. */
    protected $perpage = 25;

    /** @var int[] Options for max number of rows per page. */
    protected $perpageoptions = [25, 50, 75, 100];

    /** @var int The course ID being reported on */
    protected $courseid = 0;

    /** @var int The forum ID being reported on - 0 = all forums in the course. */
    protected $forumid = 0;

    /**
     * Forum report table constructor.
     *
     * @param int $courseid The ID of the course the forum(s) exist within.
     * @param int (opt) $forumid The ID of the forum being summarised. 0 will fetch for all forums in the course.
     */
    public function __construct($courseid, $forumid = 0) {

        //TODO: Check permission to view this combination of course/forum, so it's only checked once

        parent::__construct("summaryreport_{$courseid}_{$forumid}");

        $this->courseid = intval($courseid);

        $checkboxattrs = [
            'title' => get_string('selectall'),
            'data-action' => 'selectall'
        ];

        //TODO - this may be dynamic and need to be built, depending on which filters are applied (eg only include country where it's a filter)
        $columnheaders = [
            'select' => html_writer::checkbox('selectall', 1, false, null, $checkboxattrs),
            'username' => get_string('username'),
            'fullname' => get_string('fullname', 'forumreport_summary'),
            'postcount' => get_string('postcount', 'forumreport_summary'),
            'replycount' => get_string('replycount', 'forumreport_summary'),
        ];

        $this->define_columns(array_keys($columnheaders));
        $this->define_headers(array_values($columnheaders));

        // Define configs.
        $this->define_table_configs();

        // Define the basic SQL data and object format.
        $this->define_base_sql();

        // Set the forum ID if one has been provided.
        if ($forumid > 0) {
            $this->add_filter(self::FILTER_FORUM, [$forumid]);
        }
    }

    /**
     * Provides the string name of each filter type.
     *
     * @param type $filtertype Type of filter
     * @return string Name of the filter
     */
    public function get_filter_name($filtertype) {
        $filternames = [
            self::FILTER_FORUM => 'Forum',
            self::FILTER_DATEFROM => 'Date created from',
            self::FILTER_DATETO => 'Date created to',
        ];

        return $filternames[$filtertype];
    }

    /**
     * Generate the select column.
     *
     * @param stdClass $data The row data.
     * @return string HTML for checkbox.
     */
    public function col_select($data) {
        //TODO: rowids[] name might work if it could be user or group ID, otherwise userid
        //TODO: see if checkboxes ever need to be checked by default (3rd param)
        return \html_writer::checkbox('rowids[]', $data->userid, false, '', ['class' => 'selectrows']);
    }

    /**
     * Generate the username column.
     *
     * @param stdClass $data The row data.
     * @return string username.
     */
    public function col_username($data) {
        return $data->username;
    }

    /**
     * Generate the fullname column.
     *
     * @param stdClass $data The row data.
     * @return string User's full name.
     */
    public function col_fullname($data) {
        //TODO: Need to check permission to view this

        return $data->firstname . ' ' . $data->lastname;
    }

    /**
     * Generate the postcount column.
     *
     * @param stdClass $data The row data.
     * @return int number of discussion posts made by user.
     */
    public function col_postcount($data) {
        return $data->postcount;
    }

    /**
     * Generate the replycount column.
     *
     * @param stdClass $data The row data.
     * @return int number of replies made by user.
     */
    public function col_replycount($data) {
        return $data->replycount;
    }

    /**
     * Override the default implementation to set a decent heading level.
     *
     * @return string Output indicating no rows were found.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4);
    }

    /** TODO::: This is to override, so it can pull in the more complicated queries
     * Query the db. Store results in the table object for use by build_table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Overridden but unused.
     * @return void
     */
    public function query_db($pagesize, $useinitialsbar=false) {
        global $DB;

        // Set up pagination if not downloading the whole report.
        if (!$this->is_downloading()) {
            $totalsql = $this->get_full_sql(false);

            // Set up pagination.
            $totalrows = $DB->count_records_sql($totalsql, $this->sql->params);
            $this->pagesize($pagesize, $totalrows);
        }

        // Fetch the data.
        $sql = $this->get_full_sql();

        // Only paginate when not downloading.
        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }

        /* Could possibly process the data to find subtotals before setting $this->rawdata
         * $rawdata = [];
        foreach ($rawdata as $datarow) {
            //
        }*/
    }

       /**
     * Adds the relevant SQL to apply a filter to the report.
     *
     * @param int $filtertype Filter type as defined by class constants.
     * @param array $values Optional array of values passed into the filter type.
     * @return void
     * @throws coding_exception
     */
    public function add_filter($filtertype, $values = []) {
        $paramcounterror = false;

        switch($filtertype) {
            case self::FILTER_FORUM:
                if (count($values) != 1) {
                    $paramcounterror = true;
                }

                // No select fields required - displayed in title.
                // No extra joins required, forum is already joined.
                $this->sql->filterwhere .= ' AND f.id = :forumid';
                $this->sql->params['forumid'] = $values[0];
                break;

            case self::FILTER_DATEFROM:
                if (count($values) != 1) {
                    $paramcounterror = true;
                }

                // No select fields required - forms part of a range.
                // No extra joins required.
                $this->sql->filterwhere .= ' AND p.created >= :datefrom';
                $this->sql->params['datefrom'] = $values[0];
                break;

            case self::FILTER_DATETO:
                if (count($values) != 1) {
                    $paramcounterror = true;
                }

                // No select fields required - forms part of a range.
                // No extra joins required.
                $this->sql->filterwhere .= ' AND p.created <= :dateto';
                $this->sql->params['dateto'] = $values[0];

                break;
            default:
                throw new coding_exception("Report filter type '{$filtertype}' not found.");
                break;
        }

        if ($paramcounterror) {
            $filtername = $this->get_filter_name($filtertype);
            throw new coding_exception("An invalid number of values have been passed for the '{$filtername}' filter.");
        }
    }

    /**
     * Define various table config options.
     */
    protected function define_table_configs() {
        $this->collapsible(false);
        $this->sortable(true, 'firstname', SORT_ASC);
        $this->pageable(true);
        $this->no_sorting('select');
    }

    /**
     * Define the object to store all for the table SQL and initialises the base SQL required.
     *
     * @param void.
     * @return void.
     */
    protected function define_base_sql() {
        $this->sql = new \stdClass();

        // Define base SQL query format.
        $this->sql->basefields = ' ue.userid AS userid,
                                    e.courseid AS courseid,
                                    f.id as forumid,
                                    SUM(IF(p.parent = 0, 1, 0)) AS postcount,
                                    SUM(IF(p.parent != 0, 1, 0)) AS replycount,
                                    u.username,
                                    u.firstname,
                                    u.lastname';

        $this->sql->basefromjoins = '    {enrol} e
                                    JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                    JOIN {user} u ON u.id = ue.userid
                                    JOIN {forum} f ON f.course = e.courseid
                                    JOIN {forum_discussions} d ON d.forum = f.id
                               LEFT JOIN {forum_posts} p ON p.discussion =  d.id
                                     AND p.userid = ue.userid';

        $this->sql->basewhere = 'e.courseid = :courseid';

        $this->sql->groupby = ' GROUP BY ue.userid';

        $this->sql->params = ['courseid' => $this->courseid];

        // Filter values will be populated separately where required.
        $this->sql->filterfields = '';
        $this->sql->filterfromjoins = '';
        $this->sql->filterwhere = '';
    }

    /**
     * Overriding the parent method because it should not be used here.
     * Filters are applied, so the stucture of $this->sql is now different to the way this is set up in the parent.
     *
     * @throws coding_exception
     */
    public function set_sql($fields, $from, $where, array $params = array()) {
        throw new coding_exception('The set_sql method should not be used by the summary_table class.');
    }

    /**
     * Convenience method to call a number of methods for you to display the table.
     * Overrides the parent so SQL for filters is handled.
     *
     * @param $pagesize int Number of rows to fetch.
     * @param $useinitialsbar bool Whether to include the initials bar with the table.
     * @param $downloadhelpbutton string Unused.
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton='') {
        global $DB;

        if (!$this->columns) {
            $sql = $this->get_full_sql();

            $onerow = $DB->get_record_sql($sql, $this->sql->params, IGNORE_MULTIPLE);
            //if columns is not set then define columns as the keys of the rows returned
            //from the db.
            $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }

        $this->setup();
        $this->query_db($pagesize, $useinitialsbar);
        $this->build_table();
        $this->close_recordset();
        $this->finish_output();
    }

    /**
     * Prepares a complete SQL statement from the base query and any filters defined.
     *
     * @param bool $fullselect Whether to select all relevant columns.
     *              False selects a count only (used to calculate pagination).
     * @return string The complete SQL statement.
     */
    private function get_full_sql($fullselect = true) {
        $sql = 'SELECT';

        if ($fullselect) {
            $sql .= " {$this->sql->basefields}
                      {$this->sql->filterfields}";
        } else {
            $sql .= ' COUNT(DISTINCT(ue.userid))';
        }

        $sql .= " FROM {$this->sql->basefromjoins}
                       {$this->sql->filterfromjoins}
                 WHERE {$this->sql->basewhere}
                       {$this->sql->filterwhere}";

        if ($fullselect) {
            $sql .= $this->sql->groupby;

            if(($sort = $this->get_sql_sort())) {
                $sql .= " ORDER BY {$sort}";
            }
        }

        return $sql;
    }

    /**
     * Override the wrap_html_finish method so per page option and bulk select actions can be handled.
     */
    public function wrap_html_finish() {
        global $OUTPUT;

        //TODO per page output, and whatever is required for the checkboxes
        /*
        $data = new stdClass();
        $data->options = [
            [
                'value' => 0,
                'name' => ''
            ],
            [
                'value' => \tool_dataprivacy\api::DATAREQUEST_ACTION_APPROVE,
                'name' => get_string('approve', 'tool_dataprivacy')
            ],
            [
                'value' => \tool_dataprivacy\api::DATAREQUEST_ACTION_REJECT,
                'name' => get_string('deny', 'tool_dataprivacy')
            ]
        ];

        $perpageoptions = array_combine($this->perpageoptions, $this->perpageoptions);
        $perpageselect = new \single_select(new moodle_url(''), 'perpage',
                $perpageoptions, get_user_preferences('tool_dataprivacy_request-perpage'), null, 'selectgroup');
        $perpageselect->label = get_string('perpage', 'moodle');
        $data->perpage = $OUTPUT->render($perpageselect);

        echo $OUTPUT->render_from_template('tool_dataprivacy/data_requests_bulk_actions', $data);*/
    }

    //TODO using /admin/tool/dataprivacy/classes/output/data_requests_table.php
    //AND https://github.com/Chocolate-lightning/moodle-tool_matt/blob/master/classes/output/tool_matt_table.php
    //TO FIGURE THIS OUT


            /** Draft working query for basic info (NOTE: THE forum_posts HAS BEEN MOVED TO A SINGLE JOIN IN THE PHP):
            SELECT ue.userid AS userid, e.courseid AS courseid, f.id as forumid, COUNT(pd.id) AS postcount, COUNT(pr.id) AS replycount, u.username, u.firstname, u.lastname
            FROM mdl_enrol e
                JOIN mdl_user_enrolments ue ON ue.enrolid = e.id #users enrolled - so we get zeros for users with no posts
                JOIN mdl_user u ON u.id = ue.userid #Username etc
                JOIN mdl_forum f ON f.course = e.courseid #Only forums in this course
                JOIN mdl_forum_discussions d ON d.forum = f.id #Generic all discussions to get post count
                LEFT JOIN mdl_forum_posts pd ON pd.discussion =  d.id AND pd.userid = ue.userid AND pd.parent = 0 #<<< parent 0 is a discussion
                LEFT JOIN mdl_forum_posts pr ON pr.discussion =  d.id AND pr.userid = ue.userid AND pr.parent != 0 #<<< parent 0 is a discussion, need to sum that separate
            WHERE e.courseid = 3 AND f.id = 4
            GROUP BY ue.userid
            ORDER BY ue.userid ASC
         */
}

