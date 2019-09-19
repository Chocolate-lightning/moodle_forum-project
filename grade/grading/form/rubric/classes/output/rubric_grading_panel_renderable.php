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
namespace gradingform_rubric\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use stdClass;
use templatable;
use renderer_base as renderer;

/**
 * Callable class to be used in our webservice.
 *
 * @package   gradingform_rubric
 * @copyright 2019 Mathew May <mathew.solutions>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rubric_grading_panel_renderable implements renderable, templatable {

    protected $name;

    protected $criteria;

    protected $values;

    protected $arevaluesinvalid;

    protected $instanceupdate;

    protected $rubrichaschanged;

    protected $teacherdescription;

    protected $canedit;

    protected $hasformfields;

    protected $builtobject;

    public function __construct(
        $name,
        $criteria,
        $values,
        $arevaluesinvalid,
        $instanceupdate,
        $rubrichaschanged,
        $teacherdescription,
        $canedit,
        $hasformfields,
        $builtobject
    ) {
        $this->name = $name;
        $this->criteria = $criteria;
        $this->values = $values;
        $this->arevaluesinvalid = $arevaluesinvalid;
        $this->instanceupdate = $instanceupdate;
        $this->rubrichaschanged = $rubrichaschanged;
        $this->teacherdescription = $teacherdescription;
        $this->canedit = $canedit;
        $this->hasformfields = $hasformfields;
        $this->builtobject = $builtobject;
    }

    /**
     * Convert the compiled criteria data into the structure we require.
     *
     * Remember: No business logic here.
     *
     * @return array
     */
    protected function get_criteria(): array {
        // The criteria we were passed is a nested object.
        // Extra the parts we want and add any current values to the data structure.
        $result = [];
        foreach ($this->criteria as $id => $criterion) {
            $result[] = [
                'id' => $id,
                'description' => format_text($criterion['description'], $criterion['descriptionformat']),
                'levels' => $this->get_levels_from_criterion($criterion),
            ];
        }

        return $result;
    }

    /**
     * Get the level definition for a single criterion.
     *
     * @param array $criterion
     * @return array
     */
    protected function get_levels_from_criterion(array $criterion): array {
        $result = [];
        foreach ($criterion['levels'] as $id => $level) {
            $result[] = [
                'id' => $id,
                'score' => $level['score'],
                'definition' => format_text($level['definition'], $level['definitionformat']),
                'checked' => $level['checked'],
            ];
        }

        return $result;
    }

    /**
     * Export the data.
     *
     * @param renderer $output
     * @return stdClass
     */
    public function export_for_template(renderer $renderer) {
        return (object) [
            // TODO Format string.
            'name' => $this->name,
            'criteria' => $this->get_criteria(),

            'arevaluesinvalid' => $this->arevaluesinvalid,

            'instanceupdate' => $this->instanceupdate,
            'rubrichaschanged' => $this->rubrichaschanged,
            'teacherdescription' => $this->teacherdescription,

            'canedit' => $this->canedit,
            'hasformfields' => $this->hasformfields,
        ];
    }
}
