<?php
/**
 * Output of public Atom feeds for news readers/aggregators.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */

global $DISABLESAMS;
$DISABLESAMS=true;

require_once('../../config.php');
require_once('system/feed_system.php');

$feed=required_param('feed',PARAM_INT);
$auth=optional_param('auth','',PARAM_RAW);
$gotauthids=explode(',',$auth);

try {
    // This throws exception if the feed isn't marked public
    $data=feed_system::$inst->get_feed_data($feed,true);
    
    // Put in the XSL stylesheet
    $data=str_replace('encoding="UTF-8"?>',
        'encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'.$CFG->wwwroot.'/blocks/newsfeed/atom.xsl"?>',
        $data);
    
    $doc=DOMDocument::loadXML($data);
    $doc->documentElement->removeAttributeNS(news_feed::NS_OU,'public');
    for($child=$doc->documentElement->firstChild;$child;$child=$child->nextSibling) {
        // Change self link to include authids
        if($auth && $child->nodeName=='link' && $child->getAttribute('rel')=='self') {
            $child->setAttribute('href',$child->getAttribute('href').'&auth='.$auth);
        }
        if($child->nodeName=='entry') {
            $authid='';
            for($grandchild=$child->firstChild;$grandchild;$grandchild=$grandchild->nextSibling) {
                if($grandchild->localName=='authid' && $grandchild->namespaceURI==news_feed::NS_OU) {
                    $authid=$grandchild->firstChild->nodeValue;
                    break;
                }
            }
            if($authid) {
                $needauthids=explode(' ',$authid);
                $ok=false;
                foreach($needauthids as $needauthid) {
                    if(in_array($needauthid,$gotauthids)) {
                        $ok=true;
                        break;
                    }
                }
                if(!$ok) {
                    $deadchild=$child;
                    $child=$child->previousSibling; // Go back so that the nextSibling call works
                    $deadchild->parentNode->removeChild($deadchild);
                } else {
                    $grandchild->parentNode->removeChild($grandchild);  
                }
            }
        } else if($child->nodeName=='error') {
            // Just remove 'ou:error' nodes.
            $deadchild=$child;
            $child=$child->previousSibling; // Go back so that the nextSibling call works
            $deadchild->parentNode->removeChild($deadchild);
        }
    }
    // This is a bit sketchy way to remove redundant namespace declaration - 
    // shouldn't work, but actually does. 
    $doc->documentElement->removeAttributeNS(news_feed::NS_OU,'ou');
    
    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $oldbrowser=
      (!preg_match('/Opera/',$useragent) && preg_match('/MSIE [456]/',$useragent)) || 
      preg_match('/Firefox\/1\./',$useragent);
    
    if($oldbrowser) {
        header('Content-Type: text/xml; charset=UTF-8');
    } else {
        header('Content-Type: application/atom+xml; charset=UTF-8');
    }
    print $doc->saveXML();    
} catch(Exception $e) {
    error_exception($e);
}
?>