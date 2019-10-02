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
 * Forum summary report filters renderable.
 *
 * @package    forumreport_summary
 * @copyright  2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace forumreport_summary\output;

use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use forumreport_summary;

defined('MOODLE_INTERNAL') || die();

/**
 * Forum summary report filters renderable.
 *
 * @copyright  2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters implements renderable, templatable {

    /**
     * Course module the report is being run within.
     *
     * @var stdClass $cm
     */
    protected $cm;

    /**
     * Moodle URL used as the form action on the generate button.
     *
     * @var moodle_url $actionurl
     */
    protected $actionurl;

    /**
     * Details of groups available for filtering.
     * Stored in the format groupid => groupname.
     *
     * @var array $groupsavailable
     */
    protected $groupsavailable = [];

    /**
     * IDs of groups selected for filtering.
     *
     * @var array $groupsselected
     */
    protected $groupsselected = [];

    /**
     * HTML for dates filter.
     *
     * @var array $datesdata
     */
    protected $datesdata = [];

    /**
     * Builds renderable filter data.
     *
     * @param stdClass $cm The course module object.
     * @param moodle_url $actionurl The form action URL.
     * @param array $filterdata (optional) Associative array of data that has been set on available filters, if any,
     *                                      in the format filtertype => [values]
     */
    public function __construct(stdClass $cm, moodle_url $actionurl, array $filterdata = []) {
        $this->cm = $cm;
        $this->actionurl = $actionurl;

        // Prepare groups filter data.
        $groupsdata = $filterdata['groups'] ?? [];
        $this->prepare_groups_data($groupsdata);

        // Prepare dates filter data.
        $datefromdata = $filterdata['datefrom'] ?? [];
        $datetodata = $filterdata['dateto'] ?? [];
        $this->prepare_dates_data($datefromdata, $datetodata);
    }

    /**
     * Prepares groups data and sets relevant property values.
     *
     * @param array $groupsdata Groups selected for filtering.
     * @return void.
     */
    protected function prepare_groups_data(array $groupsdata): void {
        $groupsavailable = [];
        $groupsselected = [];

        // Only fetch groups user has access to.
        $groups = groups_get_activity_allowed_groups($this->cm);

        // Include a 'no groups' option if groups exist.
        if (!empty($groups)) {
            $nogroups = new stdClass();
            $nogroups->id = -1;
            $nogroups->name = get_string('groupsnone');
            array_push($groups, $nogroups);
        }

        foreach ($groups as $group) {
            $groupsavailable[$group->id] = $group->name;

            // Select provided groups if they are available.
            if (in_array($group->id, $groupsdata)) {
                $groupsselected[] = $group->id;
            }
        }

        // Overwrite groups properties.
        $this->groupsavailable = $groupsavailable;
        $this->groupsselected = $groupsselected;
    }

    /**
     * Prepares from date, to date and button text.
     * Empty data will default to a disabled filter with today's date.
     *
     * @param array $datefromdata From date selected for filtering, and whether the filter is enabled.
     * @param array $datetodata To date selected for filtering, and whether the filter is enabled.
     * @return void.
     */
    private function prepare_dates_data(array $datefromdata, array $datetodata): void {
        $timezone = \core_date::get_user_timezone_object();
        $datetoday = new \DateTime("now", $timezone);

        // Prepare date/enabled data.
        if (empty($datefromdata['enabled'])) {
            $fromdate = $datetoday;
            $fromenabled = false;
        } else {
            $fromdate = new \DateTime("{$datefromdata['year']}-{$datefromdata['month']}-{$datefromdata['day']} 00:00:00", $timezone);
            $fromenabled = true;
        }

        if (empty($datetodata['enabled'])) {
            $todate = $datetoday;
            $toenabled = false;
        } else {
            $todate = new \DateTime("{$datetodata['year']}-{$datetodata['month']}-{$datetodata['day']} 23:59:59", $timezone);
            $toenabled = true;
        }

        $this->datesdata = [
            'from' => [
                'date'    => $fromdate,
                'enabled' => $fromenabled,
            ],
            'to' => [
                'date'    => $todate,
                'enabled' => $toenabled,
            ],
        ];

        // Prepare button string data.
        if ($fromenabled && $toenabled) {
            $datestrings = [
                'datefrom' => $fromdate->format('d M y'),
                'dateto'   => $todate->format('d M y'),
            ];
            $this->datesdata['buttontext'] = get_string('filter:datesfromto', 'forumreport_summary', $datestrings);
        } else if ($fromenabled) {
            $this->datesdata['buttontext'] = get_string('filter:datesfrom', 'forumreport_summary', $fromdate->format('d M y'));
        } else if ($toenabled) {
            $this->datesdata['buttontext'] = get_string('filter:datesto', 'forumreport_summary', $todate->format('d M y'));
        } else {
            $this->datesdata['buttontext'] = get_string('filter:datesname', 'forumreport_summary');
        }
    }

    /**
     * Export data for use as the context of a mustache template.
     *
     * @param renderer_base $renderer The renderer to be used to display report filters.
     * @return array Data in a format compatible with a mustache template.
     */
    public function export_for_template(renderer_base $renderer): stdClass {
        $output = new stdClass();

        // Set formaction URL.
        $output->actionurl = $this->actionurl->out(false);

        // Set groups filter data.
        if (!empty($this->groupsavailable)) {
            $output->hasgroups = true;

            $groupscount = count($this->groupsselected);

            if (count($this->groupsavailable) <= $groupscount) {
                $output->filtergroupsname = get_string('filter:groupscountall', 'forumreport_summary');
            } else if (!empty($this->groupsselected)) {
                $output->filtergroupsname = get_string('filter:groupscountnumber', 'forumreport_summary', $groupscount);
            } else {
                $output->filtergroupsname = get_string('filter:groupsname', 'forumreport_summary');
            }

            // Set groups filter.
            $groupsdata = [];

            foreach ($this->groupsavailable as $groupid => $groupname) {
                $groupsdata[] = [
                    'groupid' => $groupid,
                    'groupname' => $groupname,
                    'checked' => in_array($groupid, $this->groupsselected),
                ];
            }

            $output->filtergroups = $groupsdata;
        } else {
            $output->hasgroups = false;
        }

        // Set dates filter data.
        $output->filterdatesname = $this->datesdata['buttontext'];
        $datesform = new forumreport_summary\dates_filter_form(); //TODO: pass in the from and to dates
        $output->filterdates = $datesform->render();

        return $output;
    }
}
