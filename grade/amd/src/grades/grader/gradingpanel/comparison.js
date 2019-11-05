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
 * Compare a given form's values and its previously set data attributes.
 *
 * @module     core_grades/grades/grader/gradingpanel/comparison
 * @package    core_grades
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Iterate over the form elements till we either run out or get a true value.
 *
 * @param {HTMLElement} form
 * @return {Boolean}
 */
export const compareData = (form) => {
    return Array.prototype.some.call(form.elements, input => {
        if (input.type === 'submit' || input.type === 'button') {
            return false;
        }

        if (input.type === 'radio' || input.type === 'checkbox') {
            return input.defaultChecked !== input.checked;
        }

        if (input.type === 'textarea' || input.type === 'number') {
            return input.defaultValue !== input.value;
        }

        if (input.type === 'select-one') {
            if (input.value === "-1") {
                return false;
            }

            return Array.prototype.some.call(input.options, (option) => {
                return option.defaultSelected !== option.selected;
            });
        }
    });
};
