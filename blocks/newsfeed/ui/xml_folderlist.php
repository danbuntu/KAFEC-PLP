<?php
/**
 * Ajax: Returns list of contents of a given folder within newsfeed structure.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
header("Content-Type: application/xml; charset=UTF-8");
header("Cache-Control: no-cache");

require_once('../../../config.php');
require_once('../system/feed_system.php');

$systemcontext = get_context_instance(CONTEXT_SYSTEM);
if(!isloggedin() || !has_capability('block/newsfeed:manage', $systemcontext)) {
    header("HTTP/1.0 403 Forbidden");
    print '<error code="403"/>';
    exit;
}
if(!array_key_exists('folder',$_GET)) {
    header("HTTP/1.0 404 Not Found");
    print '<error code="404"/>';
    exit;
}

try {
    $folder=(int)$_GET['folder'];
    $lf=feed_system::$inst->get_location_folder($folder,null);
    
    // Create output document with <folder> as root element
    $dom=new DOMDocument('1.0','UTF-8');
    $root=$dom->createElement('folder');
    $root->setAttribute('id',$lf->get_id());
    $root->setAttribute('name',$lf->get_name());
    $root->setAttribute('path',$lf->get_path());
    $dom->appendChild($root);
    
    // List items in the folder and add them to the document
    foreach($lf->get_contents() as $thing) {
        $el=$dom->createElement(
            $thing->get_type()==location_folder::TYPE_FOLDER ? 'subfolder' : 'feed');
        $el->setAttribute('id',$thing->get_id());
        $el->setAttribute('name',$thing->get_name());
        $el->setAttribute('haschildren',$thing->has_children()?'yes':'no');
        $root->appendChild($el);
    }
    
    print $dom->saveXML();
} catch(Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    print '<error code="500"><message>'.
        htmlspecialchars($e->getMessage(),ENT_NOQUOTES,'UTF-8').'</message><trace>'.
        htmlspecialchars($e->getTraceAsString(),ENT_NOQUOTES,'UTF-8').
        '</trace></error>';
}
?>