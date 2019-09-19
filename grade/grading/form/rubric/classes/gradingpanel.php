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

    protected $values;

    protected $mode;

    protected $rubric;

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

        $this->values = $value;
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

    protected function set_mode() {
        $this->mode = \gradingform_rubric_controller::DISPLAY_EVAL;
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

    /*
     * This should be handled in the renderable in the coming future.
     */
    protected function show_description_teacher() {
        if(!empty($this->instanceoptions['showdescriptionteacher'])) {
            return $this->instance->get_controller()->get_formatted_description();
        }
        return false;
    }

    protected function criteria_mapper() {
        $builtcriteria = array_map(function($criterion) {
            if(isset($this->values['criteria'][$criterion['id']])) {
                $criterionvalue = $this->values['criteria'][$criterion['id']];
            } else {
                $criterionvalue = null;
            }
            $criterion['criteria-id'] = 'advancedgrading-criteria-';
            $index = 1;
            return $this->levels_mapper($criterion, $criterionvalue, $index);

        }, $this->get_criteria());
        return $builtcriteria;
    }

    protected function levels_mapper($criterion, $criterionvalue, $index) {
        $criterion['levels'] = array_map(function($level) use (&$criterion, &$criterionvalue) {
            $level['checked'] = (isset($criterionvalue['levelid']) && ((int)$criterionvalue['levelid'] === (int)$level['id']));
            if ($level['checked'] && (
                $this->mode == \gradingform_rubric_controller::DISPLAY_EVAL_FROZEN ||
                $this->mode == \gradingform_rubric_controller::DISPLAY_REVIEW ||
                $this->mode == \gradingform_rubric_controller::DISPLAY_VIEW)
            ) {
                $level['criterion']['checked'] = true;
                //in mode DISPLAY_EVAL the class 'checked' will be added by JS if it is enabled. If JS is not enabled, the 'checked' class will only confuse
            }
            if (isset($criterionvalue['savedlevelid']) && ((int)$criterionvalue['savedlevelid'] === (int)$level['id'])) {
                $level['criterion']['currentchecked'] = true;
            }
            $level['criterion']['tdwidth'] = round(100/count($criterion['levels']));

            if (!isset($level['id'])) {
                $level = array('id' => '{LEVEL-id}', 'definition' => '{LEVEL-definition}', 'score' => '{LEVEL-score}', 'class' => '{LEVEL-class}', 'checked' => false);
            } else {
                foreach (array('score', 'definition', 'class', 'checked', 'index') as $key) {
                    // set missing array elements to empty strings to avoid warnings
                    if (!array_key_exists($key, $level)) {
                        $level['criterion'][$key] = '';
                    }
                }
            }

            if ($this->mode == \gradingform_rubric_controller::DISPLAY_EVAL) {
                $level['criterion']['radio'] = [
                    'type' => 'radio',
                    'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition',
                    'name' => '{NAME}[criteria][{CRITERION-id}][levelid]',
                    'value' => $level['id']
                ];
                if ($level['checked']) {
                    $level['criterion']['radio']['checked'] = true;
                }
            }

            $level['criterion']['score-id'] = '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-score';
            $level['criterion']['score-value'] = $level['score'];
            $level['criterion']['definition-value'] = $level['definition'];
            $level['criterion']['definition-id'] = '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition-container';
            if($this->mode != \gradingform_rubric_controller::DISPLAY_EDIT_FULL &&
                $this->mode != \gradingform_rubric_controller::DISPLAY_EDIT_FROZEN) {

                $level['criterion']['tabindex'] = '0';
                $levelinfo = new \stdClass();
                $levelinfo->definition = s($level['definition']);
                $levelinfo->score = $level['score'];
                $tdattributes['criterion']['aria-label'] = get_string('level', 'gradingform_rubric', $levelinfo);

                if ($this->mode != \gradingform_rubric_controller::DISPLAY_PREVIEW &&
                    $this->mode != \gradingform_rubric_controller::DISPLAY_PREVIEW_GRADED) {
                    // Add role of radio button to level cell if not in edit and preview mode.
                    $level['role'] = 'radio';
                    if ($level['checked']) {
                        $level['criterion']['aria-checked'] = 'true';
                    } else {
                        $level['criterion']['aria-checked'] = 'false';
                    }
                }
            }

            $displayscore = true;
            if (!$this->instanceoptions['showscoreteacher'] && in_array($this->mode, array(\gradingform_rubric_controller::DISPLAY_EVAL, \gradingform_rubric_controller::DISPLAY_EVAL_FROZEN, \gradingform_rubric_controller::DISPLAY_REVIEW))) {
                $displayscore = false;
            }
            if (!$this->instanceoptions['showscorestudent'] && in_array($this->mode, array(\gradingform_rubric_controller::DISPLAY_VIEW, \gradingform_rubric_controller::DISPLAY_PREVIEW_GRADED))) {
                $displayscore = false;
            }
            if ($displayscore) {
                if (isset($level['error_score'])) {
                    $level['criterion']['scoreerror'] = true;
                }
            }
            $level['table-id'] = 'advancedgrading-criteria-1-levels-table';
            $level['table-row-id'] = 'advancedgrading-criteria-1-levels';
            return $level;
        }, $criterion['levels']);
        return $criterion;
    }

    public function build_for_template($page) {
        global $OUTPUT;

        $this->currentinstance = $this->get_instance();

        $this->get_options();

        // Till we figure out how we are gonna freeze stuff manually set the mode.
        $this->set_mode();

        $this->get_values();

        $criteria = $this->criteria_mapper();

        $this->rubric_builder();
        //print_object($this->rubric);


        $name = 'test';

        $canedit = false;
        $hasformfields = false;
        $renderable = new rubric_grading_panel_renderable(
            $name,
            $this->get_criteria(),
            $this->values,
            $this->is_invalid(),
            $this->instance_update_required(),
            $this->restored_from_draft(),
            $this->show_description_teacher(),
            $canedit,
            $hasformfields,
            $this->rubric
        );
        print_object($this->rubric);
        return $OUTPUT->render_from_template(
            'gradingform_rubric/shell',
            $renderable->export_for_template($this->instance->get_controller()->get_renderer($page))
        );
    }

    protected function rubric_builder() {
        switch ($this->mode) {
            case \gradingform_rubric_controller::DISPLAY_PREVIEW:
            case \gradingform_rubric_controller::DISPLAY_PREVIEW_GRADED:
                $this->rubric['rubric-mode'] = 'editor preview';  break;
            case \gradingform_rubric_controller::DISPLAY_EVAL:
                $this->rubric['rubric-mode'] = 'evaluate editable'; break;
            case \gradingform_rubric_controller::DISPLAY_EVAL_FROZEN:
                $this->rubric['rubric-mode'] = 'evaluate frozen';  break;
            case \gradingform_rubric_controller::DISPLAY_REVIEW:
                $this->rubric['rubric-mode'] = 'review';  break;
            case \gradingform_rubric_controller::DISPLAY_VIEW:
                $this->rubric['rubric-mode'] = 'view';  break;
        }

        $this->rubric['criteria'] = $this->criteria_mapper();
    }

}
