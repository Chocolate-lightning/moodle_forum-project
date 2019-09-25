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
 * External Rubric API
 *
 * @package    gradingform_rubric
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use gradingform_rubric\gradingpanel;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/grade/grading/form/rubric/lib.php');

class gradingform_rubric_external extends external_api {

    /**
     * Get the rubric.
     *
     * @param stdClass $context the context of the form
     * @param string $component the frankenstyle name of the component
     * @param string $area the name of the gradable area
     * @param int $areaid the id of the gradable area record
     * @return  array
     */
    public static function fetch_rubric(int $cmid, string $component, string $area, int $areaid) {
        global $PAGE;
        // Validate the parameter.
        $params = self::validate_parameters(self::fetch_rubric_parameters(), [
                'cmid' => $cmid,
                'component' => $component,
                'area' => $area,
                'areaid' => $areaid,
        ]);
        $warnings = [];

        $modulecontext = context_module::instance($params['cmid']);
        $controller = new gradingform_rubric_controller($modulecontext, $params['component'], $params['area'], $params['areaid']);
        $rubricinstance = $controller->get_or_create_instance(0, 2, 1);
        $gradingpanel = new gradingpanel($rubricinstance, true);
        $PAGE->set_context($modulecontext);
        $datareturn = $gradingpanel->get_data($PAGE);

        return [
            'test' => $datareturn,
            'warnings' => $warnings,
        ];
    }

    /**
     * Describe the post parameters.
     *
     * @return external_function_parameters
     */
    public static function fetch_rubric_parameters() {
        return new external_function_parameters ([
            'cmid' => new external_value(
                    PARAM_INT, 'ID of the context this rubric belongs to', VALUE_REQUIRED),
            'component' => new external_value(
                PARAM_RAW, 'Name of the component the rubric belongs to', VALUE_REQUIRED),
            'area' => new external_value(
                PARAM_ALPHA, 'Name of the gradeable area', VALUE_REQUIRED),
            'areaid' => new external_value(
                PARAM_INT, 'ID of the gradeable area', VALUE_REQUIRED),

        ]);
    }

    /**
     * Describe the post return format.
     *
     * @return external_single_structure
     */
    public static function fetch_rubric_returns() {
        return new external_single_structure([
            'test' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_RAW, 'Name of '),
                    'rubric-mode' => new external_value(PARAM_RAW, 'Name of '),
                    'arevaluesinvalid' => new external_value(PARAM_INT, 'ID of '),
                    'instanceupdate' => new external_value(PARAM_INT, 'ID of '),
                    'rubrichaschanged' => new external_value(PARAM_INT, 'ID of '),
                    'teacherdescription' => new external_value(PARAM_RAW, 'Name of '),
                    'canedit' => new external_value(PARAM_INT, 'ID of '),
                    'hasformfields' => new external_value(PARAM_INT, 'ID of '),
                    'criteria' => new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'ID of '),
                        'description' => new external_value(PARAM_RAW, 'Name of '),
                        'aria-label' => new external_value(PARAM_RAW, 'Name of '),
                        'showremark' => new external_value(PARAM_INT, 'ID of '),
                        'levels' => new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'ID of '),
                            'criterionid' => new external_value(PARAM_INT, 'ID of '),
                            'score' => new external_value(PARAM_INT, 'ID of '),
                            'aria-label' => new external_value(PARAM_RAW, 'Name of '),
                            'definition' => new external_value(PARAM_RAW, 'Name of '),
                            'checked' => new external_value(PARAM_INT, 'ID of '),
                            'currentchecked' => new external_value(PARAM_INT, 'ID of '),
                        ]),

                    ]),



                ])),
            'warnings' => new external_warnings(),
        ]);
    }

}
