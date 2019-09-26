<?php
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
 * The mform used by the forum summary report dates filter.
 *
 * @package forumreport_summary
 * @copyright 2019 Michael Hawkins <michaelh@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace forumreport_summary;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The mform class for creating the forum summary report dates filter.
 *
 * @copyright 2019 Michael Hawkins <michaelh@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dates_filter_form extends \moodleform {

    /**
     * Build the editor options using the given context.
     *
     * @param \context $context A Moodle context
     * @return array
     */
//    public static function build_editor_options(\context $context) {
//        global $CFG;
//
//        return [
//            'context' => $context,
//            'maxfiles' => EDITOR_UNLIMITED_FILES,
//            'maxbytes' => $CFG->maxbytes,
//            'noclean' => true,
//            'autosave' => false
//        ];
//    }

    /**
     * The form definition
     */
    public function definition() {
        //global $PAGE;

        $mform = $this->_form;
        //$starttime = isset($this->_customdata['starttime']) ? $this->_customdata['starttime'] : 0;

        //$mform->setDisableShortforms();
        //$mform->disable_form_change_checker();

        // Empty string so that the element doesn't get rendered.
        //$mform->addElement('header', 'general', '');

        //$this->add_default_hidden_elements($mform);

        // Event time start field.
        $mform->addElement('date_selector', 'filterdatefrompopover', get_string('from'), ['optional' => true]);
        $mform->addElement('date_selector', 'filterdatetopopover', get_string('to'), ['optional' => true]);

        // Add the javascript required to enhance this mform.
        //$PAGE->requires->js_call_amd('core_calendar/event_form', 'init', [$mform->getAttribute('id')]);
    }

    /**
     * Add the list of hidden elements that should appear in this form each
     * time. These elements will never be visible to the user.
     *
     * @param MoodleQuickForm $mform
     */
//    protected function add_default_hidden_elements($mform) {
//        global $USER;
//
//        // Add some hidden fields.
//        $mform->addElement('hidden', 'id');
//        $mform->setType('id', PARAM_INT);
//        $mform->setDefault('id', 0);
//
//        $mform->addElement('hidden', 'userid');
//        $mform->setType('userid', PARAM_INT);
//        $mform->setDefault('userid', $USER->id);
//
//        $mform->addElement('hidden', 'modulename');
//        $mform->setType('modulename', PARAM_INT);
//        $mform->setDefault('modulename', '');
//
//        $mform->addElement('hidden', 'instance');
//        $mform->setType('instance', PARAM_INT);
//        $mform->setDefault('instance', 0);
//
//        $mform->addElement('hidden', 'visible');
//        $mform->setType('visible', PARAM_INT);
//        $mform->setDefault('visible', 1);
//    }
}
