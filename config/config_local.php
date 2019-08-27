<?php
    /* config_local.php - Local configuration file
       -------------------------------------------
       In this file you can place your custom configuration, instead of editing
       config_inc.php directly, although this is possible to.

       Please note: Everything you set here has precedence over settings defined in the config_inc.php. 
    */

	$g_window_title = 'ILIAS Bug Tracker';

	$g_enable_sponsorship = OFF;
	$g_my_view_boxes = array ( 'assigned' => '6',
                            'unassigned' => '7',
                            'reported' => '1',
                            'resolved' => '5',
                            'recent_mod' => '4',
                            'monitored' => '3',
                            'feedback' => '2');
	
	$g_access_levels_enum_string = '10:viewer,25:reporter,40:vereinsmitglied,55:developer,70:manager,90:administrator';

        $g_auto_set_status_to_assigned = OFF;
	
	#Revised enum string with new custom statuses
    	$g_status_enum_string =  '10:open,15:unassigned,20:feedback,23:needs JF decision,25:postponed,35:funding needed,50:assigned,60:fixing acc to prio,80:resolved,90:closed';


    	# Status color additions
        $g_status_colors['open'] 		    = '#fcbdbd'; # red    (scarlet red #ef2929)
    	$g_status_colors['unassigned']              = '#D2B48C'; // brown (tan  #D2B48C)
    	$g_status_colors['needs JF decision']	    = '#f0fff0'; // HoneyDew 
	$g_status_colors['postponed']               = '#ffcd85'; // orange (orango  #f57900)
    	$g_status_colors['funding needed']          = '#FFFACD'; // yellow (LemonChiffon #FFFACD)
    	$g_status_colors['fixing acc to prio']      = '#c2dfff'; // blue (sky blue    #729fcf)
	$g_status_colors['assigned']		    = '#B0C4DE'; // blue (LightSteelBlue #B0C4DE)

        $g_status_enum_workflow[OPEN]  			='15:unassigned,20:feedback,23:needs JF decision,25:postponed,35:funding needed,60:fixing acc to prio,80:resolved,90:closed';
        $g_status_enum_workflow[UNASSIGNED] 		= '10:open,20:feedback,23:needs JF decision';
        $g_status_enum_workflow[FEEDBACK]       	='10:open,23:needs JF decision,90:closed';
	$g_status_enum_workflow[NEEDS_JF_DECISION]   	='10:open,15:unassigned,20:feedback,25:postponed,35:funding needed,50:assigned,60:fixing acc to prio,80:resolved,90:closed';
        $g_status_enum_workflow[POSTPONED]   		='23:needs JF decision,35:funding needed,60:fixing acc to prio,80:resolved';
        $g_status_enum_workflow[FUNDING_NEEDED]      	='23:needs JF decision,60:fixing acc to prio,80:resolved';
        $g_status_enum_workflow[ASSIGNED] 		='15:unassigned,20:feedback,23:needs JF decision,25:postponed,35:funding needed,60:fixing acc to prio,80:resolved,90:closed';
        $g_status_enum_workflow[FIXING_ACC_TO_PRIO]     ='23:needs JF decision,35:funding needed,60:fixing acc to prio,80:resolved';
        $g_status_enum_workflow[RESOLVED]       	='20:feedback,23:needs JF decision,90:closed';
        $g_status_enum_workflow[CLOSED]         	='20:feedback,23:needs JF decision';

	$g_antispam_max_event_count = 40;
	//only for MantisBT version < 2.0
#	$g_top_include_page = $g_absolute_path."/top_include_page.html";


?>
