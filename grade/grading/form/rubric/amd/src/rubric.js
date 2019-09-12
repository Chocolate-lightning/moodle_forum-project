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
 * This module will tie together all of the different calls the gradable module will make.
 *
 * @module     gradingform_rubric/rubric
 * @package    core_grades
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = (uid, options) => {
    const level = `#rubric-${options.name} .level`;
    const radio = `#rubric-${options.name} .radio`;
    registerActionListeners(level);
    displayChangesBasedOnData(level, radio);
};
const displayChangesBasedOnData = (level, radio) => {
    const radioElements = document.querySelectorAll(radio);
    radioElements.forEach((element) => {
        element.classList.add('d-none');
    });
    const levelElements = document.querySelectorAll(level);
    levelElements.forEach((element) => {
        if (element.querySelector('input[type=radio]').checked) {
            element.classList.add("checked");
        }
    });
};

const registerActionListeners = (level) => {
    document.addEventListener('click', (e) => {
        const button = e.target.closest(level);
        if (button) {
            levelClick(button);
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.defaultPrevented) {
            return;
        }
        if (level) {
            const button = e.target.closest(level);
            // Use key_codes module here?
            var key = event.key || event.keyCode;
            if (key === 'Enter' || key === 'enter' || key === 13) {
                levelClick(button);
            }
        }
    });
};

const levelClick = (element) => {
    //event.classList.contains('level');

    const parent = element.parentElement;
    const children = parent.childNodes;
    children.forEach((child) => {
        const radio = child.querySelector('input[type=radio]');
        if (child.isEqualNode(element)) {
            child.classList.add('checked');
            child.setAttribute('aria-checked', 'true');
            radio.setAttribute('checked', 'true');
        } else {
            child.classList.remove('checked');
            child.setAttribute('aria-checked', 'false');
            radio.removeAttribute("checked");
        }
    });
    return event;
};
