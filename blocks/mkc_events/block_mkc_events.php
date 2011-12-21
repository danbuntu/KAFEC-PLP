<?php

class block_mkc_events extends block_base {

    function init() {
        $this->title = get_string('mkc_events', 'block_mkc_events');
        $this->version = 2004111200;
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $CFG, $USER, $SITE, $COURSE;
        if ($this->content !== NULL) {
            return $this->content;
        }

        $query = "SELECT * FROM events WHERE enddate>= curdate() ORDER BY enddate ASC LIMIT 4";

        $result = mysql_query($query);
        $num_rows = mysql_num_rows($result);

        $i = 1;

        while ($row = mysql_fetch_assoc($result)) {
            ${'name' . $i} = $row['name'];
            ${'details' . $i} = $row['details'];
            ${'religion' . $i} = $row['religion'];
            ${'start' . $i} = $row['startdate'];
            ${'end' . $i} = $row['enddate'];
            $i++;
        }

//get the course context id - probably not needed
        $courseid = $COURSE->id;
        $context = $COURSE->context->id;


// get the proper context
        $context2 = get_context_instance(CONTEXT_COURSE, $COURSE->id);

//used for bebugging
//  if (has_capability('block/ilp_student_info:viewclass', $context2)) {
//      $allow = 1;
//  } else {
//      $allow = 0;
//  }
//
//only display the block if the users is a teacher - ie can update the course

        $url = $CFG->wwwroot . '/blocks/group_targets/view.php?courseid=' . $courseid . '&var1=' . $context . '';

        $this->content = new stdClass;

        $this->content->text .= '<table width=100%><tr><th colspan="2" style="text-align: center;"><big>' . $name1 . '</big></th></tr>';
        $this->content->text .= '<th colspan="2" style="text-align: center;">' . $religion1 . '</th></tr>';
        $this->content->text .= '<tr><td colspan="2" style="text-align: center;">' . date("d-M-Y", strtotime($start1)) . ' - ' . date("d-M-Y", strtotime($end1)) . '</td></tr>';
        $this->content->text .= '<tr><td style="text-align: center;" colspan="2">' . $details1 . '</td></tr>';

        $this->content->text .= '<tr><th style="text-align: center;"><small>' . $name2 . '</small></th><th><small>' . $religion2 . '<small></tr>';
        $this->content->text .= '<tr><td colspan="2" style="text-align: center;"><small>' . date("d-M-Y", strtotime($start2)) . ' - ' . date("d-M-Y", strtotime($end2)) . '</small></td></tr>';
        $this->content->text .= '<tr><th style="text-align: center;"><small>' . $name3 . '</th><th><small>' . $religion3 . '</small></tr>';
        $this->content->text .= '<tr><td colspan="2" style="text-align: center;"><small>' . date("d-M-Y", strtotime($start3)) . ' - ' . date("d-M-Y", strtotime($end3)) . '</small></td></tr>';
        $this->content->text .= '<tr><th style="text-align: center;"><small>' . $name4 . '</th><th><small>' . $religion4 . '</small></tr>';
        $this->content->text .= '<tr><td colspan="2" style="text-align: center;"><small>' . date("d-M-Y", strtotime($start4)) . ' - ' . date("d-M-Y", strtotime($end4)) . '</small></td></tr>';
        $this->content->text .= '</table>';
        if (has_capability('moodle/course:update', $context2)) {
            $this->content->text .= '<a href="' . $CFG->wwwroot . '/blocks/mkc_events/index.php"><img src="' . $CFG->wwwroot . '/blocks/mkc_events/images/edit-icon.png" width="36" height="36" align=right alt="Edit Religious Events"/></a></div>';
        }
        return $this->content;
    }

}

?>