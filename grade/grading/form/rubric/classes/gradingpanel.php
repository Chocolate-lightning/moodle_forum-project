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
 * Build rubric content before we render it out.
 *
 * @package   gradingform_rubric
 * @copyright 2019 Mathew May <mathew.solutions>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace gradingform_rubric;

use gradingform_rubric\output\rubric_grading_panel_renderable;

class gradingpanel {

    protected $instance;

    public function __construct($instance) {
        $this->instance = $instance;
    }

    public function build_for_template($page) {
        global $OUTPUT;

        $name = 'test';
        $values = [
            ['test' => true],
            ['fake' => false],
        ];
        $canedit = false;
        $hasformfields = false;
        $renderable = new rubric_grading_panel_renderable($name, $values, $canedit, $hasformfields);
        return $OUTPUT->render_from_template(
            'gradingform_rubric/shell',
            $renderable->export_for_template($this->instance->get_controller()->get_renderer($page))
        );
    }

}
