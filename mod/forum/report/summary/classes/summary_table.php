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
 * @package   forum_report
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

    /**
     * Forum report table constructor.
     *
     * @param int (opt) $forumid the ID of the forum being summarised. 0 will fetch for all forums in the course.
     */
    public function __construct($forumid = 0) {
        parent::__construct("report_{$forumid}"); //TODO: Is the string part needed in the param

        //TODO: Have values passed in for filters that can be assigned to properties.

        $checkboxattrs = [
            'title' => get_string('selectall'),
            'data-action' => 'selectall'
        ];

        $columnheaders = [
            'select' => html_writer::checkbox('selectall', 1, false, null, $checkboxattrs),
            'username' => get_string('username'),
            'fullname' => get_string('fullname', 'forumreport_summary'),
            'postcount' => get_string('postcount', 'forumreport_summary'),
        ];

        $this->define_columns(array_keys($columnheaders));
        $this->define_headers(array_values($columnheaders));

        // Define configs.
        $this->define_table_configs();

        $fields = '*';
        $from = '{user}';
        $where = '1 = 1';
        $params =[];
        $this->set_sql($fields, $from, $where, $params); //TODO: move out into whatever builds this
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
        return \html_writer::checkbox('rowids[]', $data->id, false, '', ['class' => 'selectrows']);
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
    public function col_fullnamez($data) {
        return $data->firstname . ' ' . $data->lastname;
    }

    /**
     * Generate the postcount column.
     *
     * @param stdClass $data The row data.
     */
    public function col_postcount($data) {
        return 42069; //TODO
    }

    /**
     * Override the default implementation to set a decent heading level.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4); //TODO - test this
    }









    //TODO using /admin/tool/dataprivacy/classes/output/data_requests_table.php
    //AND https://github.com/Chocolate-lightning/moodle-tool_matt/blob/master/classes/output/tool_matt_table.php
    //TO FIGURE THIS OUT
}

