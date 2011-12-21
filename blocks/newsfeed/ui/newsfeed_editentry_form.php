<?php
global $CFG;
require_once($CFG->libdir.'/formslib.php');
/**
 * Moodle form for creating or editing a newsfeed post.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class newsfeed_editentry_form extends moodleform {

    function __construct($nf,$version) {
        $this->nf=&$nf;
        $this->version=&$version;
        parent::__construct();
    }

    function getMaxFileSize() {
        global $CFG;
        return $CFG->maxbytes;
    }

    function definition() {

        $mform=&$this->_form;

        $mform->addElement('hidden','viewnewsfeedid');
        $mform->addElement('hidden','versionid');
        $mform->addElement('hidden','courseid');

        if(!$this->version) {
            $options=array();
            $feeds=feed_system::$inst->get_feeds($this->nf->get_all_descendant_ids());
            $feedinfo='';
            foreach($feeds as $feed) {
                // Only include feeds you can post to
                $nfcontext = get_context_instance(CONTEXT_BLOCK, $feed->get_blockinstance());
                if(!has_capability('block/newsfeed:manage', $nfcontext) &&
                   !has_capability('block/newsfeed:post', $nfcontext)) {
                    continue;
                }

                $options[$feed->get_id()]=$feed->get_full_name();

                $feedinfo.='<div '.($feed->get_id()==$this->nf->get_id() ? '' : 'style="display:none" ').
                    'class="newsfeed_feedinfo" id="feedinfo'.$feed->get_id().'">';
                $feedinfo.=$feed->get_summary();

                $ancestors=$feed->get_all_ancestor_ids();
                if(count($ancestors) > 10) {
                    $appearsin="Messages posted to this feed also appear in ".(count($ancestors)-1)." other feeds";
                } else if(count($ancestors)>1) {
                    $appearsin="Messages posted to this feed also appear in:<ul class='nf_appearsin'>";
                    foreach($ancestors as $ancestor) {
                        if($ancestor==$feed->get_id()) {
                            continue;
                        }
                        $appearsin.=
                            "<li>".
                            htmlspecialchars(feed_system::$inst->get_feed($ancestor)->get_full_name()).
                            "</li>";
                    }
                    $appearsin.="</ul>";
                } else {
                    $appearsin=false;
                }
                if($appearsin) {
                    $feedinfo.=$appearsin;
                }
                $feedinfo.='</div>';
            }
$feedinfo.='
<script type="text/javascript">
var nf_select=document.getElementById("id_newsfeedid");
var oldchange=nf_select.onchange;
nf_select.onchange=function() {
    if(oldchange) {
        oldchange();
    }
    var divs=document.getElementsByTagName("div");
    for(var i=0;i!=divs.length;i++) {
        if(divs[i].className!="newsfeed_feedinfo") continue;
        var newsfeedid=divs[i].id.substr(8);
        divs[i].style.display=nf_select.value==newsfeedid ? "block" : "none";
    }
    var fieldsets=document.getElementsByTagName("fieldset");
    for(var i=0;i!=fieldsets.length;i++) {
        if(fieldsets[i].id.substr(0,12)!="authidheader") continue;
        var newsfeedid=fieldsets[i].id.substr(12);
        fieldsets[i].style.display=nf_select.value==newsfeedid ? "block" : "none";
    }
}
nf_select.onchange();
</script>
';

            $mform->addElement('select', 'newsfeedid', get_string('field_newsfeedid', 'block_newsfeed'),$options);
            $mform->addElement('static', 'feedinfo','',$feedinfo);

        } else {
            $mform->addElement('hidden','newsfeedid');
            $feeds=array($this->nf);
        }

        if(class_exists('ouflags')) {
            $arr_rollforward = array();
            if (!$this->rollforward_block_exists($this->nf->get_blockinstance())) {
                $arr_rollforward = array(''=>get_string('rollforward_select','block_newsfeed'),
                                          1=>get_string('rollforward_relative','block_newsfeed'),
                                          0=>get_string('rollforward_absolute','block_newsfeed'));
            } else {
                $arr_rollforward = array(''=>get_string('rollforward_select','block_newsfeed'),
                                          0=>get_string('rollforward_absolute','block_newsfeed'));
            }
            $mform->addElement('select', 'rollforward', get_string('field_rollforward', 'block_newsfeed'),
                               $arr_rollforward);
            $mform->addRule('rollforward',get_string('rollforward_required','block_newsfeed'),'required',null,'client');
            $mform->addRule('rollforward',null,'numeric',null,'client');
        } else {
            $mform->addElement('hidden','rollforward');
            $mform->setDefault('rollforward',0);
        }

        $mform->addElement('date_selector', 'appearancedate', get_string('field_appearancedate','block_newsfeed'));

        $relativeinfo="
<div id='relativedate'></div>
<script type='text/javascript'>
// Set up listener on rollforward type
var rf_select=document.getElementById('id_rollforward');
var rf_oldchange=rf_select.onchange;
rf_select.onchange=function() {
    if(rf_oldchange) {
        rf_oldchange();
    }
    document.getElementById('relativedate').style.display=rf_select.value==1 ? 'block' : 'none';
}
rf_select.onchange();

// Let's play hunt the selects
var selects=document.getElementsByTagName('select');
var day,month,year;
for(var i=0;i<selects.length;i++) {
    var expected=1;
    var found=true;
    for(var option=selects[i].firstChild;option;option=option.nextSibling) {
        if(option.nodeName.toLowerCase()=='option') {
            if(option.value!=expected) {
                found=false;
                break;
            }
            expected++;
        }
    }
    if(found && expected==32) {
        day=selects[i];
        month=selects[i+1];
        year=selects[i+2];
        break;
    }
}

var startdate=new Date();
startdate.setUTCHours(0,0,0,0);
startdate.setUTCFullYear(".date('Y,n-1,d',$this->nf->get_start_date()).");

function update_date() {
    var setdate=new Date();
    setdate.setUTCFullYear(year.value,month.value-1,day.value);
    setdate.setUTCHours(0,0,0,0);

    var days=Math.round((setdate-startdate)/(24*60*60*1000));
    var text='".get_string('relativedate','block_newsfeed')."'.replace(/_/,days);
    var output=document.getElementById('relativedate');
    while(output.firstChild) output.removeChild(output.firstChild);
    output.appendChild(document.createTextNode(text));
}

day.onchange=update_date;
month.onchange=update_date;
year.onchange=update_date;
update_date();
</script>";
        $mform->addElement('static', 'relativeinfo','',$relativeinfo);

        $mform->addElement('text', 'title', get_string('field_title', 'block_newsfeed'),array('size'=>'50'));
        $mform->addRule('title',null,'required',null,'client');
        $mform->setType('title', PARAM_RAW);

        $mform->addElement('htmleditor', 'text', get_string('field_text', 'block_newsfeed'));
        $mform->setType('text', PARAM_RAW);

        // Get message attachments if any
        if($this->version) {
            $index=0;
            foreach($this->version->get_attachments() as $attachment) {
                $mform->addElement('checkbox', 'deleteattachment'.$index,
                    $attachment->get_filename(),
                    get_string('delete'));
                $mform->addElement('hidden','attachment'.$index);
                $mform->setDefault('attachment'.$index,$attachment->get_filename());
                $index++;
            }
        }

        // Make boxes to add new attachments. Note that the repeat feature doesn't work for
        // attachments.
        $mform->addElement('file', 'addattachment1', get_string('field_attachment','block_newsfeed'));
        $mform->addElement('file', 'addattachment2', get_string('field_attachment','block_newsfeed'));
        $mform->addElement('file', 'addattachment3', get_string('field_attachment','block_newsfeed'));

        // Authid checkboxes
        foreach($feeds as $feed) {
            // Don't show for feeds that have no authids anyhow
            $default=$feed->get_default_authid();
            $optional=$feed->get_optional_authids();
            if(!$default && count($optional)==0) {
                continue;
            }

            $mform->addElement('header', 'authidheader'.$feed->get_id(),
                get_string('header_authids', 'block_newsfeed'));
            $mform->addElement('static','authidstatic'.$feed->get_id(),'',
                get_string('explanation_authids', 'block_newsfeed').
                (($feed->get_id()!=$this->nf->get_id())
? '
<script type="text/javascript">
document.getElementById("authidheader'.$feed->get_id().'").style.display="none";
</script>
' : ''));

            if($default) {
                $mform->addElement('checkbox', $name=('authid'.$feed->get_id().'_'.$default),
                    '',$default);
                $mform->setDefault($name,1);
            }
            foreach($optional as $authid) {
                $mform->addElement('checkbox', $name=('authid'.$feed->get_id().'_'.$authid),
                    '',$authid);
            }
        }

        $this->add_action_buttons();

    }

    /**
     * Check if roll forward block exists for newsfeed block instance course
     *
     * @param int $biid newsfeed block instance id
     * @return true if rollforward block found, false if not found or an error
     */
    private function rollforward_block_exists($biid) {
        global $CFG;

        return get_field_sql("
SELECT bi.id
FROM {$CFG->prefix}block_instance bi
WHERE blockid = (SELECT id FROM {$CFG->prefix}block WHERE name = 'rfmarker')
AND bi.pageid = (SELECT pageid FROM {$CFG->prefix}block_instance WHERE id = {$biid})
");
    }    

}
?>