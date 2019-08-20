import Templates from 'core/templates';
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
    let paginator = html.querySelector('data-grader="paginator"');
    let nextButton = paginator.querySelector('[data-action="next-user"]');
    let previousButton = paginator.querySelector('[data-action="previous-user"]');
    return [nextButton, previousButton];
};

// Use the next index or the given index here.
const _nextUser = (items, index) => {
    index++;

    const context = {
        firstName: items[index].firstName,
        lastName: items[index].lastName,
        displayIndex: index++
    };

    _renderUserChange(context);

    // PubSub
    //callback({id: items[currentIndex].userid});
};

const _previousUser = (items, index) => {
    index--;

    const context = {
        firstName: items[index].firstName,
        lastName: items[index].lastName,
        displayIndex: index++
    };

    _renderUserChange(context);

    // PubSub
    //callback({id: items[currentIndex].userid});
};

const _bindEvents = (nextButton, previousButton) => {
    nextButton.on('click', _nextUser);
    previousButton.on('click', _previousUser);
};

export const buildPicker = async (items, index) => {
    let context = _init(items, index);

    let [html, js] = await Promise.all(_renderNavigator(context));

    let [nextButton, previousButton] = _cacheDom(html);

    _bindEvents(nextButton, previousButton);

    return [html, js];
};
