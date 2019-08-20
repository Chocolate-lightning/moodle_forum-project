import Templates from 'core/templates';
//import PubSub from 'core/pubsub';

const _init = (items, index) => {
    // TODO generate the full name from PHP function.
    this.index = index;
    this.items = items;

    this.context = {
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

const  _cacheDom = () => {
    this.paginator = document.querySelector('data-grader="paginator"');
    this.nextButton = this.paginator.querySelector('[data-action="next-user"]');
    this.previousButton = this.paginator.querySelector('[data-action="previous-user"]');
};

const nextUser = () => {
    this.index++;

    const context = {
        firstName: this.items[this.index].firstName,
        lastName: this.items[this.index].lastName,
        displayIndex: this.index++
    };

    _renderUserChange(context);

    // PubSub
    //callback({id: items[currentIndex].userid});
};

const previousUser = () => {
    this.index--;

    const context = {
        firstName: this.items[this.index].firstName,
        lastName: this.items[this.index].lastName,
        displayIndex: this.index++
    };

    _renderUserChange(context);

    // PubSub
    //callback({id: items[currentIndex].userid});
};

const _bindEvents = () => {
    this.nextButton.on('click', nextUser);
    this.previousButton.on('click', previousUser);
};

export const buildPicker = (items, index) => {
    _init(items, index);

    _renderNavigator(this.context);

    _cacheDom();

    _bindEvents();
};
