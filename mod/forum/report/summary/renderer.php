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
     * @param stdClass $course The course object.
     * @param context $context The context object.
     * @param array $filters Optional array of currently applied filter values.
     * @return string The filter form HTML.
     */
    public function render_report_filters(stdClass $course, context $context, array $filters = []): string {
        $renderable = new \forumreport_summary\output\filters($course, $context);
        $templatecontext = $renderable->export_for_template($this);

        return self::render_from_template('forumreport_summary/filters', $templatecontext);
    }

    /**
     * Renders the button which generates the summary report.
     *
     * @param moodle_url $url Moodle URL to generate the report.
     * @return string HTML.
     */
    public function render_generate_button($url): string {


//////////////////////
        //TODO - move this into the template and/or renderable.
        //////////////////////


        $attributes = [
            'type'       => 'submit',
            'value'      => get_string('generatereport', 'forumreport_summary'),
            'title'      => get_string('generatereport', 'forumreport_summary'),
            'disabled'   => null,
            'class'      => 'btn btn-primary',
            'formaction' => $url->out(false),
        ];

//TODO (maybe) need to add the formaction later -> probs not,
// and do whatever $url stuff is commented out below or similar so that
//the URL can be built properly to generate the report << will be post so not needed?

        /*if ($button->actions) {
            $id = html_writer::random_id('single_button');
            $attributes['id'] = $id;
            foreach ($button->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }*/
        // Create the input element.
        $output = html_writer::empty_tag('input', $attributes);

        // Create hidden fields.
        $params = [];//$button->url->params();
        /*if ($button->method === 'post') {
            $params['sesskey'] = sesskey();
        }*/
        foreach ($params as $var => $val) {
           $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => $var, 'value' => $val]);
        }


        // now the form itself around it - TODO - this should not make the form itself, or should it?
        /*if ($button->method === 'get') {
            $url = $button->url->out_omit_querystring(true); // url without params, the anchor part allowed
        } else {
            $url = $button->url->out_omit_querystring();     // url without params, the anchor part not allowed
        }*/

        // Action is required.
        if ($url === '') {
            $url = s($url->out());
        }
        $attributes = array('method' => 'get', //$button->method,
                            'action' => $url,
                            'id'     => 'form ID'); //$button->formid);
        $output = html_writer::tag('form', $output, $attributes);

        // and finally one more wrapper with class
        return html_writer::tag('div', $output, array('class' => 'text-center')); //$button->class));
    }
}
