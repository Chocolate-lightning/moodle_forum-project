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

    /** @var stdClass $course The course being reported on. */
    protected $course;

    /** @var context $context The context of the report. */
    protected $context;

    /** @var array $filters Filters which will be rendered in the format type => [values] */
    protected $filters = [
        'groups' => [0],
    ];

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
        $this->course = $course;
        $this->context = $context;
        $output = '';

        // Update any filter values from their defaults.
        $this->set_filter_values($filters);

        // Prepare HTML for all required filters within it.
        $output .= $this->render_group_filter();

        return $output;
    }

    /**
     * Replaces default filter values with those provided.
     *
     * @param array $filters Values for any filter properties to be set.
     * @return void.
     */
    private function set_filter_values($filters): void {
        foreach ($filters as $filter => $value) {
            if (array_key_exists($filter, $this->filters)) {
                $this->filters[$filter] = $value;
            }
        }
    }

    /**
     * Renders the group selection filter.
     *
     * @return string The filter in HTML format.
     */
    private function render_group_filter(): string {
        $output = html_writer::label(get_string('filter:groupsname', 'forumreport_summary'), 'groups');
        $options = [];
        $selected = [];
        $allgroups = [0 => get_string('filter:groupsdefault', 'forumreport_summary')];

        //TODO: Need to check the below is the right thing, because on my test site it seems to show course groups that aren't part of the forum

        // Only fetch groups user has access to.
        $cm = get_coursemodule_from_instance('forum', $this->context->instanceid, $this->course->id);
        $groups = groups_get_activity_allowed_groups($cm);

        foreach ($groups as $group) {
            $options[$group->id] = $group->name;

            if (in_array($group->id, $this->filters['groups'])) {
                $selected[] = $group->id;
            }
        }

        $output .= html_writer::select($options, 'groups[]', $selected, $allgroups);

        return $output;
    }

    /**
     * Renders the button which generates the summary report.
     *
     * @param moodle_url $url Moodle URL to generate the report.
     * @return string HTML.
     */
    public function render_generate_button($url): string {
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
