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
 * Provides rendering functionality for the forum summary report subplugin.
 *
 * @package   forumreport_summary
 * @copyright 2019 Michael Hawkins <michaelh@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer for the forum summary report.
 *
 * @copyright  2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class forumreport_summary_renderer extends plugin_renderer_base {

    /**
     * Wrap the filter contents in form tags.
     *
     * @param string $formHTML The HTML to be included in the form.
     * @return string Form HTML wrapped in relevant form tags.
     */
    public function render_form_tags($formHTML): string {
        $attributes = [
            'method' => 'post',
            'id'     => 'forumsummaryfilters',
        ];

        return html_writer::tag('form', $formHTML, $attributes);
    }

    /**
     * Renders the filters available for the forum summary report.
     *
     * @param \stdClass $course The course object.
     * @param \context $context The context object.
     * @param \moodle_url $actionurl The form action URL.
     * @param array $filters Optional array of currently applied filter values.
     * @return string The filter form HTML.
     */
    public function render_filters_form(\stdClass $course, \context $context, \moodle_url $actionurl, array $filters = []): string {
        $renderable = new \forumreport_summary\output\filters($course, $context, $actionurl, $filters);
        $templatecontext = $renderable->export_for_template($this);

        return self::render_from_template('forumreport_summary/filters', $templatecontext);
    }
}
