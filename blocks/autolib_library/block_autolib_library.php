<?PHP

//$Id: block_autolib_library'.php, v1.0 2008/2/1 by Red Morris of Mid-Kent College $

class block_autolib_library extends block_base {

    function init() {
        $this->version = 20070220;
        $this->title = "E-Library";
    }

    function instance_allow_multiple() {
        return false;
    }

    function hide_header() {
        return false;
    }

    // Sets the title
    function specialization() {
        $this->title = "E-Library";
    }

    function get_content() {
        global $USER, $CFG, $SESSION;
        // Code is run many times, so check if it's run before to save processing
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        if (isset($USER->username)) {
            $loginID = $USER->username;
        } else {
            $loginID = '';
        }

        $remoteurl = 'http://larsey.midkent.ac.uk/autolib/vle.dll/vle?userid=' . $loginID;
        $url_headers = @get_headers($remoteurl);
        if ($url_headers[0] == 'HTTP/1.1 404 Not Found' || $url_headers[0] == '') {
            $exists = false;
        } else {
            $exists = true;
        }

        if ($exists) {
            $autolibhtml = implode('', file($remoteurl));
            //$autolibhtml = implode('', file("http://larsey.midkent.ac.uk/autolib/vle.dll/vle?userid=abryan"));

            if (strlen($autolibhtml) > 2) {
                $borrowed = substr($autolibhtml, strpos($autolibhtml, '[Borrowed]') + 10);
                $borrowed = substr($borrowed, 0, strpos($borrowed, ';'));

                $overdues = substr($autolibhtml, strpos($autolibhtml, '[Overdues]') + 10);
                $overdues = substr($overdues, 0, strpos($overdues, ';'));

                $fines = substr($autolibhtml, strpos($autolibhtml, '[Fines]') + 7);
                $fines = '&pound;' . number_format(substr($fines, 0, strpos($fines, ';')), 2);

                $reservations = substr($autolibhtml, strpos($autolibhtml, '[Reservations]') + 14);
                $reservations = substr($reservations, 0, strpos($reservations, ';'));

                /* $summaries = substr($autolibhtml,strpos($autolibhtml, '[Summary]') + 9);
                  $summaries = substr($summaries,0,strpos($summaries, ';'));

                  $sdi = substr($autolibhtml,strpos($autolibhtml, '[SDI]') + 5);
                  $sdi = substr($sdi,0,strpos($sdi, ';')); */

                $this->content->text = '<a href="https://sharepoint.midkent.ac.uk/Sites/library2/default.aspx">
											
											<!-- <center><b><a class="elibrarytext" href=http://online.midkent.ac.uk target="opacwindow">Online Resource Portal</a></b></center> -->
                                                                                        
											<table style="text-align: center;">
                                                                                      											  <tr> 
											    <td width="48%" class="elibrarytext">Borrowed: ' . $borrowed . '</td>
											    <td width="52%" class="elibrarytext">Overdues: ' . $overdues . '</td>
											  </tr>
											  <tr>
											    <td class="elibrarytext">Charges: ' . $fines . '</td>
											    <td class="elibrarytext">Reservations: ' . $reservations . '</td> 
											  </tr>
                                                                                          <tr>
                                                                                          <td><a class="elibrarytext" href="http://larsey.midkent.ac.uk/opac/opacreq.dll/new?id=' . $loginID . '" target="opacwindow"><img src="' . $CFG->wwwroot . '/blocks/autolib_library/magnify.png"><br>Search <br>Library</a></td>
											   
											    <td><a class="elibrarytext" href="https://sharepoint.midkent.ac.uk/sites/library2/onlineportal/default.aspx" target="opacwindow"><img src="' . $CFG->wwwroot . '/blocks/autolib_library/book.png"><br>Online Services</a></td>
                                                                                                        </tr>
											  <tr>
											   <td><a class="elibrarytext" href="http://larsey.midkent.ac.uk/autolib/opacreq.dll/bordetails?id=' . $loginID . '" target="opacwindow"><img src="' . $CFG->wwwroot . '/blocks/autolib_library/user.png"><br>Your Library<br>Account</a></td>
											    <td><a class="elibrarytext" href="https://sharepoint.midkent.ac.uk/Sites/library2/default.aspx" target="opacwindow"><img src="' . $CFG->wwwroot . '/blocks/autolib_library/screen.png"><br>Learning<br>Resource Centre</a></td>
                                                                                                        </tr>
											</table>';
            } else {
                if (!isloggedin() or isguest()) {
                    // User isn't logged in, or is logged in as a guest
                    // With no content the block will be hidden. Alternatively enter a helpful message
                    $this->content->text = '';
                } else {
                    // The userid didn't return any records, probably because they don't have an account
                    $this->content->text = '<a href="https://sharepoint.midkent.ac.uk/Sites/library2/default.aspx">
												<br>
												<center><b><a class="elibrarytext" href=http://online.midkent.ac.uk target="opacwindow">Online Resource Portal</a></b></center>
												<center><table>
												  <tr>
												    <td class="elibrarytext">There is no library account for ' . $loginID . ' or it is not configured.<br />
												    <br />
												    Please see a member of the library staff.</td>
											      </tr>
												</table>';
                }
            }
        } else {
            $this->content->text = '';
        }

        $this->content->footer = '';

        return $this->content;
    }

}

?>