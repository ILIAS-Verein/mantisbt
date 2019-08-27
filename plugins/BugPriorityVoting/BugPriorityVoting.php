<?php

/** 
 * requires check_custom_field.php
 */
require_once( config_get( 'plugin_path' ) 
       . 'BugPriorityVoting' . DIRECTORY_SEPARATOR . 'check_custom_field.php' );

/**
 * A plugin that manages priority voting on bugs.
 */
class BugPriorityVotingPlugin extends MantisPlugin {
    
    function register() {
        $this->name = 'BugPriorityVoting';
        $this->description = 'A plugin that manages priority voting on bugs';

        $this->version = '2.0';
        $this->requires = array( 'MantisCore' => '2.3.0', );

        $this->author = 'Anton';
        $this->contact = 'anton@ilias.de';
        $this->url = 'www.ilias.de';
    }

    function hooks() {
        return array(
            'EVENT_REPORT_BUG' => 'reported',
            'EVENT_VIEW_BUG_DETAILS' => "view_bug_details",
        ); 
    }
    
    function reported($p_event, $bugdata, $bug_id){
        check($bug_id);
    }
    
    function view_bug_details($p_event, $bug_id){
        check($bug_id);
    }
}
