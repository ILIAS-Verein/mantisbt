<?php
# Translation for Custom Status Code: open, unassigned, postponed, funding needed, fixing
switch( $g_active_language ) {

/**	case 'french':
		$s_status_enum_string = '10:nouveau,20:commentaire,30:accepté,40:confirmé,50:affecté,60:à tester,80:résolu,90:fermé';

		$s_testing_bug_title = 'Mettre le bogue en test';
		$s_testing_bug_button = 'A tester';

		$s_email_notification_title_for_status_bug_testing = 'Le bogue suivant est prêt à être TESTE.';
		break;
*/
	default: # english
		
		$s_fixing_priority = "Fixing Priority";		
		$g_access_levels_enum_string = '10:viewer,25:reporter,40:vereinsmitglied,55:developer,70:manager,90:administrator';

		$s_status_enum_string = '10:open,15:unassigned,20:feedback,23:needs JF decision,25:postponed,35:funding needed,50:assigned,60:fixing acc to prio,80:resolved,90:closed'; 
		
		$s_open_bug_title = 'Mark issue Open';
		$s_open_bug_button = 'Open';

		$s_email_notification_title_for_status_bug_open = 'The following issue is Open.';

		$s_unassigned_bug_title = 'Mark issue Unassigned';
                $s_unassigned_bug_button = 'Unassigned';

                $s_email_notification_title_for_status_bug_unassigned = 'The following issue is Unissigned.';
		
		$s_needs_JF_decision_bug_title = 'Mark issue Needs JF decision';
		$s_needs_JF_decision_bug_button = 'Needs JF decision';
		
		$s_postponed_bug_title = 'Mark issue Postponed';
                $s_postponed_bug_button = 'Postponed';

                $s_email_notification_title_for_status_bug_postponed = 'The following issue is Postponed.';

		$s_funding_needed_bug_title = 'Mark issue Funding needed';
                $s_funding_needed_bug_button = 'Funding needed';

                $s_email_notification_title_for_status_bug_funding_needed = 'The following issue needs funding.';
		
		$s_fixing_acc_to_prio_bug_title = 'Mark issue Fixing acccording to priorities';
                $s_fixing_acc_to_prio_bug_button = 'Fixing acc. to prio';

                $s_email_notification_title_for_status_bug_fixing_acc_to_prio = 'The following issue is beeing fixed according to the priorities.';
		
		break;
}
?>
