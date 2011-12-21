<?php
global $CFG;
require_once($CFG->libdir.'/formslib.php');
/**
 * Moodle form for newsfeed
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class newsfeed_editfeed_form extends moodleform {

    function __construct($nf) {
        $this->nf=&$nf;
        parent::__construct();
    }

    function definition() {
    	$mform=&$this->_form;

        $mform->addElement('hidden','newsfeedid');
        $mform->addElement('hidden','returntofeed');
        $mform->addElement('hidden','courseid');

        $mform->addElement('text', 'nfname', get_string('field_name', 'block_newsfeed'),array('size'=>'50'));
        $mform->addRule('nfname',null,'required',null,'client');
        $mform->setType('nfname', PARAM_RAW);

        $mform->addElement('htmleditor', 'summary', get_string('field_summary', 'block_newsfeed'));
        $mform->setType('summary', PARAM_RAW);

        if($this->nf) {
            $mform->addElement('hidden','type');
        } else {
            $mform->addElement('select', 'type', get_string('field_type', 'block_newsfeed'),
                array(
                    'internal'=>get_string('type_internal','block_newsfeed'),
                    'external'=>get_string('type_external','block_newsfeed')));
        }

        if(!$this->nf || is_a($this->nf,'external_news_feed')) {
            $mform->addElement('header', 'externalheader', get_string('header_external', 'block_newsfeed'));

            $mform->addElement('text', 'url', get_string('field_url', 'block_newsfeed'),array('size'=>'50'));

            $mform->addElement('select','checkfreq',get_string('field_checkfreq','block_newsfeed'),
                array(
                    86400=>get_string('checkfreq_day','block_newsfeed'),
                    0=>get_string('checkfreq_manual','block_newsfeed')));
            $mform->setDefault('checkfreq',86400);
        }

        if(!$this->nf || is_a($this->nf,'internal_news_feed')) {
            $mform->addElement('header', 'internalheader', get_string('header_internal', 'block_newsfeed'));

            $mform->addElement('select','publicfeed',get_string('field_public','block_newsfeed'),
                array(
                    'public'=>get_string('access_public','block_newsfeed'),
                    'private'=>get_string('access_private','block_newsfeed')));

            if(class_exists('ouflags')) {
                $mform->addElement('text', 'defaultauthid', get_string('field_defaultauthid', 'block_newsfeed'));
                $mform->addRule('defaultauthid',
                    get_string('error_authid','block_newsfeed'),'regex','/^([A-Z0-9]+)?$/','client');
    
                $repeatarray=array();
                $repeatarray[] = &MoodleQuickForm::createElement('text', 'optionalauthid', get_string('field_optionalauthid','block_newsfeed'));
    
                if($this->nf && is_a($this->nf,'internal_news_feed')) {
                    $repeatno=count($this->nf->get_optional_authids())+2;
                } else {
                    $repeatno = 3;
                }
    
                $repeateloptions = array();
                if(!$this->nf) {
                    $repeateloptions['optionalauthid']['disabledif'] = array('type', 'ne', 'internal');
                }
                $repeateloptions['optionalauthid']['rule'] = array(
                    get_string('error_authid','block_newsfeed'),'regex','/^([A-Z0-9]+)?$/','client');
                $mform->setType('optionalauthid',PARAM_TEXT);
    
                $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'optionalauthid_repeats', 'optionalauthid_add_fields', 3, null, true);
            }
        }

// already set up somehow        $mform->addElement('hidden','sesskey');
        $mform->addElement('hidden','instanceid');
        $mform->addElement('hidden','blockaction');
        $mform->addElement('hidden','id');

        $mform->addElement('header', null, get_string('blockgeneral', 'block_newsfeed'));

        $mform->addElement('text', 'showcount', get_string('field_showcount', 'block_newsfeed'));
        $mform->addRule('showcount',
            get_string('error_authid','block_newsfeed'),'regex','/^([0-9]+)?$/','client');

        $choices = array();
        $choices['0'] = get_string('no');
        $choices['1'] = get_string('yes');
        $mform->addElement('select','showsummaries',get_string('field_showsummaries','block_newsfeed'), $choices);
        $mform->setDefault('showsummaries', 1);

        // Only allow admins to move block (and only if there is a newsfeed)
        if($this->nf &&
            has_capability('moodle/site:manageblocks', get_context_instance(CONTEXT_SYSTEM, SITEID))) {
            $mform->addElement('header', 'movenewsfeedblock', get_string('move_newsfeed_block', 'block_newsfeed'));

            $mform->addElement('text', 'courseshortname', get_string('course_shortname', 'block_newsfeed'),array('size'=>'16'));

            $mform->addElement('submit', 'submitmove', get_string('move_block', 'block_newsfeed'));
        }

        if(!$this->nf) {
            $mform->disabledIf('url', 'type', 'ne', 'external');
            $mform->disabledIf('checkfreq', 'type', 'ne', 'external');
            $mform->disabledIf('publicfeed', 'type', 'ne', 'internal');
            $mform->disabledIf('defaultauthid', 'type', 'ne', 'internal');
        }
        $this->add_action_buttons();
    }
}
?>