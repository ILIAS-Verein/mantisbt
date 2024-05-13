<?php

require_api("version_api.php");

class MajorVersionFilter extends MantisFilter
{
    /**
     * @var mixed
     */
    private $custom_field_id;
    /**
	 * Field name, as used in the form element and processing.
	 */
	public $field = "major_version";

	/**
	 * Filter title, as displayed to the user.
	 */
	public $title = "Major Version";

	/**
	 * Filter type, as defined in core/constant_inc.php
	 */
	public $type = FILTER_TYPE_MULTI_STRING;

	/**
	 * Default filter value, used for non-list filter types.
	 */
	public $default = "0";

	/**
	 * Form element size, used for non-boolean filter types.
	 */
	public $size = null;

	/**
	 * Number of columns to use in the bug filter.
	 */
	public $colspan = 1;
	/**
	 * @var array
	 */
	private $major_versions = null;

	private $db_field;

	private $project_id;

	public function __construct($project_id, $custom_field_id, $db_field)
	{
		$this->project_id = $project_id;
		$this->db_field = $db_field;
        $this->custom_field_id = $custom_field_id;
	}


	/**
	 * Validate the filter input, returning true if input is
	 * valid, or returning false if invalid.  Invalid inputs will
	 * be replaced with the filter's default value.
	 * @param mixed $p_filter_input Filter field input.
	 * @return boolean Input valid (true) or invalid (false)
	 */
	public function validate( $p_filter_input ) {
		return true;
	}

	/**
	 * Build the SQL query elements 'join', 'where', and 'params'
	 * as used by core/filter_api.php to create the filter query.
	 * @param mixed $p_filter_input Filter field input.
	 * @return array Keyed-array with query elements; see developer guide
	 */
	function query( $p_filter_input ){
        global $g_db;
		$major_versions = array_keys($this->getMajorVersions());
		$wheres = [];
        $t_table_name = 'xcf_sort_' . $this->custom_field_id ?? "";

		foreach ($p_filter_input as $input){
			if(in_array($input, $major_versions)){

                $wheres[] = "{bug}." . $this->db_field . " LIKE " . $g_db->qstr( substr($input, 1) . ".%", false);
                if( $this->custom_field_id !== null ){
                    $search = '^.*\\|' . str_replace('.', '\\.', substr($input, 1)) . '\\.[\\w ]+\\|.*$';
                    $wheres[] = $t_table_name . ".value" . " RLIKE " . $g_db->qstr( $search );
                }
            }
		}
        var_dump($wheres);
        if( $this->custom_field_id !== null ){
            $t_filter_query['join']  = 'LEFT OUTER JOIN {custom_field_string} ' . $t_table_name . ' ON {bug}.id = ' . $t_table_name . '.bug_id AND ' . $t_table_name . '.field_id = ' . $this->custom_field_id;
        }

        $t_filter_query['where'] = !empty($wheres) ? "(" . implode(" OR ", $wheres). ")" : "";

		return $t_filter_query;
	}

	/**
	 * Display the current value of the filter field.
	 * @param mixed $p_filter_value Filter field input.
	 * @return string Current value output
	 */
	function display( $p_filter_value ){
		return substr($p_filter_value, 1) . ".x";
	}

	/**
	 * For list type filters, define a keyed-array of possible
	 * filter options, not including an 'any' value.
	 * @return array Filter options keyed by value=>display
	 */
	public function options() {
		return $this->getMajorVersions();
	}

	/**
	 * @param $project_ids
	 * @return array
	 */
	private function getMajorVersions(){

		if($this->major_versions !== null){
			return $this->major_versions;
		}

		$all = version_get_all_rows($this->project_id);
		$this->major_versions = [];
		foreach($all as $item){
			$version = explode(".", $item["version"]);
			if(is_numeric($version[0]))
			switch(count($version) ){
				case 2:
					$name = $version[0];
					if(!isset($this->major_versions["v" .$name]))
						$this->major_versions["v" .$name] = $name . ".x";
					break;
				case 3:
					$name = $version[0] . "." . $version[1];
					if(!isset($this->major_versions["v" .$name]))
						$this->major_versions["v" .$name] = $name . ".x";
					break;
				default:
					break;
			}
		}
		return $this->major_versions;
	}
}