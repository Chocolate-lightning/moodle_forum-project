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
     * @return  array
     */
    public static function testing(string $test) {
        global $USER, $DB;
        // Validate the parameter.
        $params = self::validate_parameters(self::testing_parameters(), [
                'test' => $test,
        ]);
        $warnings = [];

        return [
            'test' => $test,
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
                'test' => new external_value(
                        PARAM_ALPHA, 'Wow a test', VALUE_REQUIRED)
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
