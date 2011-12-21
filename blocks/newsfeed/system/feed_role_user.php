<?php
/**
 * Holds information about a user on a particular role on a feed.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class feed_role_user {
    private $userid,$rolename,$username,$firstname,$lastname,$email;
    
    /**
     * Construct from database fields.
     * @param array $fields Associative array of field values.
     */
    function __construct($fields) {
        if (is_object($fields)) {
            $this->userid=$fields->userid;
            $this->rolename=$fields->rolename;
            $this->username=$fields->username;
            $this->firstname=$fields->firstname;
            $this->lastname=$fields->lastname;
            $this->email=$fields->email;
        } else {
            $this->userid=$fields['userid'];
            $this->rolename=$fields['rolename'];
            $this->username=$fields['username'];
            $this->firstname=$fields['firstname'];
            $this->lastname=$fields['lastname'];
            $this->email=$fields['email'];
        }
    }
    
    /** @return int Moodle user id */
    public function get_user_id() {
        return $this->userid; 
    }
    /** @return string Role name (internal_news_feed::ROLE_xx) */ 
    public function get_role_name() {
        return $this->rolename;
    }
    /** @return string User name (from user table) */
    public function get_user_name() {
        return $this->username;
    }
    /** @return string First name (from user table) */
    public function get_first_name() {
        return $this->firstname;
    }
    /** @return string Last name (from user table) */
    public function get_last_name() {
        return $this->lastname;
    }
    /** @return string Email (from user table) */
    public function get_email() {
        return $this->email;
    }
    
    /** @return string Real name followed by username in brackets, for use in emails */
    public function get_display_version() {
        return $this->firstname.' '.$this->lastname.
            ' ('.$this->username.')';
    }    
}
?>