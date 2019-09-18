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

class gradingpanel {

    protected $instance;

    protected $currentinstance;

    protected $instanceoptions;

    protected $canedit;

    protected $gradingformelement;

    public function __construct($instance, $canedit, $gradingformelement) {
        $this->instance = $instance;
        $this->canedit = $canedit;
        $this->gradingformelement = $gradingformelement;
    }

    protected function get_values() {
        $value = $this->gradingformelement->getValue();
        if ($value === null) {
            $value = $this->instance->get_rubric_filling();
        }
        return $value;
    }

    protected function is_invalid() {
        if ($value = $this->gradingformelement->getValue()){
            return !$this->instance->validate_grading_element($value);
        };
        return false;
    }

    protected function get_criteria() {
        return $this->instance->get_controller()->get_definition()->rubric_criteria;
    }

    protected function get_instance() {
        return $this->instance->get_current_instance();
    }

    protected function instance_update_required() {
        if($this->currentinstance->get_status() == \gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
            return true;
        }
        return false;
    }

    protected function stored_rubric_has_changes() {
        $storedinstance = $this->currentinstance->get_rubric_filling();
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

    protected  function show_description_teacher() {
        if(!empty($this->instanceoptions['showdescriptionteacher'])) {
            return $this->instance->get_controller()->get_formatted_description();
        }
        return false;
    }

    public function build_for_template($page) {
        global $OUTPUT;

        $this->currentinstance = $this->get_instance();

        $this->get_options();


        $name = 'test';

        $canedit = false;
        $hasformfields = false;
        $renderable = new rubric_grading_panel_renderable(
            $name,
            $this->get_criteria(),
            $this->get_values(),
            $this->is_invalid(),
            $this->instance_update_required(),
            $this->restored_from_draft(),
            $this->show_description_teacher(),
            $canedit,
            $hasformfields
        );
        return $OUTPUT->render_from_template(
            'gradingform_rubric/shell',
            $renderable->export_for_template($this->instance->get_controller()->get_renderer($page))
        );
    }

}
