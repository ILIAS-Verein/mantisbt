<?php

require_api("version_api.php");

class AlsoVersionFilter extends MantisFilter
{
    private $versions;
    /**
	 * Field name, as used in the form element and processing.
	 */
	public $field = "version";

	/**
	 * Filter title, as displayed to the user.
	 */
	public $title = "Version";

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
	 * @var int
	 */
	private $custom_field_id;

	private $db_field;

	private $project_id;

	public function __construct($project_id, $title, $custom_field_id, $db_field)
	{
		$this->project_id = $project_id;
        $this->title = $title;
		$this->db_field = $db_field;
        $this->custom_field_id = $custom_field_id;
        $this->field = $db_field . '_' . $custom_field_id;
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
		$versions = array_keys($this->getVersions());
		$wheres = [];
        $t_table_name = 'xcf_sort_' . $this->custom_field_id;

		foreach ($p_filter_input as $input){
			if(in_array($input, $versions)){
                $wheres[] = "{bug}." . $this->db_field . " = ". $g_db->qstr(substr($input, 1), false);
                $wheres[] = $t_table_name . ".value" . " LIKE " . $g_db->qstr("%|" . substr($input, 1) ."|%", false);
            }
		}
        $t_cf_join_clause = 'LEFT OUTER JOIN {custom_field_string} ' . $t_table_name . ' ON {bug}.id = ' . $t_table_name . '.bug_id AND ' . $t_table_name . '.field_id = ' . $this->custom_field_id;

		$t_filter_query['where'] = "(" . implode(" OR ", $wheres). ")";
        $t_filter_query['join'] = $t_cf_join_clause;

		return $t_filter_query;
	}

	/**
	 * Display the current value of the filter field.
	 * @param mixed $p_filter_value Filter field input.
	 * @return string Current value output
	 */
	function display( $p_filter_value ){
		return substr($p_filter_value, 1);
	}

	/**
	 * For list type filters, define a keyed-array of possible
	 * filter options, not including an 'any' value.
	 * @return array Filter options keyed by value=>display
	 */
	public function options() {
		return $this->getVersions();
	}

	/**
	 * @param $project_ids
	 * @return array
	 */
	private function getVersions() {
		if($this->versions !== null){
			return $this->versions;
		}

		$all = version_get_all_rows($this->project_id);
		$this->versions = [];
		foreach($all as $item){
            $this->versions["v" . $item["version"]] = $item["version"];
		}
		return $this->versions;
	}
}