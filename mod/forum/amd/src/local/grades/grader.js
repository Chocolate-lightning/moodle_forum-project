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
 * @module     core_grades/unified_grader
 * @package    core_grades
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Templates from 'core/templates';
// TODO import Notification from 'core/notification';
import Selectors from './local/grader/selectors';
import * as UserPicker from './local/grader/user_picker';
import {createLayout as createFullScreenWindow} from 'mod_forum/local/layout/fullscreen';

const templateNames = {
    grader: {
        app: 'mod_forum/local/grades/grader',
    },
};

const displayUserPicker = (root, html) => {
    window.console.log(Selectors);
    root.querySelector(Selectors.regions.pickerRegion).append(html);
};

const registerEventListeners = (graderLayout) => {
    const graderContainer = graderLayout.getContainer();
    graderContainer.addEventListener('click', (e) => {
        if (e.target.matches(Selectors.buttons.toggleFullscreen)) {
            // TODO the user should not listen to button clicks specifically.
            e.stopImmediatePropagation();
            e.preventDefault();

            graderLayout.toggleFullscreen();
        } else if (e.target.matches(Selectors.buttons.closeGrader)) {
            // TODO the user should not listen to button clicks specifically.
            e.stopImmediatePropagation();
            e.preventDefault();

            graderLayout.close();
        }
    });
};

// Make this explicit rather than object
export const launch = async(getListOfUsers, getContentForUser, { // eslint-disable-line
    initialUserId = 0, // eslint-disable-line
} = {}) => {

    const [
        graderLayout,
        graderHTML,
        userList, // eslint-disable-line
    ] = await Promise.all([
        createFullScreenWindow({fullscreen: false, showLoader: false}),
        Templates.render(templateNames.grader.app, {}),
        getListOfUsers(),
    ]);
    const graderContainer = graderLayout.getContainer();

    Templates.replaceNodeContents(graderContainer, graderHTML, '');
    registerEventListeners(graderLayout);
    // TODO const [pickerHTML] = await Promise.all([UserPicker.buildPicker(userList, initialUserId)]);
    const pickerHTML = await UserPicker.buildPicker(userList, initialUserId);
    displayUserPicker(graderContainer, pickerHTML);
};
