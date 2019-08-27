import Templates from 'core/templates';
import Selectors from './user_picker/selectors';
//import PubSub from 'core/pubsub';

const _init = (items, index) => {
    // TODO generate the full name from PHP function.

    return {
        firstName: items[index].firstname,
        lastName: items[index].lastname,
        displayindex: index + 1,
        total: items.length,
    };
};

const _renderNavigator = (context) => {
    return Templates.render('mod_forum/local/grades/unified_grader/user_navigator', context);
};

const _renderUserChange = (context) => {
    return Templates.render('mod_forum/local/grades/unified_grader/user_navigator_user', context);
};

const  _cacheDom = (html) => {
    let widget = document.createElement('div');
    widget.innerHTML = html;
    let paginator = widget.querySelector('[data-grader="paginator"]');
    let nextButton = paginator.querySelector('[data-action="next-user"]');
    let previousButton = paginator.querySelector('[data-action="previous-user"]');
    return [nextButton, previousButton];
};

// Use the next index or the given index here.
const _nextUser = async (items, index) => {
    index++;

    items[index].displayIndex = index++;

    let [html, js] = await Promise.all([_renderUserChange(items[index])]);
    Templates.replaceNodeContents(Selectors.regions.paginatorReplace, html, js);

    // PubSub
};

const _previousUser = async (items, index) => {
    index--;

    items[index].displayIndex = index++;

    let [html, js] = await Promise.all([_renderUserChange(items[index])]);
    Templates.replaceNodeContents(Selectors.regions.paginatorReplace, html, js);

    // PubSub
};

const _bindEvents = (nextButton, previousButton, items, index) => {
    nextButton.addEventListener('click', function() {
        _nextUser(items, index);
    });
    previousButton.addEventListener('click', function() {
        _previousUser(items, index);
    });
};

export const buildPicker = async (items, index) => {
    let context = _init(items, index);

    let [html, js] = await Promise.all([_renderNavigator(context)]);

    let [nextButton, previousButton] = _cacheDom(html);

    _bindEvents(nextButton, previousButton, items, index);

    return [html, js];
};
