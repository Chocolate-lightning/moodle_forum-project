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

            /**
             * Generic filter handlers.
             */

            // Event handler to clear filters.
            $(document).on("click", ".filter-clear", function(event) {
                // Uncheck any checkboxes.
                $(event.target.parentNode.parentElement).find('input[type=checkbox]:checked').prop("checked", false);

                // Check the default checkbox.
                $(event.target.parentNode.parentElement).find('input[type=checkbox][value="0"]').prop("checked", true);
            });

            /**
             * Groups filter specific handlers.
             */

            // Set filter button text.
            var setGroupFilterText = function (groupCount) {
                if ($('#filtergroups0').prop("checked")) {
                    groupCount = 'all'; //TODO: Lang string this boi
                }

               $('#filter_groups_button').text($('#groups_title_base').val() + ' (' + groupCount + ')');
            };

            $('#filter-groups-popover input[name="filtergroups[]"]').on('click', function(event) {
                // If checking 'all', uncheck others.
                var filterid = event.target.value;

                // Uncheck other groups if 'all' selected.
                if (filterid == 0) {
                    if ($('#' + event.target.id).prop('checked')) {
                        $(event.target.parentNode).find('input[name="filtergroups[]"]:checked').each( function() {
                            if ($(this).val() != 0) {
                                $(this).prop('checked', false);
                            }
                        });
                    }
                } else {
                    // Uncheck 'all' if another group is checked.
                    $('#filtergroups0').prop('checked', false);
                }
            });

            // Event handler for showing groups filter popover.
            $('#filter_groups_button').on('click', function() {
                // Create popover.
                new Popper(document.querySelector('#filter_groups_button'),
                    document.querySelector('#filter-groups-popover'));

                // Show popover.
                $('#filter-groups-popover').removeClass('d-none');
                this.$root.$emit('bv::show::popover', '#filter_groups_button');
            });

            // Event handler to save groups filter.
            $(document).on("click", ".filter-save", function() {
                // Close the popover.
                $('#filter-groups-popover').addClass('d-none');

                // Update group count on button.
                var groupsCount = $('#filter-groups-popover').find('input[name="filtergroups[]"]:checked').length;
                setGroupFilterText(groupsCount);
            });
        }
    };
});
