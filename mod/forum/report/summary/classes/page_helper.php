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
     * @param string $coursename The course's name.
     * @param string $forumtitle The title of the forum(s) being summarised.
     * @param string $attachtoparentnode The parent navigation node where this page can be accessed from.
     * @param string $requiredcapability The required capability to view this page.
     */
    public static function setup(moodle_url $url, $coursename, $forumtitle, $attachtoparentnode = '',
                                 $requiredcapability = 'tool/dataprivacy:managedataregistry') {
        global $PAGE, $SITE;

        $context = context_system::instance();

        require_login();
        if (isguestuser()) {
            print_error('noguest');
        }

        //TODO maybe something here for whether filters are visible - students = no
        //require_capability($requiredcapability, $context);

        $PAGE->navigation->override_active_url($url); //TODO what is this?

        $PAGE->set_url($url);
        $PAGE->set_context($context);
        $PAGE->set_pagelayout('admin'); //TODO
        $PAGE->set_title($forumtitle);
        $PAGE->set_heading($coursename);

        // If necessary, override the settings navigation to add this page into the breadcrumb navigation.
        if ($attachtoparentnode) {
            if ($siteadmin = $PAGE->settingsnav->find('root', \navigation_node::TYPE_SITE_ADMIN)) {
                $PAGE->navbar->add($siteadmin->get_content(), $siteadmin->action());
            }
            if ($dataprivacy = $PAGE->settingsnav->find('privacy', \navigation_node::TYPE_SETTING)) {
                $PAGE->navbar->add($dataprivacy->get_content(), $dataprivacy->action());
            }
            if ($dataregistry = $PAGE->settingsnav->find($attachtoparentnode, \navigation_node::TYPE_SETTING)) {
                $PAGE->navbar->add($dataregistry->get_content(), $dataregistry->action());
            }

            $PAGE->navbar->add($forumtitle, $url); //TODO - this should be a generic title
        }
    }
}
