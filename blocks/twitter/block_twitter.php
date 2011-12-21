<?php

// import the twitter class
require_once($CFG->dirroot . '/blocks/twitter/twitter.class.php');

//include ('jscript.php')
//
////include count - forms the basis of the letter count in the message box
include ('count.php');


//set the session state
session_start();

class block_twitter extends block_base {

    //Name the block and set some content
    function init() {
        $this->title   = get_string('title', 'block_twitter');
        $this->version = 2004111200;
    }

    //allows the block title to be set
    function specialization() {
        if(!empty($this->config->title)) {
            $this->title = $this->config->title;
        }else {
            $this->config->title = 'Some title ...';
        }
        if(empty($this->config->text)) {
            $this->config->text = 'Some text ...';
        }
    }


//save settings
    function config_save($data) {
        // Default behavior: save all variables as $CFG properties
        foreach ($data as $name => $value) {
            set_config($name, $value);
        }
        return true;
    }

    //allow global configuration
    function has_config() {
        return true;
    }

//stops multiple blocks being displayed
    function instance_allow_multiple() {
        return false;
    }

    function instance_allow_config() {
        return true;
    }

    function instance_config_save($data) {
        $data = stripslashes_recursive($data);
        $this->config = $data;
        return set_field('block_instance',
                'configdata',
                base64_encode(serialize($data)),
                'id',
                $this->instance->id);
    }

    // check the block has content
    function get_content() {
        global $USER, $COURSE, $CFG;
   // print_object($CFG);
        //set some variables for latter
        $userfirstname = $USER->firstname;
        $coursename = $COURSE->fullname;
        $name = $COURSE->fullname;
        $courseid = $COURSE->id;
        $groups = groups_get_all_groupings($courseid);
        $context = $COURSE->context->id;
        $roo = $this->config->tusername;
        echo '<h2>' . $roo . '</h2>';
        if ($this->content !== NULL) {
            return $this->content;
        }

// Get the twitter username and password from the config_global file and message box length
        $twitterusername = $CFG->twitterusername;
        $twitterpassword = $CFG->twitterpassword;
        $length2 = $CFG->messagelength;

        // set the query string - uses the id number to make sure all users are grabbed
        $query2 = "SELECT u.id, u.firstname, u.lastname, ud.data FROM {$CFG->prefix}role_assignments a Join {$CFG->prefix}user u on a.userid=u.id Join {$CFG->prefix}user_info_data ud on u.id=ud.userid Join {$CFG->prefix}user_info_field fe on ud.fieldid=fe.id  where contextid =  $context and ud.data IS NOT NULL and shortname = 'TwitterUsername'";
        // run the query and feed results to an array
        $names2 = get_records_sql($query2);

//print_r($names);
///print_object($names);
        $this->content->text   =  'Twitter users on this course<br />' ;
       // $this->content->text .= 'Test config: ' . $test . '<br />';
        foreach ($names2 as $names2) {
            $firstname = $names2->firstname;
            $lastname = $names2->lastname;
            $twitterid = $names2->data;
            $this->content->text .= $firstname . ": " . $twitterid . '<br />';
// $serializedArray = serialize($names2);

        }
    
        $this->content->footer = '';
        $this->content->text .=  '<form name="tweet" action="'.$CFG->wwwroot.'/blocks/twitter/sendtweet.php" method="post">';
        //hidden field to send the course context to the form
        $this->content->text .= '<input type="hidden" name="context" value="' . $context. '">';
        $this->content->text .= '<input type="hidden" name="id" value="' . $courseid. '">';
        //pass the twitter username and password through for processing
        $this->content->text .= '<input type="hidden" name="twitterusername" value="' . $twitterusername. '">';
        $this->content->text .= '<input type="hidden" name="twitterpassword" value="' . $twitterpassword. '">';

        // print the form to collect the tweet - limit to 100 characters to allow for username to be appended
        $this->content->text .=  '<font size="1" face="arial, helvetica, sans-serif"> Only 100 characters allowed!';
        $this->content->text .=  ' <input name="text" type="text" size="' . $length2 . '"';
        $this->content->text .= 'onKeyDown="CountLeft(this.form.text,this.form.left,100);" ';
        $this->content->text .= 'onKeyUp="CountLeft(this.form.text,this.form.left,100);">';
        $this->content->text .= '<br />';
        $this->content->text .=  ' <input readonly type="text" name="left" size=3 maxlength=3 value="100">  ';
        $this->content->text .=  'characters left</font>';
        $this->content->text .=  '<input type="submit" value="Tweet it!" name="tweetit" />';
        $this->content->text .= '<form>';


        return $this->content;
    
 }

}   // Here's the closing curly bracket for the class definition
// and here's the closing PHP tag from the section above.
?>
