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
 * Page helper for the forum summary report.
 *
 * @package   forumreport_summary
 * @copyright 2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace forumreport_summary;
use context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Page helper for the forum summary report.
 *
 * @package   forumreport_summary
 * @copyright 2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class page_helper {

    /**
     * Sets up $PAGE for forum summary reports.
     *
     * @param moodle_url $url The page URL.
     * @param int $courseid The course's ID.
     * @param int $forumid The forum's ID.
     * @param string $forumname The title of the forum(s) being summarised.
     */
    public static function setup(moodle_url $url, $courseid, $forumid, $forumname) {
        global $PAGE;

        if (isguestuser()) {
            print_error('noguest');
        }

        if ($forumid > 0) {
            $cm = get_coursemodule_from_instance('forum', $forumid, $courseid);
        } else {
            $cm = null;
        }

        require_login($courseid, false, $cm);

        $course = get_course($courseid);

        $PAGE->set_url($url);
        $PAGE->set_pagelayout('standard');
        $PAGE->set_title($forumname);
        $PAGE->set_heading($course->fullname);
    }
}
