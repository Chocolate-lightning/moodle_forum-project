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
 * Allow the radio buttons on the rubric be unselected.
 *
 * @module     gradingform_rubric/grades/grader/deselectradio
 * @package    gradingform_rubric
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const init = (rootId) => {
    const rootNode = document.querySelector(`#${rootId}`);
    rootNode.addEventListener('click', (e) => {
        const radio = e.target.closest("input[type='radio']");
        let mutate = rootNode.querySelector(`#${radio.id}`);
        if (mutate.checked === true) {
            mutate.checked = false;
            mutate.setAttribute('aria-checked', 'false');
            mutate.setAttribute('tabindex', '-1');
        } else {
            mutate.checked = true;
            mutate.setAttribute('aria-checked', 'true');
            mutate.setAttribute('tabindex', '0');
        }
    });
};
