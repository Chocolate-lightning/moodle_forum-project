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

use table_sql;
use html_writer;

/**
 * The class for displaying the forum report table.
 *
 * @copyright  2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class summary_table extends table_sql {

    //TODO: Add filter values
    /*protected $forumids;
    protected $fromtimestamp;
    protected $totimestamp;
    etc etc*/

    /** @var int The number of rows to be displayed per page. */
    protected $perpage = 25;

    /** @var int[] Options for max number of rows per page. */
    //protected $perpageoptions = [25, 50, 100];

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
        parent::__construct("summaryreport_{$forumid}");

        $this->courseid = $courseid;
        $this->forumid = $forumid;

        //TODO: Have values passed in for filters that can be assigned to properties.

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
     * Generate the select column.
     *
     * @param stdClass $data The row data.
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
     */
    public function col_username($data) {
        return $data->username;
    }

    /**
     * Generate the fullname column.
     *
     * @param stdClass $data The row data.
     */
    public function col_fullname($data) {
        //TODO: Need to check permission to view this

        return $data->firstname . ' ' . $data->lastname;
    }

    /**
     * Generate the postcount column.
     *
     * @param stdClass $data The row data.
     */
    public function col_postcount($data) {
        return $data->postcount;
    }

    /**
     * Generate the replycount column.
     *
     * @param stdClass $data The row data.
     */
    public function col_replycount($data) {
        return $data->replycount;
    }

    /**
     * Override the default implementation to set a decent heading level.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4); //TODO - test this
    }

    /** TODO::: This is to override, so it can pull in the more complicated queries
     * Query the db. Store results in the table object for use by build_table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Overridden but unused.
     */
    public function query_db($pagesize, $useinitialsbar=false) {
        global $DB;

        // Set up pagination if not downloading the whole report.
        if (!$this->is_downloading()) {
            $totalsql = ' SELECT COUNT(DISTINCT(ue.userid))
                            FROM {enrol} e
                            JOIN {user_enrolments} ue ON ue.enrolid = e.id
                            JOIN {user} u ON u.id = ue.userid
                            JOIN {forum} f ON f.course = e.courseid
                           WHERE e.courseid = :courseid';
            $totalparams = [
                'courseid' => $this->courseid,
            ];

            if($this->forumid > 0) {
                $totalsql .= ' AND f.id = :forumid';
                $totalparams['forumid'] = $this->forumid;
            }

            // Set up pagination.
            $totalrows = $DB->count_records_sql($totalsql, $totalparams);
            $this->pagesize($pagesize, $totalrows);
        }

        $this->build_query();

        // Fetch the data.
        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = "ORDER BY {$sort}";
        }


        //TODO: PERHAPS THIS COULD BE $this->sql->fromdefault and fromcustom <<< so rather than building a single from, have a default that can be used, and then custom stuff that is added in
        $sql = "SELECT
                {$this->sql->fields}
                FROM {$this->sql->from}
                WHERE {$this->sql->where}
                {$sort}";

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

    private function build_query() {
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

        // Default fields that will always be included.
        $basefields = 'ue.userid AS userid, e.courseid AS courseid, f.id as forumid, SUM(IF(p.parent = 0, 1, 0)) AS postcount, SUM(IF(p.parent != 0, 1, 0)) AS replycount, u.username, u.firstname, u.lastname';

        $basefrom = '     {enrol} e
                     JOIN {user_enrolments} ue ON ue.enrolid = e.id
                     JOIN {user} u ON u.id = ue.userid
                     JOIN {forum} f ON f.course = e.courseid
                     JOIN {forum_discussions} d ON d.forum = f.id
                LEFT JOIN {forum_posts} p ON p.discussion =  d.id
                      AND p.userid = ue.userid';

        $basewhere = 'e.courseid = :courseid';
        
        $groupby = ' GROUP BY ue.userid';
        
        $params = [
            'courseid' => $this->courseid,
        ];

        //TODO: Add in other fields here by filters, in addition to the base fields
        $fields = $basefields;

        //TODO: Add in any other joins required, based on filters
        $from = $basefrom;
        
        //TODO: Add in any other wheres required, based on filters - eg specific forum ID
        $where = $basewhere;

        if ($this->forumid > 0) {
            $where .= ' AND f.id = :forumid';

            $params['forumid'] = $this->forumid;
        }

        $where .= $groupby;

        $this->set_sql($fields, $from, $where, $params); //TODO: move out into whatever builds this
    }





    //TODO using /admin/tool/dataprivacy/classes/output/data_requests_table.php
    //AND https://github.com/Chocolate-lightning/moodle-tool_matt/blob/master/classes/output/tool_matt_table.php
    //TO FIGURE THIS OUT
}

