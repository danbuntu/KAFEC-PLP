<?php
require_once(dirname(__FILE__).'/exceptions.php');

/**
 * Wraps ADODB transactions. 
 * 
 * To use, simply construct, then call commit() or rollback() 
 * when you are done. If you forget to do that you'll get errors. Also, this cannot be
 * combined with other transaction methods (i,e, using ADODB directly); it will give errors
 * if you do that too. (This is to warn you if there is a situation where you might not have
 * closed all the transactions.)
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moduleapi
 */
class transaction_wrapper {
    
    private $done,$trace,$db;
    
    private static $nesting=0;
    
    function __construct(&$localdb=false) {
        global $CFG;
        if($localdb) {
            $this->db =& $localdb;
        } else {
            global $db;
            $this->db=$db;
        }
        
        if(self::$nesting==0 && $this->db->transCnt!=0 && $CFG->debug>7) {
            throw new Exception('Already within transaction?',EXN_LOCAL_TRANSACTIONENTER);
        }
                
        $this->db->StartTrans();
        if($CFG->debug>7) {
            $e=new Exception('backtrace');
            $this->trace=$e->getTraceAsString();
        }
        self::$nesting++;
    }
    
    function complete($ok=true) {
        if($this->done) {
            throw new Exception('Cannot complete transaction twice');
        }
        global $CFG;
        $val=$this->db->CompleteTrans($ok);
        $this->done=true;
        self::$nesting--;
        
        if(self::$nesting==0) {
            if($this->db->transCnt!=0 && $CFG->debug>7) {
                throw new Exception('Still within transaction?',EXN_LOCAL_TRANSACTIONLEAVE);
            }            
        }
        
        return $val;
    }
    
    function commit() {
        if($this->done) {
            throw new Exception('Cannot commit transaction twice');
        }
        return $this->complete();
    }
    
    function rollback() {
        if($this->done) {
            throw new Exception('Cannot rollback transaction twice');
        }
        $this->complete(false);
    }
    
    static function is_in_transaction() {
        return self::$nesting;
    }
    
    function __destruct() {
        if($this->done) {
            return;
        }        
        if($this->trace) {
            print "<pre><strong>transaction_wrapper</strong> not closed:\n".
                htmlspecialchars($this->trace).'</pre>';
        }
        $this->complete(false);
    }
}

?>