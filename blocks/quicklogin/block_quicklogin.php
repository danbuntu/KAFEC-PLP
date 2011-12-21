<?PHP //$Id: block_quicklogin.php,v 1.22 2007/03/22 12:28:10 skodak Exp $

class block_quicklogin extends block_base {
    function init() {
        $this->title = 'Quick '.get_string('login');
        $this->version = 2006102700; //TODO
    }

    function applicable_formats() {
        return array('site' => true);
    }

    function get_content () {
        global $USER, $CFG;
        $wwwroot = '';
        $signup = '';

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($CFG->loginhttps)) {
            $wwwroot = $CFG->wwwroot;
        } else {
            // This actually is not so secure ;-), 'cause we're
            // in unencrypted connection...
            $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
        }
        
        if (!empty($CFG->registerauth)) {
            $authplugin = get_auth_plugin($CFG->registerauth);
            if ($authplugin->can_signup()) {
                $signup = $wwwroot . '/login/signup.php';
            }
        }
        // TODO: now that we have multiauth it is hard to find out if there is a way to change password
        $forgot = $wwwroot . '/login/forgot_password.php';

        $username = get_moodle_cookie() === 'nobody' ? '' : get_moodle_cookie();

        $this->content->footer = '';
        $this->content->text = '';

        // - isguestuser doesn't work in 1.7 - if (!isloggedin() or isguestuser()) {   // Show the block
        if (!isloggedin()) {   // Show the block

            $this->content->text .= "\n".'<form class="loginform" id="login" method="get" action="'.$wwwroot.'/login/index.php">';

            $this->content->text .= '<div class="c1"><center><img src="' . $CFG->wwwroot . '/blocks/quicklogin/padlock1.jpg"><p>You are not logged in</p></center>';

            $this->content->text .= '<div class="c1 btn"><center><input type="submit" value="'.get_string('login').'" /></center></div>';

            $this->content->text .= "</form></br>\n";

            if (!empty($signup)) {
                $this->content->footer .= '<div><a href="'.$signup.'">'.get_string('startsignup').'</a></div>';
            }
            if (!empty($forgot)) {
                $this->content->footer .= '<div><a href="'.$forgot.'">'.get_string('forgotaccount').'</a></div>';
            }
        }

        return $this->content;
    }
}

?>
