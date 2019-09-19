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

    protected $valuesinvalid;

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
        $valuesinvalid,
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
        $this->valuesinvalid = $valuesinvalid;
        $this->instanceupdate = $instanceupdate;
        $this->rubrichaschanged = $rubrichaschanged;
        $this->teacherdescription = $teacherdescription;
        $this->canedit = $canedit;
        $this->hasformfields = $hasformfields;
        $this->builtobject = $builtobject;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $data = new stdClass();

        /*$data->name = $this->name;
        $data->criteria = $this->criteria;
        $data->values = $this->values;
        $data->valuesinvalid = $this->valuesinvalid;
        $data->instanceupdate = $this->instanceupdate;
        $data->rubrichaschanged = $this->rubrichaschanged;
        $data->teacherdescription = $this->teacherdescription;
        $data->canedit = $this->canedit;
        $data->hasformfields = $this->hasformfields;*/


        return $this->builtobject;
    }
}
