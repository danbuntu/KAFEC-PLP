<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$message = 'from moodledev - with proxy';
//echo $message;
$number = '+447799625520';

$xml = "<?xml version='1.0' ?>
<Request>
<Authentication>
<Username><![CDATA[MidKC_Admin]]></Username>
<Password><![CDATA[axwn2gwc]]></Password>
</Authentication>
<Message>
<MessageText><![CDATA[". $message . "]]></MessageText>
<Phone><![CDATA[" . urlencode($number) . "]]></Phone>
<Type>1</Type>
<MessageDate>1234567890</MessageDate>
<UniqueID><![CDATA[Just an ID]]></UniqueID>
<From><![CDATA[Dattwood]]></From>
</Message>
</Request>";

header("Content-type: text/xml");
echo $xml;
$url = 'http://www.txttools.co.uk/connectors/XML/xml.jsp';

// echo '<form ACTION="http://www.txttools.co.uk/connectors/XML/xml.jsp" METHOD="POST" name="form1">';

// echo '<input type="hidden" name="XMLPOST" value="' . $xml . '">';
// echo '<input type="submit" value="test">';
// echo '</form>';



// $header  = "POST HTTP/1.0 \r\n";
// $header .= "Content-type: text/xml \r\n";
// $header .= "Content-length: ".strlen($xml)." \r\n";
// $header .= "Content-transfer-encoding: text \r\n";
// $header .= "Connection: close \r\n\r\n";
// $header .= $xml;


$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 4);
//curl_setopt($ch,CURLOPT_PORT,80);
curl_setopt($ch, CURLOPT_PROXY, "http://10.0.100.61:8080");
curl_setopt($ch, CURLOPT_PROXYUSERPWD, "MIDKENT\proxys:pr0xyS");


curl_setopt($ch, CURLOPT_POSTFIELDS, "XMLPOST=$xml");


$data = curl_exec($ch);

if(curl_errno($ch))
    print curl_error($ch);
else
    curl_close($ch);

	var_dump($data);






// do_post_request($url, $xml);

// function do_post_request($url, $xml2, $optional_headers = null)
// {
// echo $url;
// echo $xml2;

  // $params = array('http' => array(
              // 'method' => 'POST',
              // 'content' => $xml2
            // ));
  // if ($optional_headers !== null) {
    // $params['http']['header'] = $optional_headers;
  // }
  // $ctx = stream_context_create($params);
  // $fp = @fopen($url, 'rb', false, $ctx);
  // if (!$fp) {
    // throw new Exception("Problem with $url, $php_errormsg");
  // }
  // $response = @stream_get_contents($fp);
  // if ($response === false) {
    // throw new Exception("Problem reading data from $url, $php_errormsg");
  // }
  // return $response;
// }



?>
