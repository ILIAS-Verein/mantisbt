<?php
class SemanticVersionFilterPlugin extends MantisPlugin {

	function register() {
		$this->name = 'SemanticVersionFilter';    # Proper name of plugin
		$this->description = 'Filter fields for "Product Version" required.';    # Short description of the plugin
		$this->page = '';           # Default plugin page

		$this->version = '1.0';     # Plugin version string
		$this->requires = array(    # Plugin dependencies
			'MantisCore' => '2.3.0',  # Should always depend on an appropriate
			# version of MantisBT
		);

		$this->author = 'Fabian Wolf';         # Author/team name
		$this->contact = 'wolf@ilias.de';        # Author/team e-mail address
		$this->url = 'ilias.de';            # Support webpage
	}

	function init() {
	}

	function events() {
		return array(
			'EVENT_FILTER_FIELDS' => EVENT_TYPE_CHAIN
		);
	}

	function hooks() {
		return array(
			'EVENT_FILTER_FIELDS' => 'addFilter'
		);
	}

	function addFilter( $p_event, $p_chained_param )
	{
		$project = helper_get_current_project();
		if(version_should_show_product_version($project))
		{
			plugin_require_api("VersionFilter.php");
			$p_chained_param[] = new VersionFilter($project);
		}
		return $p_chained_param;
	}
}