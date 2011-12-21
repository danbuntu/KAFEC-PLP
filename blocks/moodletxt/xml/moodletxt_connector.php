<?php


    /**
     * Class to send and receive data to and from
     * the XML connector system.
     * IMPORTANT: Written for PHP 4.3.0
     *
     * @package xmlconnection
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011040401
     * @since 2006081012
     */
    class moodletxt_connector {

        /**
         * User agent for moodletxt
         * @var string
         */
        var $USER_AGENT = 'Moodletxt 2.4 (2011032901)';

        /**
         * Sets up object for different protocols
         *
         * @version 2010090301
         * @since 2006081012
         */
        function moodletxt_connector() {

            if (array_key_exists('SERVER_SOFTWARE', $_SERVER))
                $this->USER_AGENT .= ' SRV(' . $_SERVER['SERVER_SOFTWARE'] . ')';

            $this->USER_AGENT .= ' PHP(' . phpversion() . ')';

        }

        /**
         * Method to send an XML request to the connector,
         * read back the result, and pass that result back
         *
         * @param string $xmlrequest XML request packets
         * @return string Server response
         * @version 2011032901
         * @since 2006081012
         */
        function send_request($xmlrequests) {

            // Set defaults
            $pathPrefix = "https://";
            $txttoolsHost = 'www.txttools.co.uk';
            $filePath = '/connectors/XML/xml.jsp';
            $fullPath = $filePath;
            $connectionPrefix = 'ssl://';
            $connectionHost = $txttoolsHost;
            $connectionPort = 443;

            if (moodletxt_get_setting('Use_Protocol') != 'SSL') {

                $connectionPort = 80;
                $pathPrefix = "http://";
                $connectionPrefix = '';

            }

            // Get proxy details
            $proxyHost     = moodletxt_get_setting('Proxy_Host');
            $proxyPort     = moodletxt_get_setting('Proxy_Port');
            $proxyUsername = moodletxt_get_setting('Proxy_Username');
            $proxyPassword = moodletxt_get_setting('Proxy_Password');

            // If proxy details are set, override defaults
            if ($proxyHost != '' && $proxyPort != '') {
                $connectionHost = $proxyHost;
                $connectionPort = $proxyPort;
                $fullPath = "http://" . $txttoolsHost . $filePath;
                $connectionPrefix = '';
            }

            $responsearray = array();

            foreach($xmlrequests as $xmlrequest) {

                // Build URL-encoded string from XML request
                $poststring = "XMLPost=" . urlencode($xmlrequest);

                // Build connection string
                $request  = "POST " . $fullPath . " HTTP/1.0\r\n";
                $request .= "Host: " . $txttoolsHost . "\r\n";
                $request .= "User-Agent: " . $this->USER_AGENT . "\r\n";
                if ($proxyHost != '' && $proxyPort != '') {
                    $request .=  $this->get_proxy_headers($proxyHost, $proxyPort, $proxyUsername, $proxyPassword, $fullPath, $filePath);
                }
                $request .= "Content-type: application/x-www-form-urlencoded\r\n";
                $request .= "Content-length: " . strlen($poststring) . "\r\n";
                $request .= "Connection: close\r\n\r\n";
                $request .= $poststring . "\r\n";

                // Open socket
                $fp = @fsockopen($connectionPrefix . $connectionHost, $connectionPort, $errorNo, $errorStr, $timeout = 30);

                if (! $fp) {

                    $errorstring = get_string('errorconnnosocket', 'block_moodletxt');

                    // Error 700 is an internal code to represent socket connection failure
                    $response = '<Response>
    <Error>
        <ErrorMessage>' . $errorstring . '</ErrorMessage>
        <ErrorCode>700</ErrorCode>
    </Error>
</Response>';

                } else {

                    // Send request to server
                    fputs($fp, $request);

                    // Get server response
                    $response = '';

                    while (!feof($fp)) {

                        $response .= @fgets($fp, 128); // Bug in PHP SSL handling causes problems here - suppress

                    }

                    fclose($fp);

                    // Check that XML has been returned
                    $XMLproc = '<?xml';

                    $checkForXML = strpos($response, $XMLproc);

                    if ($checkForXML === false) {

                        // Check for HTTP error codes
                        // Uses only the txttools server, so safe to assume Apache,HTTP 1.1, Linux

                        $responseCode = substr($response, 9, 3);

                        // Check for common error codes
                        switch($responseCode) {

                            case '401':
                            case '403':

                                $errorstring = get_string('errorconn401', 'block_moodletxt') . $responseCode;

                                break;

                            case '404':

                                $errorstring = get_string('errorconn404', 'block_moodletxt');

                                break;

                            case '500':

                                $errorstring = get_string('errorconn500', 'block_moodletxt');

                                break;

                            case '503':

                                $errorstring = get_string('errorconn503', 'block_moodletxt');

                                break;

                            default:

                                $errorstring = get_string('errorconndefault', 'block_moodletxt') . $responseCode;

                        }

                        // Inject error XML into the response
                        // (This can then be parsed and utilised as normal)

                        $response = '<Response>
    <Error>
        <ErrorMessage>' . $errorstring . '</ErrorMessage>
        <ResponseCode>' . $responseCode . '</ResponseCode>
    </Error>
</Response>';

                    } else {

                        $response = substr($response, $checkForXML);

                    } // End of if-else for presence of XML

                }  // End of "is socket open?"

                array_push($responsearray, $response);

            } // End of for-each

            return $responsearray;

        }  // End of method



        /**
         * Function to open an XML feed, get the content,
         * and pass this back to the calling method
         *
         * @version 2011040401
         * @since 2007012612
         */
        function get_feed() {

            // Set defaults
            $pathPrefix = "http://";
            $txttoolsHost = 'www.txttools.co.uk';
            $txttoolsPath = 'www.txttools.co.uk/preloginjsp/moodletxt/rss.xml';
            $connectionHost = $txttoolsHost;
            $connectionPort = 80;

            // Get proxy details
            $proxyHost     = moodletxt_get_setting('Proxy_Host');
            $proxyPort     = moodletxt_get_setting('Proxy_Port');
            $proxyUsername = moodletxt_get_setting('Proxy_Username');
            $proxyPassword = moodletxt_get_setting('Proxy_Password');

            // If proxy details are set, override defaults
            if ($proxyHost != '' && $proxyPort != '') {
                $connectionHost = $proxyHost;
                $connectionPort = $proxyPort;
            }

            $responsearray = array();

            // Build connection string
            $request  = "GET " . $pathPrefix . $txttoolsPath . " HTTP/1.0\r\n";
            $request .= "Host: " . $txttoolsHost . "\r\n";
            $request .= "User-Agent: " . $this->USER_AGENT . "\r\n";
            if ($proxyHost != '' && $proxyPort != '') {
                $request .=  $this->get_proxy_headers($proxyHost, $proxyPort, $proxyUsername, $proxyPassword, $pathPrefix . $txttoolsPath, $txttoolsPath);
            }
            $request .= "Connection: close\r\n\r\n";

            // Open socket
            $fp = @fsockopen($connectionHost, $connectionPort, $errorNo, $errorStr, $timeout = 30);

            if (! $fp) {

                $errorstring = get_string('errorconnnosocket', 'block_moodletxt');

                // Error 700 is an internal code to represent socket connection failure
                $response = '<Response>
    <Error>
        <ErrorMessage>' . $errorstring . '</ErrorMessage>
        <ErrorCode>700</ErrorCode>
    </Error>
</Response>';

            } else {

                // Send request to server
                fputs($fp, $request);

                // Get server response
                $response = '';

                while (!feof($fp)) {

                    $response .= @fgets($fp, 128); // Bug in PHP SSL handling causes problems here - suppress

                }

                fclose($fp);

                $XMLproc = "<?xml";

                $checkForXML = strpos($response, $XMLproc);

                if ($checkForXML === false) {

                    // Check for HTTP error codes
                    // Uses only the txttools server, so safe to assume Apache,HTTP 1.1, Linux

                    $responseCode = substr($response, 9, 3);

                    // Check for common error codes
                    switch($responseCode) {

                        case '401':
                        case '403':

                            $errorstring = get_string('errorconnrss401', 'block_moodletxt') . $responseCode;

                            break;

                        case '404':

                            $errorstring = get_string('errorconnrss404', 'block_moodletxt');

                            break;

                        case '500':

                            $errorstring = get_string('errorconnrss500', 'block_moodletxt');

                            break;

                        case '503':

                            $errorstring = get_string('errorconnrss503', 'block_moodletxt');

                            break;

                        default:

                            $errorstring = get_string('errorconnrssdefault', 'block_moodletxt') . $responseCode;

                    }

                    // Inject error XML into the response
                    // (This can then be parsed and utilised as normal)

                    $response = '<Response>
    <Error>
        <ErrorMessage>' . $errorstring . '</ErrorMessage>
        <ResponseCode>' . $responseCode . '</ResponseCode>
    </Error>
</Response>';

                } else {

                    $response = substr($response, $checkForXML);

                }

            }

            return $response;

        }

        /**
         * Method performs proxy digest authentication
         * @TODO Fix this! Digest authentication *still* doesn't work, despite this algorithm matching the RFC!
         * @link http://uk.php.net/manual/en/function.fopen.php
         * @link http://www.rfc-archive.org/getrfc.php?rfc=2617
         * @param string $host The proxy host being connected to
         * @param string $port The port of the proxy
         * @param string $username The username used to authenticate with the server
         * @param string $password The password used to authenticate with the server
         * @param string $fullPath The full URI of the page being requested
         * @param string $filePath The relational URI of the page being requested
         * @return string
         * @version 2010062212
         * @since 2009092512
         */
        function get_proxy_headers ($host, $port, $username, $password, $fullPath, $filePath) {

            
//            if (! $fp = fsockopen($host, $port, $errno, $errstr, 15))
//                return false;
//
//            // First do the non-authenticated header so that the server
//            // sends back a 401 error containing its nonce and opaque
//            $out  = "GET " . $fullPath . " HTTP/1.1\r\n";
//            $out .= "Host: http://www.txttools.co.uk\r\n";
//            $out .= "Connection: Close\r\n\r\n";
//
//            fwrite($fp, $out);
//
//            $content = '';
//
//            // Read the reply and look for the WWW-Authenticate element
//            while (!feof($fp)) {
//
//                $line = fgets($fp, 512);
//                $content .= $line;
//
//            }
//
//            fclose($fp);
//
//            if (strpos($content, "WWW-Authenticate: Basic") !== false ||
//                strpos($content, "Proxy-Authenticate: Basic") !== false) {
                // Proxy is using basic authentication
                return "Proxy-Authorization: Basic " . base64_encode($username . ":" . $password) . "\r\n";
//            }
//
//            // These are all the vals required from the server
//            $realm  = $this->get_value_from_headers('realm',    $content);
//            $qop    = $this->get_value_from_headers('qop',      $content);
//            $nonce  = $this->get_value_from_headers('nonce',    $content);
//            $opaque = $this->get_value_from_headers('opaque',   $content);
//
//            // Client nonce can be anything since this authentication session is not going to be persistent
//            // likewise for the cookie - just call it anything
//            $cnonce = "686fb978a9c7e";
//            $cookie = "ChocolateChipPlease";
//
//            // Calculate the hashes of A1 and A2 as described in RFC 2617
//            $a1  = $username . ':' . $realm . ':' .$password;
//            $a2  = 'POST:' . $filePath;
//            $ha1 = md5($a1);
//            $ha2 = md5($a2);
//
//
//            if (strpos($qop, "auth-int") !== false) {
//                // auth-int support goes here
//            } else if ($qop == '') {
//                // Legacy connection, as per RFC 2069
//                $response = md5($ha1 . ':' . $nonce . ':' . $ha2);
//            } else {
//                // Calculate the response hash as described in RFC 2617
//                $response = md5($ha1 . ':' . $nonce . ':00000001:' . $cnonce . ':' . $qop . ':' . $ha2);
//            }
//
//           // Put together the Authorization Request Header
//            //$out  = "Cookie: cookie=$cookie\r\n";
//            $out = 'Proxy-Authorization: Digest username="' . $username . '", ' .
//                                          'realm="' . $realm . '", ' .
//                                          'qop="' . $qop . '", ' .
//                                          'algorithm="MD5", ' .
//                                          'uri="' . $filePath . '", ' .
//                                          'nonce="' . $nonce . '", ' .
//                                          'nc="00000001", ' .
//                                          'cnonce="' . $cnonce . '", ' .
//                                          'response="' . $response . '"';
//            $out .= ($opaque != '') ? 'opaque="' . $opaque . "\"\r\n" : "\r\n";
//
//            return $out;
            
        }

        /**
         *
         * @param <type> $key
         * @param <type> $headers
         * @return <type>
         */
        function get_value_from_headers($key, $headers) {

            preg_match('/' . $key . '=".*"/i', $headers, $matches);

            if (count($matches) > 0) {
                $matchsplit = explode('"', $matches[0]);
                return $matchsplit[1];
            }

            return '';

        }

    } // End of class

?>