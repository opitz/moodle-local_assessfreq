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
 * This file contains the class that handles testing of the assess by month class.
 *
 * @package    local_assessfreq
 * @copyright  2020 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */

namespace local_assessfreq\output;

use stdClass;

/**
 * This file contains the class that handles testing of the assess by month class.
 *
 * @package    local_assessfreq
 * @copyright  2020 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_assessfreq\output\assess_by_month_student
 */
class assess_by_month_student_test extends \advanced_testcase {

    /**
     *
     * @var stdClass $course Test course.
     */
    protected $course;

    /**
     * Set up conditions for tests.
     */
    public function setUp(): void {
        $this->resetAfterTest();

        // Create a course.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(
            array('format' => 'topics', 'numsections' => 3,
                'enablecompletion' => 1),
            array('createsections' => true));
        $this->course = $course;

        $version = get_config('moodle', 'version');

        if ($version < 2019052000) { // Versions less than 3.7 don't support forum due dates.
            set_config('modules', 'assign,choice,data,feedback,lesson,quiz,scorm,workshop', 'local_assessfreq');
        } else {
            set_config('modules', 'assign,choice,data,feedback,forum,lesson,quiz,scorm,workshop', 'local_assessfreq');
        }
    }


    /**
     * Test gett assess due by month chart method.
     */
    public function test_get_assess_due_chart() {
        global $DB;
        $year = 2020;

        // Make some records to put in the database;
        // Every month should have an increasing ammount of users.
        for ($i = 1; $i <= 12; $i++) {
            $record = new stdClass();
            $record->module = 'quiz';
            $record->instanceid = $i;
            $record->courseid = $this->course->id;
            $record->contextid = $i;
            $record->timestart = 0; // Start can be fake for this test.
            $record->timeend = 0; // End can be fake for this test.
            $record->endyear = $year;
            $record->endmonth = $i;
            $record->endday = 1;

            $eventid = $DB->insert_record('local_assessfreq_site', $record, true);

            for ($j = 1; $j <= $i; $j++) {
                $userrecord = new stdClass();
                $userrecord->userid = $j;
                $userrecord->eventid = $eventid;

                $DB->insert_record('local_assessfreq_user', $userrecord, true);
            }

        }

        $assessbymonthstudent = new assess_by_month_student();
        $result = $assessbymonthstudent->get_assess_by_month_student_chart($year);
        $values = $result['chart']->get_series()[0]->get_values();

        foreach ($values as $key => $value) {
            $count = $key + 1;
            $this->assertEquals($count, $value);
        }
    }
}
