<?php
require_once(dirname(__FILE__).'/../../../lib/filelib.php');

/**
 * Data stored about an attachment to newsfeed message.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 */
class news_attachment {
    
    const 
        FILELETTER='A-Za-z0-9_\-.!\',',
        MIMETYPE='/^[a-z0-9.-]+(\/[a-z0-9.-]+)*$/',
        SIZE='/^[0-9]+$/';
        
    private $filename,$mimetype,$size;   
    private $fromnewsfeed,$sourcepath=null,$desiredname;
    
    /**
     * Creates an attachment with the given details and checks
     * they are valid.
     * @param string $filename Filename (may only include certain characters)
     * @param string $mimetype MIME type of format text/plain
     * @param int $size Size of file
     * @param int $fromnewsfeed Newsfeed ID from which this file came, or null if not in any
     * @throws Exception If any parameters are invalid
     */
    function __construct($filename,$mimetype,$size,$fromnewsfeed=null) {
        if(!is_null($filename) && 
            !preg_match('/^['.self::FILELETTER.']+$/',$filename)) {
            throw new Exception("Invalid filename: $filename",
                EXN_NEWSFEED_FILENAMEINVALID);
        }
        if(!preg_match(self::MIMETYPE,$mimetype)) {
            throw new Exception("Invalid MIME type: $mimetype",
                EXN_NEWSFEED_MIMETYPEINVALID);
        }
        if(!preg_match(self::SIZE,$size)) {
            throw new Exception("Invalid size: $size",
                EXN_NEWSFEED_SIZEINVALID);
        }
        $this->filename=$filename;
        $this->mimetype=$mimetype;
        $this->size=$size;
        $this->fromnewsfeed=$fromnewsfeed;
    }
    
    /** 
     * Creates a new attachment object to represent the given file, which
     * will be moved (not copied!) into the appropriate newsfeed folder on
     * save.
     * @param string $path Full path of file
     * @param string $desiredname Desired name of file (may contain unsafe characters)
     * @param string $mimetype MIME type, or null to look one up
     * @return news_attachment New attachment object 
     */
    public static function create($path,$desiredname,$mimetype=null) {
        if(!file_exists($path)) {
            throw new Exception("File $path not found",
                EXN_NEWSFEED_NOSUCHFILE);            
        }
        if($mimetype==null) {
            $mimetype=mimeinfo('type',$desiredname);
        }
        $na=new news_attachment(null,$mimetype,filesize($path));
        $na->sourcepath=$path;
        $na->desiredname=$desiredname;
        return $na;
    }
    
    /**
     * Note: Not valid when created using create, until attachment is saved.
     * @return string Filename (not full path). The file is stored within the news feed's folder. 
     */
    public function get_filename() {
        if(is_null($this->filename)) {
            throw new Exception("Filename for attachment not yet defined",
                EXN_NEWSFEED_NOFILENAME);            
        }
        return $this->filename;
    }
    
    /**
     * @return string MIME type of file.
     */
    public function get_mime_type() {
        return $this->mimetype;
    }
    
    /**
     * @return int Size of file.
     */
    public function get_size() {
        return $this->size;
    }
    
    /**
     * Saves these details into the database with a new entry version.
     * @param int $newsfeedid ID of newsfeed
     * @param int $versionid ID of newly-created version 
     */
    function save($newsfeedid,$versionid) {
        if($this->sourcepath) { // Newly-added files must be moved into place
            // Folder
            global $CFG;
            $folder=$CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/'.$newsfeedid;
            mkdir_recursive($folder);
            // Initial filename is this version ID followed by sanitised version of filename
            $filename=$versionid.'_'.preg_replace('/[^'.self::FILELETTER.']/','_',strtolower($this->desiredname));
            // Unless that already exists (how unlikely is that)...
            while(file_exists($folder.'/'.$filename)) {
                $filename=preg_replace('/^(.*_)/','$1x',$filename);
            }
            // Remember name and move file
            if(!rename($this->sourcepath,$folder.'/'.$filename)) {
                throw new Exception("File {$this->sourcepath} could not be moved to $folder/$filename",
                    EXN_NEWSFEED_ATTACHMENTIOERROR);            
            }
            $this->filename=$filename;
            $this->sourcepath=null;
            $this->desiredname=null;
        } else if($this->fromnewsfeed!=$newsfeedid) { // File being saved to other feed must be copied
            // Folder
            global $CFG;
            $fromfolder=$CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/'.$this->fromnewsfeed;
            $tofolder=$CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/'.$newsfeedid;
            mkdir_recursive($tofolder);
            $filename=$this->filename;
            while(file_exists($tofolder.'/'.$filename)) {
                $filename=preg_replace('/^(.*_)/','$1x',$filename);
            }
            if(!copy($fromfolder.'/'.$this->filename,$tofolder.'/'.$filename)) {
                throw new Exception("File $fromfolder/{$this->filename} could not be copied to $tofolder/$filename",
                    EXN_NEWSFEED_ATTACHMENTIOERROR);            
            }
            $this->filename=$filename;
        }
        $this->fromnewsfeed=$newsfeedid;
        db_do('INSERT INTO prefix_newsfeed_files(versionid,filename,mimetype,filesize) VALUES('.
            sql_int($versionid).','.db_q($this->filename).','.db_q($this->mimetype).','.
            sql_int($this->size).')');
    }
    
    /** @return string Full path of file (if present) */
    public function get_path() {
        global $CFG;
        return $CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/'.$this->fromnewsfeed.'/'.$this->get_filename();        
    }
}

?>