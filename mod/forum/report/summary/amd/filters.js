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
define(['jquery', 'core/popper'], function($, popper) {

    return {
        init: function() {
            //TODO
            //TODO: also check whether this is how popper is correctly included, whether it's needed etc.
            //Also is bootstrap JS needed to be manually included, or is it part of the core libraries that are included?

            // Go popovers go!
            $('[data-toggle="filter-groups-popover"]').popover({
                html: true,
                title: function() {
                    return $("#filter-groups-title").html();
                },
                content: function() {
                    return $('#filter-groups-content').html();
                }
            });

            // Save filter.
            $(document).on("click", ".filter-save", function(event) {
                var valuesToSave = [];

                // Find groups that have been checked.
                $(event.target.parentNode.parentNode).find('input[name="filtergroups[]"]:checked').each( function() {
                    valuesToSave.push($(this).val());
                });

                // Store groups that have been checked in the form.
                $('#filter_groups_value').val(JSON.stringify(valuesToSave));

                // Close the popover.
                $('[data-toggle="filter-groups-popover"]').popover('hide');
            });

            $(document).on("click", ".filter-cancel", function(event) {
                // Uncheck any checkboxes.
                $(event.target.parentNode.parentElement).find('input[type=checkbox]:checked').prop("checked", false);
            });

        }
    };
});
