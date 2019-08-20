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
import Notification from 'core/notification';
import Selectors from './selectors';
import * as UserPaginator from './new_unified_grader_user_paginator';
import {createLayout as createFullScreenWindow} from 'mod_forum/local/layout/fullscreen';

const templateNames = {
    grader: {
        app: 'mod_forum/local/grades/unified_grader/grading_app',
    },
};

const getHelpers = (config) => {
    let graderLayout = null;
    let graderContainer = null;
    let contentRegion = null;

    /*const displayContent = (html, js) => {
        let widget = document.createElement('div');
        widget.className = "grader-module-content-display col-sm-12";
        widget.dataset.replace = "grader-module-content";
        widget.innerHTML = html;
        return Templates.replaceNode(Selectors.regions.moduleReplace, widget, js);
    };*/

    const displayUsers = (html) => {
        return Templates.replaceNode(Selectors.regions.gradingReplace, html);
    };

    // Remove from bottom section userpicker rendered up top
    const getUsers = (cmid) => {
        return config
            .getUsersForCmidFunction(cmid)
            .catch(Notification.exception);
    };
    /*const showUser = (userid) => {
        config
            .getContentForUserId(userid)
            .then(displayContent)
            .catch(Notification.exception);
    };*/

    const renderUserPicker = (state) => {
        const userNames = state.map(user => ({firstname: user.firstname, lastname: user.lastname, userid: user.id}));
        const picker = UserPaginator.buildPicker(userNames, 0);
        return picker;

    };
    const registerEventListeners = () => {
        graderContainer.addEventListener('click', (e) => {
            if (e.target.matches(Selectors.buttons.toggleFullscreen)) {
                e.stopImmediatePropagation();
                e.preventDefault();

                graderLayout.toggleFullscreen();
            } else if (e.target.matches(Selectors.buttons.closeGrader)) {
                e.stopImmediatePropagation();
                e.preventDefault();

                graderLayout.close();
            }
        });
    };

    const displayGrader = () => {
        graderLayout = createFullScreenWindow({fullscreen: false});
        graderContainer = graderLayout.getContainer();

        return Templates.render(templateNames.grader.app, {})
            .then((html, js) => {
                Templates.replaceNodeContents(graderContainer, html, js);

                return graderContainer;
            })
            .then(() => {
                // Set user picker
                console.log('1');
                contentRegion = graderContainer.querySelector(Selectors.regions.moduleReplace);
                return;
            })
            .then(() => {
                console.log('2');
                registerEventListeners();

                return;
            })
            .then(() => {
                console.log('3');
                getUsers(config.cmid)
                    .then(state => {
                        console.log('4');
                        renderUserPicker(state.users)
                            .then((picker) => {
                                displayUsers(picker);
                            });
                    })
                    .catch();
            })
            .then(() => {
                graderLayout.hideLoadingIcon();
                return;
            })
            .catch();
    };

    return {
        displayGrader,
    };
};

// Make this explicit rather than object
export const launch = (config) => {
    const {
        displayGrader,
    } = getHelpers(config);

    displayGrader();
};
