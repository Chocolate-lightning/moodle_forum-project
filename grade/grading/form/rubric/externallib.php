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

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

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
    public static function testing(int $cmid, string $component, string $area, int $areaid) {
        // Validate the parameter.
        $params = self::validate_parameters(self::testing_parameters(), [
                'cmid' => $cmid,
                'component' => $component,
                'area' => $area,
                'areaid' => $areaid,
        ]);
        $warnings = [];

        $modulecontext = context_module::instance_by_id($params['id']);
        $controller = new gradingform_rubric_controller($modulecontext, $params['component'], $params['area'], $params['areaid']);
        print_object($controller);

        return [
            'test' => $params['component'],
            'warnings' => $warnings,
        ];
    }

    /**
     * Describe the post parameters.
     *
     * @return external_function_parameters
     */
    public static function testing_parameters() {
        return new external_function_parameters ([
            'cmid' => new external_value(
                    PARAM_INT, 'ID of the context this rubric belongs to', VALUE_REQUIRED),
            'component' => new external_value(
                PARAM_ALPHA, 'Name of the component the rubric belongs to', VALUE_REQUIRED),
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
    public static function testing_returns() {
        return new external_single_structure([
            'test' => new external_value(PARAM_RAW, 'Test'),
            'warnings' => new external_warnings(),
        ]);
    }

}
