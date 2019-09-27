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
 * @module     mod_forum/grades/expandconversation
 * @package    mod_forum
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as ForumSelectors from './grader/selectors';
import Repository from 'mod_forum/repository';
import Notification from "../../../../../lib/amd/src/notification";
import Templates from 'core/templates';
import * as Modal from 'core/modal_factory';
import * as ModalEvents from 'core/modal_events';

export const registerEventListeners = () => {
    const contentRegion = document.querySelector(ForumSelectors.posts);
    contentRegion.addEventListener('click', (e) => {
        const rootNode = findGradableNode(e.target);
        e.preventDefault();
        const postId = rootNode.dataset.postid;
        const discussionId = rootNode.dataset.discussionid;
        Repository.getDiscussionPosts(parseInt(discussionId))
            .then((context) => {
                return Modal.create({
                    title: context.name,
                    body: Templates.render('mod_forum/grades/grader/discussion/posts/post_modal', context),
                    type: Modal.types.CANCEL
                });
            })
            .then((modal) => {
                // Handle hidden event.
                modal.getRoot().on(ModalEvents.hidden, function() {
                    // Destroy when hidden.
                    modal.destroy();
                });
                modal.setLarge();
                modal.show();

                const root = document.querySelector(ForumSelectors.postModal);
                const element = root.querySelector(`#p${postId}`);

                element.scrollIntoView({behavior: "smooth"});
            })
            .catch(Notification.exception);
    });
};

const findGradableNode = (node) => {
    return node.closest(ForumSelectors.expandConversation);
};
