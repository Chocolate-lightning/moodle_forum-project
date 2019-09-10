import Templates from 'core/templates';

export const createPicker = (items, startIndex, callback) => {
    let currentIndex = startIndex;
    const context = {
        name: `${items[0].firstname} ${items[0].lastname}`,
        displayIndex: currentIndex + 1,
        total: items.length
    };
    return Templates.render('mod_forum/user_navigator', context)
        .then((html) => {
            let widget = document.createElement('div');
            widget.dataset.graderreplace = "grading-panel-display";
            widget.innerHTML = html;

            let nameElement = widget.querySelector('[data-region="name"]');
            const indexNumber = widget.querySelector('[data-region="index"]');
            const nextButton = widget.querySelector('[data-action="next-user"]');
            const previousButton = widget.querySelector('[data-action="previous-user"]');

            nameElement.innerText = `${items[currentIndex].firstname} ${items[currentIndex].lastname}`;
            indexNumber.innerText = context.displayIndex;
            callback(currentIndex, {id: items[currentIndex].userid});
            nextButton.addEventListener('click', () => {
                currentIndex++;
                nameElement.innerText = `${items[currentIndex].firstname} ${items[currentIndex].lastname}`;
                indexNumber.innerText = currentIndex + 1;
                callback(currentIndex, {id: items[currentIndex].userid});
            });

            previousButton.addEventListener('click', () => {
                currentIndex--;
                nameElement.innerText = `${items[currentIndex].firstname} ${items[currentIndex].lastname}`;
                indexNumber.innerText = currentIndex + 1;
                callback(currentIndex, {id: items[currentIndex].userid});
            });

            return widget;
        });
};
