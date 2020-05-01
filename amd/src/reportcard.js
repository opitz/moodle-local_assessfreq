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
 * Javascript for report card display and processing.
 *
 * @package    local_assessfreq
 * @copyright  2020 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    ['core/fragment', 'core/templates', 'core/notification', 'core/ajax'],
    function(Fragment, Templates, Notification, Ajax) {

    /**
     * Module level variables.
     */
    var Reportcard = {};
    var contextid;
    var yearselect;

    /**
     * Generic handler to persist user preferences
     *
     * @param {string} type The name of the attribute you're updating
     * @param {string} value The value of the attribute you're updating
     */
     function updateUserPreferences(type, value) {
        var request = {
            methodname: 'core_user_update_user_preferences',
            args: {
                preferences: [
                    {
                        type: type,
                        value: value
                    }
                ]
            }
        };

        Ajax.call([request])[0]
        .fail(function() {
            Notification.exception(new Error('Failed to update user preference'));
        });
    }

    function assessByMonth() {
        var cardid = 'local-assessfreq-assess-due-month';
        var cardElement = document.getElementById(cardid);

        // Call an ajax method that returns all the info we need.
        // This includes:
        // Lang strings for the block.
        // Values for year selection.
        // Initial data set.

        var params = {'data': JSON.stringify([])};
        Fragment.loadFragment('local_assessfreq', 'get_assess_by_month', contextid, params)
        .done(function(response) {
            // Load card body.
            window.console.log(response);
            return;
        }).fail(function() {
            Notification.exception(new Error('Failed to load card year filter'));
            return;
        }).then(function() {
            // And then?
        });
    }

    function yearButtonAction(event) {
        var element = event.target;

        if (element.tagName.toLowerCase() === 'a' && element.dataset.year != yearselect) { // Only act on certain elements.
            yearselect = element.dataset.year;

            // Save selection as a user preference.
            updateUserPreferences('local_assessfreq_overview_year_preference', yearselect);

            // Update card data based on selected year.
            var yeartitle = document.getElementById('local-assessfreq-report-overview')
                                .getElementsByClassName('local-assessfreq-year')[0];
            yeartitle.innerHTML = yearselect;
        }
    }

    /**
     * Initialise method for report card rendering.
     *
     * @param {integer} context The current context id.
     */
    Reportcard.init = function(context) {
        contextid = context;

        var cardsYearSelectElement = document.getElementById('local-assessfreq-cards-year');
        yearselect = cardsYearSelectElement.getElementsByClassName('active')[0].dataset.year;
        cardsYearSelectElement.addEventListener("click", yearButtonAction);

        assessByMonth(); // Process loading for the assessments by month card.

    };

    return Reportcard;
});
