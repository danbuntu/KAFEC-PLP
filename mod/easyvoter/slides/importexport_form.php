<?php
/////////////////////////////////////////////////////////////////////////////
//AUTHOR: Solihull College SRDU
//SCRIPT: slides/importexport_form.php
//VERSION: 2010060700
//MODULE: easyvoter
//NOTES: Form for importing or exporting of slides
////////////////////////////////////////////////////////////////////////////
//BASIC SECURITY TO STOP PAGE BEING DISPLAYED IF $sPageCaller VARIABLE NOT FOUND
if(!isset($sPageCaller)||$sPageCaller!=='editslides.php'){
	require_once("../../../config.php");
	redirect($CFG->wwwroot);
}
////////////////////////////////////////////////////////////////////////////

require_once($CFG->libdir.'/formslib.php');

class importexport_form extends moodleform {

    function definition() {
        global $USER, $CFG;
		
		$mform =& $this->_form;
		$mform->addElement('header', 'importexport', get_string('easyvoterimportexport', 'easyvoter'));
		
	//FILE BROWSER
		$mform->addElement('file', 'attachment', get_string('easyvoterimportslides', 'easyvoter'));
		$mform->setType('attachment', PARAM_FILE);
  		$mform->addRule('attachment', null, 'required');

	//HIDDEN FIELDS
		$id = optional_param('id', 0, PARAM_INT); // Course Module ID
		$mform->addElement('hidden', 'id', $id);
		$mform->addElement('hidden', 'action', 'importexport');
	
	//ADD BUTTONS
		$buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('easyvoteruploadcsv', 'easyvoter'));
        $buttonarray[] = &$mform->createElement('button', 'downloadcsv', get_string('easyvoterdownloadcsv', 'easyvoter'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
	}

//PERFORM EXTRA VALIDATION -- IF CORRECT ALSO WRITE TO DB
    function validation($data, $files){
        $errors= array();
		$iError = 0;
		
		//CHECK INSTANCE
		if(!empty($data['id'])){
			$instance=get_field('course_modules','instance','id',$data['id']);
		}
		
		if(!empty($data['id'])&&$instance!==false&&!empty($files['attachment'])){
           if($oFile=@fopen($files['attachment'], 'rb')){
				//CHECK NUMBER OF FIELDS AND WRITE TO OBJECT
				$iLoop=0;
				$aDataArray = array();
		   		while(($fields=fgetcsv($oFile))!==false){
					if(count($fields)==5){
						$oDataObject = new object;
						$oDataObject->instance = $instance;
						$oDataObject->name = $fields[0];
						$oDataObject->content = $fields[1];
						$oDataObject->type = $fields[2];
						$oDataObject->answer = $fields[3];
						$oDataObject->control = $fields[4];
						$oDataObject->timecreated = time();
						$aDataArray[$iLoop] = $oDataObject;
						$iLoop++;
					}else{
						$iError++;
						break;
					}
				}
				fclose($oFile);	
			}else{
				$iError++;
			}
			//VALIDATE OBJECT ENTRIES
			if($iError<1&&count($aDataArray)>1){
				$iLoop=0;
				$aSlideTypes = easyvoter_slideTypes();
				foreach($aDataArray as $oEntry){
					//DONT VALIDATE FIRST COLUMN HEADERS ENTRY
					if($iLoop>0){
						if(!empty($oEntry->name)&&!empty($oEntry->content)&&!empty($oEntry->type)){
							//CHECK TYPE AND ENTRIES
							if(is_string($oEntry->name)&&is_string($oEntry->content)&&array_key_exists($oEntry->type,$aSlideTypes)){
								if(!easyvoter_slideTypes($oEntry->type,$oEntry->answer,$oEntry->control)){
									$iError++;
									break;
								}
							}else{
								$iError++;
								break;
							}
						}else{
							$iError++;
							break;
						}
					}else{
						$iLoop++;
					}
				}
					
				//WRITE TO DB
				if($iError<1){
					$iCurrentSlides = count_records('easyvoter_slides','instance',$instance);
					$iLoop=0;
					foreach($aDataArray as $oEntry){
						if($iLoop>0){
							$oEntry->numeral = $iCurrentSlides+$iLoop;
							insert_record('easyvoter_slides', $oEntry);
						}
						$iLoop++;
					}
				}
						
			}else{
				$iError++;
			}
				

		}else{
			$iError++;
		}
		
		
        if($iError>0){
			$errors['attachment'] = get_string('easyvoterincorrectfile', 'easyvoter');
            return $errors;
        }else{
			return true;
        }
    }
	
}
?>