import Templates from 'core/templates';
import Selectors from './user_picker/selectors';
//import PubSub from 'core/pubsub';

const renderNavigator = () => {
    return Templates.render('mod_forum/local/grades/local/grader/user_picker', {});
};

const renderUserChange = (context) => {
    return Templates.render('mod_forum/local/grades/local/grader/user_picker/user', context);
};

const bindEvents = (root, users, currentUserIndex) => {
    root.addEventListener('click', (e) => {
        if (e.target.matches(Selectors.actions.changeUser)) {
            currentUserIndex += parseInt(e.target.dataset.direction);
            showUser(root, users, currentUserIndex);
        }
    });
};

const showUser = async(root, users, currentUserIndex) => {
    const user = {
        ...users[currentUserIndex],
        total: users.length,
        displayIndex: currentUserIndex + 1,
    };
    const html = await renderUserChange(user);
    const userRegion = root.querySelector(Selectors.regions.userRegion);
    Templates.replaceNodeContents(userRegion, html, '');
};

export const buildPicker = async (users, currentUserID) => {
    let root = document.createElement('div');

    const [html] = await Promise.all([renderNavigator()]);
    Templates.replaceNodeContents(root, html, '');

    const currentUserIndex = users.findIndex((user) => {
        return user.id === parseInt(currentUserID);
    });

    showUser(root, users, currentUserIndex);

    //let [nextButton, previousButton] = cacheDom(html);

    bindEvents(root, users, currentUserIndex);

    return root;
};
