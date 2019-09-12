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
 * Web services relating to fetching of a rubric for the grading panel.
 *
 * @package    gradingform_rubric
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types = 1);

namespace gradingform_rubric\grades\grader\gradingpanel\external;

use coding_exception;
use context;
use core_user;
use core_grades\component_gradeitem as gradeitem;
use core_grades\component_gradeitems;
use external_api;
use external_format_value;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use gradingform_rubric\gradingpanel;
use required_capability_exception;
use stdClass;

/**
 * Web services relating to fetching of a rubric for the grading panel.
 *
 * @package    gradingform_rubric
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fetch extends external_api {

    /**
     * Describes the parameters for fetching the grading panel for a simple grade.
     *
     * @return external_function_parameters
     * @since Moodle 3.8
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters ([
            'component' => new external_value(
                PARAM_ALPHANUMEXT,
                'The name of the component',
                VALUE_REQUIRED
            ),
            'contextid' => new external_value(
                PARAM_INT,
                'The ID of the context being graded',
                VALUE_REQUIRED
            ),
            'itemname' => new external_value(
                PARAM_ALPHANUM,
                'The grade item itemname being graded',
                VALUE_REQUIRED
            ),
            'gradeduserid' => new external_value(
                PARAM_INT,
                'The ID of the user show',
                VALUE_REQUIRED
            ),
        ]);
    }

    /**
     * Fetch the data required to build a grading panel for a simple grade.
     *
     * @param string $component
     * @param int $contextid
     * @param string $itemname
     * @param int $gradeduserid
     * @return array
     * @since Moodle 3.8
     */
    public static function execute(string $component, int $contextid, string $itemname, int $gradeduserid): array {
        global $USER;

        [
            'component' => $component,
            'contextid' => $contextid,
            'itemname' => $itemname,
            'gradeduserid' => $gradeduserid,
        ] = self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'contextid' => $contextid,
            'itemname' => $itemname,
            'gradeduserid' => $gradeduserid,
        ]);

        // Validate the context.
        $context = context::instance_by_id($contextid);
        self::validate_context($context);

        // Validate that the supplied itemname is a gradable item.
        if (!component_gradeitems::is_valid_itemname($component, $itemname)) {
            throw new coding_exception("The '{$itemname}' item is not valid for the '{$component}' component");
        }

        // Fetch the gradeitem instance.
        $gradeitem = gradeitem::instance($component, $context, $itemname);

        if ('rubric' !== $gradeitem->get_advanced_grading_method()) {
            throw new moodle_exception(
                "The {$itemname} item in {$component}/{$contextid} is not configured for advanced grading with a rubric"
            );
        }

        // Fetch the actual data.
        $gradeduser = \core_user::get_user($gradeduserid);

        return self::get_fetch_data($gradeitem, $gradeduser);
    }

    /**
     * Get the data to be fetched.
     *
     * @param component_gradeitem $gradeitem
     * @return array
     */
    public static function get_fetch_data(gradeitem $gradeitem, stdClass $gradeduser): array {
        global $USER, $PAGE;

        $grade = $gradeitem->get_grade_for_user($gradeduser, $USER);
        $instance = $gradeitem->get_advanced_grading_instance($USER, $grade);

        $gradingpanel = new gradingpanel($instance, true);
        $datareturn = $gradingpanel->get_data($PAGE);
        return [
            'templatename' => 'gradingform_rubric/grades/grader/gradingpanel',
            'grade' => [
                'instanceid' => $instance->get_id(),
                'criteria' => $datareturn->criteria,
                'name' => $datareturn->name,
                'rubricmode' => $datareturn->rubricmode,
                'arevaluesinvalid' => $datareturn->arevaluesinvalid,
                'instanceupdate' => $datareturn->instanceupdate,
                'rubrichaschanged' => $datareturn->rubrichaschanged,
                'teacherdescription' => $datareturn->teacherdescription,
                'canedit' => $datareturn->canedit,
                'hasformfields' => $datareturn->hasformfields,
                'timecreated' => $grade->timecreated,
                'timemodified' => $grade->timemodified,
            ],
            'warnings' => [],
        ];
    }


    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     * @since Moodle 3.8
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'templatename' => new external_value(PARAM_SAFEPATH, 'The template to use when rendering this data'),
            'grade' => new external_single_structure([
                'instanceid' => new external_value(PARAM_INT, 'The id of the current grading instance'),
                'name' => new external_value(PARAM_RAW, 'Name of the Rubric'),
                'rubricmode' => new external_value(PARAM_RAW, 'The mode i.e. evaluate editable'),
                'arevaluesinvalid' => new external_value(PARAM_BOOL, 'Check if the current grades are valid'),
                'instanceupdate' => new external_value(PARAM_BOOL, 'Has this been updated'),
                'rubrichaschanged' => new external_value(PARAM_BOOL, 'Has the Rubric changed'),
                'teacherdescription' => new external_value(PARAM_RAW, 'Description for the teacher'),
                'canedit' => new external_value(PARAM_BOOL, 'Can the user edit this'),
                'hasformfields' => new external_value(PARAM_BOOL, 'Does this have form fields'),
                'criteria' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'ID of the Criteria'),
                        'description' => new external_value(PARAM_RAW, 'Description of the Criteria'),
                        'showremark' => new external_value(PARAM_INT, 'Show the textbox of remark'),
                        'levels' => new external_multiple_structure(new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'ID of level'),
                            'criterionid' => new external_value(PARAM_INT, 'ID of the criterion this matches to'),
                            'score' => new external_value(PARAM_INT, 'What this level is worth'),
                            'definition' => new external_value(PARAM_RAW, 'Definition of the level'),
                            'checked' => new external_value(PARAM_BOOL, 'Selected flag'),
                            'currentchecked' => new external_value(PARAM_BOOL, 'Currently selected level'),
                        ])),
                    ])
                ),
                'timecreated' => new external_value(PARAM_INT, 'The time that the grade was created'),
                'timemodified' => new external_value(PARAM_INT, 'The time that the grade was last updated'),
            ]),
            'warnings' => new external_warnings(),
        ]);
    }
}
