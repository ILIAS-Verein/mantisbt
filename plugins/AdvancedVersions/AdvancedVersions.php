<?php

require_api( 'custom_field_api.php' );

class AdvancedVersionsPlugin extends MantisPlugin
{
    private $cache = [];
    private $is_rest = false;
    private $is_soap = false;

    function register() {
		$this->name = 'AdvancedVersions';    # Proper name of plugin
		$this->description = 'Adding a major version filter.\nAdding Filter for further version fields.\nMaking field "Product Version" required.';    # Short description of the plugin
		$this->page = '';           # Default plugin page

		$this->version = '1.0';     # Plugin version string
		$this->requires = array(    # Plugin dependencies
			'MantisCore' => '2.3.0',  # Should always depend on an appropriate
			# version of MantisBT
		);

		$this->author = 'Fabian Wolf';         # Author/team name
		$this->contact = 'wolf@ilias.de';        # Author/team e-mail address
		$this->url = 'www.ilias.de';            # Support webpage
	}

    public function init() {
        if(basename($_SERVER['REQUEST_URI']) == "mantisconnect.php") {
            $this->is_soap = true;
        }
    }

	public function events() {
		return array(
            'EVENT_REPORT_BUG_DATA' => EVENT_TYPE_CHAIN,
            'EVENT_UPDATE_BUG_DATA' => EVENT_TYPE_CHAIN,
            'EVENT_REST_API_ROUTES' => EVENT_TYPE_CHAIN,
            'EVENT_LAYOUT_CONTENT_END' => EVENT_TYPE_EXECUTE,
			'EVENT_FILTER_FIELDS' => EVENT_TYPE_CHAIN,
            'EVENT_MANAGE_VERSION_CREATE' => EVENT_TYPE_EXECUTE,
            'EVENT_MANAGE_VERSION_DELETE' => EVENT_TYPE_EXECUTE
		);
	}

	public function hooks() {
		return array(
            'EVENT_REPORT_BUG_DATA' => 'validation',
            'EVENT_UPDATE_BUG_DATA' => 'validation',
            'EVENT_REST_API_ROUTES' => 'is_rest',
            'EVENT_LAYOUT_CONTENT_END' => 'add_javascript',
			'EVENT_FILTER_FIELDS' => 'add_filter',
            'EVENT_MANAGE_VERSION_CREATE' => 'version_field_update',
            'EVENT_MANAGE_VERSION_DELETE' => 'version_field_update'
		);
	}

    public function validation( $p_event, $p_chained_param ) {
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

    public function is_rest($p_event, $p_chained_param){
        $this->is_rest = true;
        return $p_chained_param;
    }

    public function add_javascript($p_event){

        echo "\t", '<script type="text/javascript" src="', plugin_file('custom.js'), '"></script>', "\n";
    }

    function add_filter( $p_event, $p_chained_param )
    {
        $project = helper_get_current_project();

        if(!version_should_show_product_version($project)){
            return $p_chained_param;
        }

        $field_list = $this->getCustomFieldIds();

        plugin_require_api("MajorVersionFilter.php");
        $p_chained_param[] = new MajorVersionFilter($project, $field_list["Also in Version"] ?? null, 'version');


        if( isset($field_list["Also in Version"]) && $this->hasCustomVersionField($project, $field_list["Also in Version"]) ){
            plugin_require_api("AlsoVersionFilter.php");
            $p_chained_param[] = new AlsoVersionFilter($project, "Advanced Version", $field_list["Also in Version"], 'version');
        }

        if( isset($field_list["Also fixed in Version"]) && $this->hasCustomVersionField($project, $field_list["Also fixed in Version"]) )
        {
            plugin_require_api("AlsoVersionFilter.php");
            $p_chained_param[] = new AlsoVersionFilter($project, "Advanced Fixed in Version", $field_list["Also fixed in Version"], 'fixed_in_version');
        }

        return $p_chained_param;
    }

    public function version_field_update($p_event, $p_chained_param){
        $version_list = $this->getActiveVersions();
        $field_list = $this->getCustomFieldIds();

        $t_query = 'UPDATE {custom_field}
				  SET possible_values=' . db_param() . '
				  WHERE id IN (' . join(', ', $field_list).')';

        db_query( $t_query, array( implode('|', $version_list),  ) );
    }

    private function getCustomFieldIds(){
        if(isset($this->cache['custom_field_ids'])) {
            return $this->cache['custom_field_ids'];
        }

        $t_query = new DbQuery();
        $t_sql = 'SELECT name, id FROM {custom_field} WHERE name IN ("Also in Version", "Also fixed in Version");';
        $t_query->sql( $t_sql );

        $field_list = [];

        while( $t_row = $t_query->fetch() ) {
            $field_list[$t_row['name']] = $t_row['id'];
        }
        $this->cache['custom_field_ids'] = $field_list;

        return $field_list;
    }

    private function getActiveVersions(){
        if(isset($this->cache['active_versions'])) {
            return $this->cache['active_versions'];
        }

        $t_query = new DbQuery();
        $t_sql = 'SELECT version FROM {project_version} WHERE obsolete=0 AND version NOT LIKE "n.a." AND project_id=1 GROUP BY version ORDER BY date_order DESC;';
        $t_query->sql( $t_sql );

        $version_list = [];

        while( $t_row = $t_query->fetch() ) {
            $version_list[] = $t_row['version'];
        }
        $this->cache['version_list'] = $version_list;

        return $version_list;
    }

    private function hasCustomVersionField($project_id, $field_id)
    {
        custom_field_get_linked_ids($project_id);
        return custom_field_is_linked($field_id, $project_id);
    }
}