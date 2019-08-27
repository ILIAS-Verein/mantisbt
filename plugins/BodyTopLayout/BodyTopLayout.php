<?php

/**
 * Puts additional information at the top of the page
 */
class BodyTopLayoutPlugin extends MantisPlugin {
    
    function register() {
        $this->name = 'BodyTopLayout';
        $this->description = 'Puts additional information at the top of the page';

        $this->version = '1.0';
        $this->requires = array( 'MantisCore' => '2.3.0', );

        $this->author = 'Anton';
        $this->contact = 'anton@ilias.de';
        $this->url = 'www.ilias.de';
    }

    function hooks() {
        return array(
            'EVENT_LAYOUT_CONTENT_BEGIN' => "show_info",
        ); 
    }
    
    function show_info(){
        return '<div style="background-color:#dddedf;border:1px solid #aaa;">
            <li style="padding-left: 10px; padding-top: 5px; list-style-position: inside;"><a href="http://www.ilias.de/docu/goto_docu_pg_64419_4793.html" target="_blank">Hinweise zur Erstellung von Bugreports</a></li>
            <li style="padding-left: 10px; list-style-position: inside;"><a href="http://www.ilias.de/docu/goto_docu_pg_68562_4793.html" target="_blank">Hinweise zum Priorisieren von Bugreports</a> <span style="font-size:small;">(nur Institutionelle Vereinsmitglieder)</span></li>
            <li style="padding-left: 10px; padding-bottom: 5px;list-style-position: inside;"><span style="font-size:small;">Please report security issues to our security list </span><a href="mailto:security@lists.ilias.de" target="_blank">security@lists.ilias.de</a> </li>
        </div>';
    }

}
