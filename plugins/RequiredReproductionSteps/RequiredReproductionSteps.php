<?php
class RequiredReproductionSteps extends MantisPlugin {
	function register() {
		$this->name = 'RequiredReproductionSteps';    # Proper name of plugin
		$this->description = 'Making field "Reproduction Steps" required.';    # Short description of the plugin
		$this->page = '';           # Default plugin page

		$this->version = '1.0';     # Plugin version string
		$this->requires = array(    # Plugin dependencies
			'MantisCore' => '2.0',  # Should always depend on an appropriate
			# version of MantisBT
		);

		$this->author = 'Fabian Wolf';         # Author/team name
		$this->contact = 'wolf@ilias.de';        # Author/team e-mail address
		$this->url = 'ilias.de';            # Support webpage
	}

	function events() {
		return array(
			'EVENT_REPORT_BUG_FORM' => EVENT_TYPE_EXECUTE,
			'EVENT_REPORT_BUG_DATA' => EVENT_TYPE_CHAIN,
			'EVENT_UPDATE_BUG_DATA' => EVENT_TYPE_CHAIN
		);
	}

	function hooks() {
		return array(
			/*'EVENT_EXAMPLE_FOO' => 'foo',
			'EVENT_EXAMPLE_BAR' => 'bar',*/
			'EVENT_REPORT_BUG_FORM' => 'form',
			'EVENT_REPORT_BUG_DATA' => 'validation',
			'EVENT_UPDATE_BUG_DATA' => 'validation'
		);
	}

	function config() {
		return array(
			/*'foo_or_bar' => 'foo',*/
		);
	}

	function form( $p_event ) {

	}

	function validation( $p_event, $p_chained_param ) {

		if( is_blank( $p_chained_param->steps_to_reproduce  ) ) {
			error_parameters( lang_get( 'steps_to_reproduce ' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

	}

}