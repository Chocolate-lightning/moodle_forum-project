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
 * Rubric repository class to encapsulate all of the AJAX requests.
 *
 * @module     gradingform_rubric/repository
 * @package    gradingform_rubric
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';

/**
 * Testing thing.
 *
 * @return {*|Promise}
 */
const testTesting = () => {
    const request = {
        methodname: 'grading_form_rubric_fetch_rubric_testing',
        args: {
            cmid: 107,
            component: 'mod_assign',
            area: 'submissions',
            areaid: 1,
        },
    };
    return Ajax.call([request])[0];
};

export const functions = () => {
    return testTesting();
};
