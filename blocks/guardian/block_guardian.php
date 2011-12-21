<?php

//block to dispaly a link to the guardians ward plp
//should be set to only display to members of the guardi

class block_guardian extends block_base {

    function init() {
        $this->title = 'Guardian Access';
        $this->version = 2010102600;
    }

    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.

    function instance_allow_multiple() {
        return false;
    }



    function get_content() {
        global $USER, $CFG;
        // Code is run many times, so check if it's run before to save processing
        if ($this->content !== NULL) {
            return $this->content;
        }

        $loggedin = $USER->username;

//strip of the _guardian bit
        $studentname = substr($loggedin, 0, -9);

// get detials for the ward
        $query = "SELECT * FROM mdl_user WHERE username='" . $studentname . "'";

        $result = mysql_query($query);
        $num_rows = mysql_num_rows($result);

        // echo 'num of rows: ' . $num_rows;
// use fullstop before = in this->content->text to concat them



        $this->content = new stdClass;
        $this->content->text = '<table><tr><td>Welcome: ' . $loggedin . ' ' . $id . '</td></tr>';
        //loop throught he results and print the name of each ward with a link to thier plp page
        while ($row = mysql_fetch_assoc($result)) {
            $wardid = $row['id'];
            $firstname = $row['firstname'];
            $surname = $row['lastname'];
            $this->content->text .= '<tr><td>Ward is:<a href="' . $CFG->wwwroot . '/blocks/ilp/view.php?courseid=1&id=' . $wardid . '">' . $firstname . ' ' . $surname . '</a><br/></td></tr>';
        }
        ;

        $this->content->text .= '<tr><td>';
        $this->content->text .= '<img ALIGN=right WIDTH=50% HIEGHT=50% src="' . $CFG->wwwroot . '/blocks/guardian/welcome_icon.png">';
        $this->content->text .= '</td></tr></table>';
        return $this->content;
    }

}

// Here's the closing curly bracket for the class definition
// and here's the closing PHP tag from the section above.
?>