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

defined('MOODLE_INTERNAL') || die();

/**
 * Forum summary report filters renderable.
 *
 * @copyright  2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters implements \renderable, \templatable {

    /**
     * Course the report is being run within.
     *
     * @var stdClass $course
     */
    protected $course;

    /**
     * Context the report is being run within.
     *
     * @var context $context
     */
    protected $context;

    /**
     * IDs of groups available for filtering.
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
     * Builds renderable filter data.
     *
     * @param \stdClass $course The course object.
     * @param \context $context The context object.
     * @param array $filterdata (optional) The data that has been set on available filters, if any.
     */
    public function __construct(\stdClass $course, \context $context, array $filterdata = []) {
        $this->course = $course;
        $this->context = $context;

        // Prepare groups filter data.
        $groupsdata = empty($filterdata['groups']) ? [] : $filterdata['groups'];
        $this->prepare_groups_data($groupsdata);
    }

    protected function prepare_groups_data($groupsdata) {
        // Always include the 'all groups' option and select it if necessary.
        $groupsavailable = [0 => get_string('filter:groupsdefault', 'forumreport_summary')];
        $groupsselected = [];

        if (empty($groupsdata) || in_array(0, $groupsdata)) {
            $groupsselected[] = 0;
        }

        // Only fetch groups user has access to.
        $cm = get_coursemodule_from_instance('forum', $this->context->instanceid, $this->course->id);
        $groups = groups_get_activity_allowed_groups($cm);

        foreach ($groups as $group) {
            $groupsavailable[$group->id] = $group->name;

            // Select provided groups if 'all' not selected, and group is available.
            if (!in_array(0, $groupsselected) && in_array($group->id, $groupsdata)) {
                $groupsselected[] = $group->id;
            }
        }

        // Overwrite groups properties
        $this->groupsavailable = $groupsavailable;
        $this->groupsselected = $groupsselected;
    }


    /**
     * Export data for use as the context of a mustache template.
     *
     * @param \renderer_base $renderer The renderer to be used to display report filters.
     * @return array Data in a format compatible with a mustache template.
     */
    public function export_for_template(\renderer_base $renderer): \stdClass {
        $output = new \stdClass();

        // Groups filters.
        $groupsdata = [];

        foreach ($this->groupsavailable as $groupid => $groupname) {
            $groupsdata[] = [
                'groupid' => $groupid,
                'groupname' => $groupname,
                'checked' => in_array($groupid, $this->groupsselected),
            ];
        }

        $output->filtergroups = $groupsdata;

        return $output;
    }
}
