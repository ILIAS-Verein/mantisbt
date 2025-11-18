<?php
class RequiredProductVersionPlugin extends MantisPlugin {
	private $is_rest = false;
	private $is_soap = false;

	function register() {
		$this->name = 'RequiredProductVersion';    # Proper name of plugin
		$this->description = 'Making field "Product Version" required.';    # Short description of the plugin
		$this->page = '';           # Default plugin page

		$this->version = '1.1';     # Plugin version string
		$this->requires = array(    # Plugin dependencies
			'MantisCore' => '2.3.0',  # Should always depend on an appropriate
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
			'EVENT_REST_API_ROUTES' => EVENT_TYPE_CHAIN,
			'EVENT_LAYOUT_CONTENT_END' => EVENT_TYPE_EXECUTE,
            		'EVENT_MANAGE_VERSION_CREATE' => EVENT_TYPE_EXECUTE,
            		'EVENT_MANAGE_VERSION_DELETE' => EVENT_TYPE_EXECUTE
		);
	}

	function hooks() {
		return array(
			'EVENT_REPORT_BUG_DATA' => 'validation',
			'EVENT_UPDATE_BUG_DATA' => 'validation',
			'EVENT_REST_API_ROUTES' => 'is_rest',
			'EVENT_LAYOUT_CONTENT_END' => 'add_javascript',
            		'EVENT_MANAGE_VERSION_CREATE' => 'version_field_update',
            		'EVENT_MANAGE_VERSION_DELETE' => 'version_field_update'
		);
	}

	function is_rest($p_event, $p_chained_param){
		$this->is_rest = true;
		return $p_chained_param;
	}

	function validation( $p_event, $p_chained_param ) {
		if($this->is_rest || $this->is_soap)
		{
			return $p_chained_param;
		}

		if( is_blank( $p_chained_param->version  )
			&& version_should_show_product_version( $p_chained_param->project_id ))
		{
			error_parameters( lang_get( 'product_version' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		return $p_chained_param;
	}

	function add_javascript($p_event){

		echo "\t", '<script type="text/javascript" src="', plugin_file('custom.js'), '"></script>', "\n";
	}

        function version_field_update($p_event){
		$t_query = new DbQuery();
        	$t_sql = 'SELECT version FROM {project_version} WHERE obsolete=0 AND version NOT LIKE "n.a." AND project_id=1 GROUP BY version  ORDER BY date_order DESC;';
        	$t_query->sql( $t_sql );

        	$version_list = [];

        	while( $t_row = $t_query->fetch() ) {
            		$version_list[] = $t_row['version'];
        	}

        	$t_query = new DbQuery();
        	$t_sql = 'SELECT id FROM {custom_field} WHERE name IN ("Also in Version", "Also fixed in Version");';
        	$t_query->sql( $t_sql );

        	$field_list = [];

        	while( $t_row = $t_query->fetch() ) {
            		$field_list[] = $t_row['id'];
        	}
		$t_query = 'UPDATE {custom_field}
				  SET possible_values=' . db_param() . '
				  WHERE id IN (' . join(', ', $field_list).')';

        	db_query( $t_query, array( implode('|', $version_list),  ) );
    }

}
