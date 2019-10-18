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
 * Module responsible for handling forum summary report filters.
 *
 * @module     forumreport_summary/filters
 * @package    forumreport_summary
 * @copyright  2019 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Popper from 'core/popper';
import CustomEvents from 'core/custom_interaction_events';
import Selectors from 'forumreport_summary/selectors';
import Y from 'core/yui';
import Ajax from 'core/ajax';

export const init = (root) => {
    let jqRoot = $(root);

    // Hide loading spinner and show report once page is ready.
    // This ensures filters can be applied when sorting by columns.
    $(document).ready(function() {
        $('.loading-icon').hide();
        $('#summaryreport').removeClass('hidden');
    });

    // Generic filter handlers.

    // Called to override click event to trigger a proper generate request with filtering.
    var generateWithFilters = (event) => {
        var newLink = $('#filtersform').attr('action');

        if (event) {
            event.preventDefault();

            let filterParams = event.target.search.substr(1);
            newLink += '&' + filterParams;
        }

        $('#filtersform').attr('action', newLink);
        $('#filtersform').submit();
    };

    // Override 'reset table preferences' so it generates with filters.
    $('.resettable').on("click", "a", function(event) {
        generateWithFilters(event);
    });

    // Override table heading sort links so they generate with filters.
    $('thead').on("click", "a", function(event) {
        generateWithFilters(event);
    });

    // Override pagination page links so they generate with filters.
    $('.pagination').on("click", "a", function(event) {
        generateWithFilters(event);
    });

    // Submit report via filter
    var submitWithFilter = (containerelement) => {
        // Close the container (eg popover).
        $(containerelement).addClass('hidden');

        // Submit the filter values and re-generate report.
        generateWithFilters(false);
    };

    // Use popper to override date mform calendar position.
    var updateCalendarPosition = () => {
        let referenceElement = document.querySelector(Selectors.filters.date.popover),
            popperContent = document.querySelector(Selectors.filters.date.calendar);

        new Popper(referenceElement, popperContent, {placement: 'bottom'});
    };

    // Call when opening filter to ensure only one can be activated.
    var canOpenFilter = (event) => {
        if (document.querySelector('[data-openfilter="true"]')) {
            return false;
        }

        event.target.setAttribute('data-openfilter', "true");
        return true;
    };

    // Groups filter specific handlers.

    // Event handler for clicking select all groups.
    jqRoot.on(CustomEvents.events.activate, Selectors.filters.group.selectall, function() {
        let deselected = root.querySelectorAll(Selectors.filters.group.checkbox + ':not(:checked)');
        deselected.forEach(function(checkbox) {
            checkbox.checked = true;
        });
    });

    // Event handler for clearing filter by clicking option.
    jqRoot.on(CustomEvents.events.activate, Selectors.filters.group.clear, function() {
        // Clear checkboxes.
        let selected = root.querySelectorAll(Selectors.filters.group.checkbox + ':checked');
        selected.forEach(function(checkbox) {
            checkbox.checked = false;
        });
    });

    // Event handler for showing groups filter popover.
    jqRoot.on(CustomEvents.events.activate, Selectors.filters.group.trigger, function(event) {
        if (!canOpenFilter(event)) {
            return false;
        }

        // Create popover.
        var referenceElement = root.querySelector(Selectors.filters.group.trigger),
            popperContent = root.querySelector(Selectors.filters.group.popover);

        new Popper(referenceElement, popperContent, {placement: 'bottom'});

        // Show popover.
        popperContent.classList.remove('hidden');

        // Change to outlined button.
        referenceElement.classList.add('btn-outline-primary');
        referenceElement.classList.remove('btn-primary');

        // Let screen readers know that it's now expanded.
        referenceElement.setAttribute('aria-expanded', true);
        return true;
    });

    // Event handler to click save groups filter.
    jqRoot.on(CustomEvents.events.activate, Selectors.filters.group.save, function() {
        submitWithFilter('#filter-groups-popover');
    });

    // Dates filter specific handlers.

   // Event handler for showing dates filter popover.
    jqRoot.on(CustomEvents.events.activate, Selectors.filters.date.trigger, function(event) {
        if (!canOpenFilter(event)) {
            return false;
        }

        // Create popover.
        let referenceElement = root.querySelector(Selectors.filters.date.trigger),
            popperContent = root.querySelector(Selectors.filters.date.popover);

        new Popper(referenceElement, popperContent, {placement: 'bottom'});

        // Show popover and move focus.
        popperContent.classList.remove('hidden');
        popperContent.querySelector('[name="filterdatefrompopover[enabled]"]').focus();

        // Change to outlined button.
        referenceElement.classList.add('btn-outline-primary');
        referenceElement.classList.remove('btn-primary');

        // Let screen readers know that it's now expanded.
        referenceElement.setAttribute('aria-expanded', true);
        return true;
    });

    // Event handler to save dates filter.
    jqRoot.on(CustomEvents.events.activate, Selectors.filters.date.save, function() {
        // Populate the hidden form inputs to submit the data.
        let filtersform = document.forms.filtersform,
            datespopover = root.querySelector(Selectors.filters.date.popover),
            datefromobj = {
                'day': datespopover.querySelector('[name="filterdatefrompopover[day]"]').value,
                'month': datespopover.querySelector('[name="filterdatefrompopover[month]"]').value,
                'year': datespopover.querySelector('[name="filterdatefrompopover[year]"]').value,
                'enabled': datespopover.querySelector('[name="filterdatefrompopover[enabled]"]').checked ? 1 : 0
            },
            datetoobj = {
                'day': datespopover.querySelector('[name="filterdatetopopover[day]"]').value,
                'month': datespopover.querySelector('[name="filterdatetopopover[month]"]').value,
                'year': datespopover.querySelector('[name="filterdatetopopover[year]"]').value,
                'enabled': datespopover.querySelector('[name="filterdatetopopover[enabled]"]').checked ? 1 : 0
            },
            args = {
                datefrom: datefromobj,
                dateto: datetoobj
            },
            request = {
                methodname: 'forumreport_summary_get_timestamps',
                args: args
            };

        Ajax.call([request])[0].done(function(result) {
            if (result.warnings.length > 0) {
                // Display the error.
                let warningdiv = document.getElementById('dates-filter-warning');
                warningdiv.textContent = result.warnings[0].message;
                warningdiv.classList.remove('hidden');
                warningdiv.classList.add('d-block');
            } else {
                // Update the elements in the filter form.
                filtersform.elements['datefrom[timestamp]'].value = result.timestampfrom;
                filtersform.elements['datefrom[enabled]'].value =
                        datespopover.querySelector('[name="filterdatefrompopover[enabled]"]').checked ? 1 : 0;
                filtersform.elements['dateto[timestamp]'].value = result.timestampto;
                filtersform.elements['dateto[enabled]'].value =
                        datespopover.querySelector('[name="filterdatetopopover[enabled]"]').checked ? 1 : 0;

                // Disable the mform checker to prevent unsubmitted form warning to the user when closing the popover.
                Y.use('moodle-core-formchangechecker', function() {
                    M.core_formchangechecker.reset_form_dirty_state();
                });

                // Submit the filter values and re-generate report.
                submitWithFilter('#filter-dates-popover');
            }
        });
    });

    jqRoot.on("click", "#id_filterdatefrompopover_calendar", function() {
        updateCalendarPosition();
    });

    jqRoot.on("click", "#id_filterdatetopopover_calendar", function() {
        updateCalendarPosition();
    });
};
