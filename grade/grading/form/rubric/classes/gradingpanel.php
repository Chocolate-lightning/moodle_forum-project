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
 * Build rubric content before we render it out.
 *
 * @package   gradingform_rubric
 * @copyright 2019 Mathew May <mathew.solutions>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace gradingform_rubric;

use gradingform_rubric\output\rubric_grading_panel_renderable;
use moodle_page;

class gradingpanel {

    protected $instance;

    protected $instanceoptions;

    protected $mode;

    protected $rubric;

    protected $canedit;

    protected $submittedvalue;
    protected $name;

    public function __construct($instance, $canedit, $gradingformelement) {
        // TODO Kill off gradingformelement - we only need the getValue and getName functions on it.
        $this->instance = $instance;
        $this->canedit = $canedit;
        $this->gradingformelement = $gradingformelement;

        $this->submittedvalue = $gradingformelement->getValue();
        $this->name = $gradingformelement->getName();
    }

    protected function get_values() {
        $values = $this->submittedvalue;
        if ($values === null) {
            $values = $this->instance->get_rubric_filling();
        }

        return $values;
    }

    protected function is_invalid() {
        if ($values = $this->submittedvalue) {
            return !$this->instance->validate_grading_element($values);
        };

        return false;
    }

    protected function get_criteria() {
        return $this->instance->get_controller()->get_definition()->rubric_criteria;
    }

    protected function set_mode() {
        $this->mode = \gradingform_rubric_controller::DISPLAY_EVAL;
    }

    protected function get_current_instance() {
        return $this->instance->get_current_instance();
    }

    protected function has_current_instance(): bool {
        return $this->get_current_instance() !== null;
    }

    protected function instance_update_required() {
        if (!$this->has_current_instance) {
            // No instance created yet - not ready for grading yet.
            return false;
        }

        if ($this->get_current_instance()->get_status() == \gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
            return true;
        }

        return false;
    }

    protected function stored_rubric_has_changes() {
        if (!$this->has_current_instance()) {
            // No instance created yet - not ready for grading yet.
            return false;
        }
        $storedinstance = $this->get_current_instance()->get_rubric_filling();
        foreach ($storedinstance['criteria'] as $criterionid => $curvalues) {
            $value['criteria'][$criterionid]['savedlevelid'] = $curvalues['levelid'];
            $newremark = null;
            $newlevelid = null;
            if (isset($value['criteria'][$criterionid]['remark'])) $newremark = $value['criteria'][$criterionid]['remark'];
            if (isset($value['criteria'][$criterionid]['levelid'])) $newlevelid = $value['criteria'][$criterionid]['levelid'];
            if ($newlevelid != $curvalues['levelid'] || $newremark != $curvalues['remark']) {
                return true;
            }
        }
        return false;
    }

    protected function restored_from_draft() {
        $instancehaschanges = $this->stored_rubric_has_changes();
        if($instancehaschanges && $this->instance->get_data('isrestored')) {
            return true;
        }
        return false;
    }

    protected function get_options() {
        $this->instanceoptions = $this->instance->get_controller()->get_options();
    }

    /*
     * This should be handled in the renderable in the coming future.
     */
    protected function show_description_teacher() {
        if(!empty($this->instanceoptions['showdescriptionteacher'])) {
            return $this->instance->get_controller()->get_formatted_description();
        }
        return false;
    }

    /*
     * This should be handled in the renderable in the coming future.
     */
    protected function show_remark(): bool {
        if(!empty($this->instanceoptions['enableremarks']) &&
            ($this->mode != \gradingform_rubric_controller::DISPLAY_VIEW || $this->instanceoptions['showremarksstudent'])) {
            return true;
        }
        return false;
    }

    protected function can_edit(): bool {
        // TODO
        return false;
    }

    protected function include_form_fields(): bool {
        // TODO. This should be based on the mode.
        return true;
    }

    protected function get_name(): string {
        // TODO. Check if this needs any formatting.
        return $this->name;
    }

    public function get_data(moodle_page $page) {
        global $OUTPUT;

        // TODO We should find a way to avoid these kinds of things. They change the state of the current object.
        $this->get_options();

        // Till we figure out how we are gonna freeze stuff manually set the mode.
        $this->set_mode();

        $values = $this->get_values();

        $renderable = new rubric_grading_panel_renderable(
            $this->get_name(),
            $this->get_criteria(),
            $this->rubric_mode(),
            $values,

            $this->is_invalid(),
            $this->instance_update_required(),
            $this->restored_from_draft(),
            $this->show_description_teacher(),
            $this->show_remark(),
            $this->can_edit(),
            $this->include_form_fields()
        );

        return $renderable->export_for_template($page->get_renderer('gradingform_rubric'));
    }

    public function build_for_template(moodle_page $page) {
        return $page
            ->get_renderer('gradingform_rubric')
            ->render_from_template('gradingform_rubric/rubric', $this->get_data($page));
    }

    protected function rubric_mode() {
        $mode = '';
        switch ($this->mode) {
            case \gradingform_rubric_controller::DISPLAY_PREVIEW:
            case \gradingform_rubric_controller::DISPLAY_PREVIEW_GRADED:
                $mode = 'editor preview';  break;
            case \gradingform_rubric_controller::DISPLAY_EVAL:
                $mode = 'evaluate editable'; break;
            case \gradingform_rubric_controller::DISPLAY_EVAL_FROZEN:
                $mode = 'evaluate frozen';  break;
            case \gradingform_rubric_controller::DISPLAY_REVIEW:
                $mode = 'review';  break;
            case \gradingform_rubric_controller::DISPLAY_VIEW:
                $mode = 'view';  break;
        }
        return $mode;
    }

}
