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
define(['jquery', 'core/popper'], function($, Popper) {

    return {
        init: function() {
            // Event handler for showing groups filter popover.
            $('#filter_groups_button').on('click', function() {
                // Create popper.
                new Popper(document.querySelector('#filter_groups_button'),
                    document.querySelector('#filter-groups-popover'));

                // Show popper.
                $('#filter-groups-popover').removeClass('d-none');
                this.$root.$emit('bv::show::popover', '#filter_groups_button');
            });

            // Event handler to save groups filter.
            $(document).on("click", ".filter-save", function(event) {
                var valuesToSave = [];

                // Find groups that have been checked.
                $(event.target.parentNode.parentNode).find('input[name="filtergroups[]"]:checked').each( function() {
                    valuesToSave.push($(this).val());
                });

                // Close the popover.
                $('#filter-groups-popover').addClass('d-none');
            });

            // Event handler to clear groups filter.
            $(document).on("click", ".filter-clear", function(event) {
                // Uncheck any checkboxes.
                $(event.target.parentNode.parentElement).find('input[type=checkbox]:checked').prop("checked", false);
            });
        }
    };
});
