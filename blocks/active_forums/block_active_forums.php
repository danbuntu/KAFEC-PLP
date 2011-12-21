<?php

//------------------------------------------------------------------------------
// Constants
define('B_ACTIVE_FORUMS_DEFAULT_DAYS',         7);
define('B_ACTIVE_FORUMS_DEFAULT_TITLE',       20);
define('B_ACTIVE_FORUMS_DEFAULT_DISCUSSIONS', 10);
define('B_ACTIVE_FORUMS_DEFAULT_INCLUDE_NEWS', 0);

//------------------------------------------------------------------------------
// Main game class
class block_active_forums extends block_base {

    //--------------------------------------------------------------------------
    function init() {
        $this->title = get_string('active_forums_title','block_active_forums');
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2010073000;
    }

    //--------------------------------------------------------------------------
    function preferred_width() {
        // The preferred value is in pixels
        return 190;
    }

    //--------------------------------------------------------------------------
    function instance_allow_config() {
        return true;
    }
    
    //--------------------------------------------------------------------------
    function instance_allow_multiple() {
        return true;
    }

    //--------------------------------------------------------------------------
    function applicable_formats() {
        return array('course-view' => true);
    }
    
    function get_content() {

        // Access to settings needed
        global $COURSE, $CFG;
        
        // If content has already been generated, don't waste time generating it again
        if ($this->content !== NULL) {
            return $this->content;
        }
        
        // Initialise the content
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        if (empty($this->instance)) {
            return $this->content;
        }
        
        //Result of user configure how many days of active forum display or setting to default value
        $select_days = isset($this->config->selectdays)?$this->config->selectdays:B_ACTIVE_FORUMS_DEFAULT_DAYS;

        //Result of user configure how many post can display or setting to default value
        $max_discussions = isset($this->config->max_discussions)?$this->config->max_discussions:B_ACTIVE_FORUMS_DEFAULT_DISCUSSIONS;

        //Collect the result of active forum in selected course and time period
        $query = 'SELECT d.id,d.forum,d.name,COUNT(p.id) as posts,d.timemodified 
                  FROM '.$CFG->prefix.'forum f, '.$CFG->prefix.'forum_discussions d, '.$CFG->prefix.'forum_posts p 
                  WHERE f.course='.$COURSE->id.'
                  '.(empty($this->config->include_news) || $this->config->include_news=='0'?'AND f.type!=\'news\'':'').'
                  AND f.id=d.forum
                  AND p.discussion=d.id
                  AND p.created>'.(time()- $select_days*86400).' 
                  GROUP BY d.id,d.forum,d.name, d.timemodified 
                  ORDER BY posts DESC
                  LIMIT '.$max_discussions;
                 
        //Print subtitle
        $this->content->text .= '<div class="active_forums_tagcloud_label">'.get_string('subtitlelabel','block_active_forums',$select_days).'</div>';
        $this->content->text .= '<div class="active_forums_tagcloud">';

        // process results        
        $results=get_records_sql($query);
        if ($results && is_array($results) && count($results)>0) {
            
            // reagange the result arrays
            $results = array_values($results);
            $num_discussions = count($results);
            
            // find max number of dicussions amount
            $maximum = $results[0]->posts;

            //Print the active forum post titles in tagcloud form
            $discussions = array_values($results);
            for($i=$num_discussions-$num_discussions%2-1; $i>0; $i-=2) {
                $this->content->text .= $this->get_tag($discussions[$i]->name, $discussions[$i]->id, $discussions[$i]->posts/$maximum);
            }
            for($i=0; $i<$num_discussions; $i+=2) {
                $this->content->text .= $this->get_tag($discussions[$i]->name, $discussions[$i]->id, $discussions[$i]->posts/$maximum);
            }
        }

        // No active discussions
        else {
            $this->content->text .= get_string('noactiveforums','block_active_forums');
        }
        
        $this->content->text .= '</div>';
        return $this->content;
    }
    
    function get_tag($name, $id, $weight) {
        global $CFG;
        
        //Result of user configure how long the forum tile to display or setting to default value
        $title_length = isset($this->config->title_length)?$this->config->title_length:B_ACTIVE_FORUMS_DEFAULT_TITLE;

        // Create the tag text and link
        $tag  = '<span class="active_forums_tagcloud_title" style="font-size:'.(100*(1+$weight/2)).'%;font-weight:'.((int)($weight*9+1)*100).';">';
        $tag .= '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$id.'">';
        $tag .= (strlen($name)>$title_length? substr($name,0,$title_length).'...':$name);
        $tag .= '</a></span> &nbsp; ';
        
        return $tag;
    }
}
?>