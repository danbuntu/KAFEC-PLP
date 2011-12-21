<?php
require_once(dirname(__FILE__).'/feed_system.php');

/**
 * Convenient storage for database fields that can be checked and set.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package package_name
 */
class checked_fields {
    
    private $fieldlist;
    private $fields=array();
    private $dirty=array();
    
    /**
     * Constructs with a defined list of field types. The list must be an
     * associative array from field name to a 3-element array:
     * 
     * 'fieldname' => ( '/regexp/', errorcode, 'type' )
     * 
     * If null is permitted, use !NULLOR before the regexp e.g. !NULLOR/[a-c]/.
     * Other possibilities instead of the regexp: !NOTNULL, !BOOLEAN, !NOBLANK.
     *    
     * Error code should be an exception code as defined in exceptions.php.
     *
     * Type must be either 'i' (integer), 's' (string), or 'b' (boolean). These
     * do not play a part in the field-checking at all, but alter how data is
     * converted for the database and whether it's quoted or not.
     * @param array $fieldlist List of fields, as described above 
     */
    public function __construct($fieldlist) {
        $this->fieldlist=$fieldlist;        
    }
    
    /**
     * Sets a batch of fields from database.
     * @param array $array Associative array of field values for some or all
     *   of the fields. May optionally contain other data, which will be ignored
     * @throws Exception If any field doesn't meet restrictions.
     */
    public function set_from_db($array) {
        foreach($this->fieldlist as $fieldname=>$details) {
            if(array_key_exists($fieldname,$array)) {
                $this->set_db($fieldname,$array[$fieldname]);
            }
        }
    }
    
    /**
     * Checks a field value or all fields.
     * @param string $field Field name or null to check all fields (in 
     *   which case $value will be ignored)
     * @param string $value Value to check against field restrictions
     * @param bool $usecurrent True to check current value (with $value=null)
     * @throws Exception If field doesn't meet restrictions.
     */
    private function check($field=null,$value=null,$usecurrent=true) {
        if(!$field) {
            foreach($this->fieldlist as $fieldname=>$details) {
                $this->check($fieldname,null,true);
            }   
            return;
        }
        
        if(!array_key_exists($field,$this->fieldlist)) {
            throw new Exception('Unknown field',EXN_NEWSFEED_UNKNOWNFIELD);
        }
        
        if($usecurrent) {
            $value=$this->get($field);
        }
        
        list($regex,$code,$type) = $this->fieldlist[$field];
        switch($regex) {
            case '!NOTNULL':
                $ok=$value!==null; 
                break;
            case '!BOOLEAN' :
                $ok=($value===true || $value===false);
                break;
            case '!NOBLANK' :
                $ok=($value===null || strlen($value)>1);
                break;
            default:
                if(strpos($regex,'!NULLOR')===0) {
                    $ok=$value===null || preg_match(substr($regex,7),$value);
                } else {
                    $ok=$value!==null && preg_match($regex,$value);
                }                 
                break;
        }        
        if(!$ok) {
            throw new Exception("Invalid value for data field ($field): $value",$code);
        }
    }
    
    /**
     * Sets a field value and marks it as changed (if it has).
     * @param string $field Field name
     * @param string $value New value
     * @throws Exception If field doesn't exist, or new value doesn't meet restrictions
     */
    public function set($field,$value) {
        $this->check($field,$value,false);
        if(array_key_exists($field,$this->fields) && $this->fields[$field]===$value) {
            return;
        }
        $this->dirty[$field]=true;
        $this->fields[$field]=$value;
    }
    
    /**
     * Returns a field value or null if the field has not been set.
     * @param string $field Field name
     * @return mixed Value
     * @throws Exception If field doesn't exist
     */
    public function get($field) {    
        if(!array_key_exists($field,$this->fieldlist)) {
            throw new Exception('Unknown field ['.$field.']',EXN_NEWSFEED_UNKNOWNFIELD);
        }
        if(array_key_exists($field,$this->fields)) {
            return $this->fields[$field];
        }
        return null;
    }

    /** 
     * Returns a string that would be suitable to update all the 'dirty'
     * fields when placed immediately after the SET in an SQL UPDATE statement,
     * e.g. "field1=4,field2='frog'"
     * @return string Update string
     */        
    public function get_update_string() {
        $this->check();
        
        $result='';
        foreach($this->fieldlist as $field=>$details) {
            if(array_key_exists($field,$this->dirty)) {            
                if(!empty($result)) {
                    $result.=',';
                }
                
                $result.=$field.'='.$this->get_sql_value($field);
            }
        }        
        return $result;
    }
    
    /**
     * Obtains the value of a field, quoted/made safe for SQL.
     * @param string $field Field name
     * @return string Value for SQL, including quotes if needed
     */
    private function get_sql_value($field) {
        switch($type=$this->fieldlist[$field][2]) {
            case 's' : 
                return db_q($this->get($field));
            case 'i' :
            case 'b' : 
                return sql_int($this->get($field));
            default:
//                $e=new Exception();
//                print $e->getTraceAsString();
//                exit;
                throw new Exception('Unknown field type: '.$type,EXN_NEWSFEED_UNKNOWNTYPE);
        }
    }
    
    /**
     * Sets the value of a field, based on a value from a database query.
     * @param string $field Field name
     * @param string $value Value from query
     * @throws Exception If value doesn't meet restrictions
     */
    private function set_db($field,$value) {
        switch($this->fieldlist[$field][2]) {
            case 's' : 
            case 'i' :
                break;
            case 'b' : 
                $value = ($value==0 ? false : ($value==1 ? true : ''));
                break;
            default:
                throw new Exception('Unknown field type',EXN_NEWSFEED_UNKNOWNTYPE);
        }
        $this->set($field,$value);
    }
    
    /**
     * Gets two strings suitable for placing in an SQL INSERT statement of the 
     * form INSERT INTO table(names) VALUES(values).
     * @return array Two-element array (names,values)
     */
    public function get_insert_strings() {
        $this->check();
        
        $names='';
        $values='';
        foreach($this->fieldlist as $field=>$details) {
            if(!empty($names)) {
                $names.=',';
                $values.=',';
            }
            $names.=$field;
            $values.=$this->get_sql_value($field);
        }
        return array($names,$values);
    }
    
    public function moodle_insert($table,$extrafields) {
        $this->check();
        
        $obj=new StdClass;
        foreach($this->fieldlist as $field=>$details) {
            $value=$this->get($field);
            if(!is_null($value)) {
                switch($type=$this->fieldlist[$field][2]) {
                    case 's' :
                        $obj->{$field}=addslashes($value);
                        break;
                    case 'i' :
                    case 'b' : 
                        $obj->{$field}=(int)$value;
                        break;
                    default:
                        throw new Exception('Unknown field type: '.$type,EXN_NEWSFEED_UNKNOWNTYPE);                    
                }
            }
        }
        foreach($extrafields as $field=>$value) {
            if(!is_null($value)) {
                $obj->{$field}=addslashes($value);
            }
        }
        if(!($obj->id=insert_record($table,$obj))) {
            throw new Exception('Error inserting row into '.$table);
        }
        return $obj->id;
    }

    /**
     * @return bool True if there are any dirty (unsaved) fields
     */
    public function is_changed() {
        return count($this->dirty) > 0;
    }
    
    /**
     * Marks all fields as saved (clean).
     */
    public function clear_changed() {
        $this->dirty=array();
    }
}

?>