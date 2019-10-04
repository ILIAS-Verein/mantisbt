<?php
class RequiredReproductionSteps extends MantisPlugin {
	private $is_rest = false;
	private $is_soap = false;

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

	function init() {
		if(basename($_SERVER['REQUEST_URI']) == "mantisconnect.php") {
			$this->is_soap = true;
		}
	}

	function events() {
		return array(
			'EVENT_REPORT_BUG_DATA' => EVENT_TYPE_CHAIN,
			'EVENT_UPDATE_BUG_DATA' => EVENT_TYPE_CHAIN,
			'EVENT_REST_API_ROUTES' => EVENT_TYPE_CHAIN
		);
	}

	function hooks() {
		return array(
			'EVENT_REPORT_BUG_DATA' => 'validation',
			'EVENT_UPDATE_BUG_DATA' => 'validation',
			'EVENT_REST_API_ROUTES' => 'is_rest'
		);
	}

	function is_rest($p_event, $p_chained_param){
		$this->is_rest = true;
	}

	function validation( $p_event, $p_chained_param ) {
		if($this->is_rest || $this->is_soap)
		{
			return;
		}

		if( is_blank( $p_chained_param->steps_to_reproduce  ) ) {
			error_parameters( lang_get( 'steps_to_reproduce ' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

	}

}