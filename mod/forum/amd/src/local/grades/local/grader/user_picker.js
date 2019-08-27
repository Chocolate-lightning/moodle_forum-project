import Templates from 'core/templates';
import Selectors from './user_picker/selectors';

const renderNavigator = () => {
    return Templates.render('mod_forum/local/grades/local/grader/user_picker', {});
};

const renderUserChange = (context) => {
    return Templates.render('mod_forum/local/grades/local/grader/user_picker/user', context);
};

const bindEvents = (root, users, currentUserIndex, showUserCallback) => {
    root.addEventListener('click', (e) => {
        const button = e.target.closest(Selectors.actions.changeUser);
        if (button) {
            currentUserIndex += parseInt(button.dataset.direction);
            showUser(root, users, currentUserIndex, showUserCallback);
        }
    });
};

const showUser = async(root, users, currentUserIndex, showUserCallback) => {
    const user = {
        ...users[currentUserIndex],
        total: users.length,
        displayIndex: currentUserIndex + 1,
    };
    const [html] = await Promise.all([renderUserChange(user), showUserCallback(user)]);
    const userRegion = root.querySelector(Selectors.regions.userRegion);
    Templates.replaceNodeContents(userRegion, html, '');
};

export const buildPicker = async(users, currentUserID, showUserCallback) => {
    let root = document.createElement('div');

    const [html] = await Promise.all([renderNavigator()]);
    Templates.replaceNodeContents(root, html, '');

    const currentUserIndex = users.findIndex((user) => {
        return user.id === parseInt(currentUserID);
    });

    showUser(root, users, currentUserIndex, showUserCallback);

    bindEvents(root, users, currentUserIndex, showUserCallback);

    return root;
};
