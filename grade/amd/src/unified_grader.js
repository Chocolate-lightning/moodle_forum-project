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
import {createLayout as createFullScreenWindow} from 'mod_forum/local/layout/fullscreen';

const templateNames = {
    grader: {
        app: 'core_grades/grading_app',
    },
};

const getHelpers = (config) => {
    let graderLayout = null;
    let graderContainer = null;
    let contentRegion = null;

    const displayContent = (html, js) => {
        return Templates.replaceNode(contentRegion, html, js);
    };

    const showUser = (userid) => {
        config
            .getContentForUserId(userid)
            .then(displayContent)
            .catch(Notification.exception);
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
            graderLayout.hideLoadingIcon();

            return;
        })
        .then(() => {
            contentRegion = graderContainer.querySelector(Selectors.regions.moduleReplace);

            return;
        })
        .then(() => {
            registerEventListeners();

            return;
        })
        .catch();
    };

    return {
        showUser,
        displayGrader,
    };
};

export const launch = (config) => {
    const {
        showUser,
        displayGrader,
    } = getHelpers(config);

    displayGrader().then(() => {
        if (config.initialUserId) {
            showUser(config.initialUserId);
        }

        return;
    })
    .catch();

    // You might instantiate the user selector here, and pass it the function displayContentForUser as the thing to call
    // when it has selected a user.
};
