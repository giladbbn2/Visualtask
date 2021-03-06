<?php

class Visualtask { 

	public $resource_types = array();
	public $mysql_db = null;	// Queryable interface
	public $limit_size_default = 10;
	public $limit_size_max = 10;

	// general purpose sanitization, also searches for "(" and ")" on top of usual suspects
	protected $sanitize_search = ["\"", "'", "<", ">", "\0", "\b", "\r", "\t", "\Z", "\\", "\x00", "\n", "\x1a", "(", ")"];
	protected $mysql_aggregate_funcs = array(
		"count" => array("count(",")"),
		"countd" => array("count(distinct ",")"),
		"sum" => array("sum(",")"),
		"avg" => array("avg(",")"),
		"max" => array("max(",")"),
		"min" => array("min(",")"),
		"gconcat" => array("group_concat(",")"),
		"gconcatd" => array("group_concat(distinct ",")"),
		"year" => array("year(",")"),
		"month" => array("month(",")"),
		"day" => array("day(",")"),
		"date" => array("date(",")"),
		"hour" => array("hour(",")"),
		"yearmonth" => array("date_format(",",'%Y-%m')")
	);

	protected $preset = null;
	protected $options = null;
	protected $sql_transform_cbs = array();
	protected $allowed = array();



	protected function s($str){

		return str_replace($this->sanitize_search, "", $str);

	}

	protected function query_mysql(&$queries, &$allowed_entities){

		if ($this->mysql_db === null)
			return false;
			
		if (@$this->mysql_db->is_connected !== true)
			return false;

		$sql_arr = array();

		foreach ($queries as $query_id => $query){

			if (!isset($query["select"]) || !isset($query["from"]))
				continue;


			// check that table is allowed

			if (!in_array($query["from"], $allowed_entities))
				continue;


			// handle select

			$sql = "select ";

			$select_count = count($query["select"]);
			$is_first = true;
			for ($i=0; $i<$select_count; $i++){

				if (!isset($query["select"][$i]["fieldName"]))
					continue;

				$x = $this->s($query["select"][$i]["fieldName"]);

				$function = "";
				if (isset($query["select"][$i]["function"]))
					$function = $this->s($query["select"][$i]["function"]);
					
				if (strlen($function) > 0 && array_key_exists($function, $this->mysql_aggregate_funcs))
					$x = $this->mysql_aggregate_funcs[$function][0] . $x . $this->mysql_aggregate_funcs[$function][1];

				if (isset($query["select"][$i]["alias"]))
					$x .= " as " . $this->s($query["select"][$i]["alias"]);

				if (!$is_first)
					$sql .= ", " . $x;
				else
					$sql .= $x;

				$is_first = false;
			}


			// handle from

			$sql .= " from " . $this->s($query["from"]);


			// handle where

			// for simplicity $query["where"] is a list of AND criteria

			// automatically add WHERE 1=1

			$sql .= " where 1=1";

			if (isset($query["where"]) && is_array($query["where"])){

				$where = array();

				$where_count = count($query["where"]);
				for ($i=0; $i<$where_count; $i++){
					$item = $query["where"][$i];

					if (!isset($item["fieldName"]) || !isset($item["fieldType"]) || !isset($item["op"]) || !isset($item["val"]))
						continue;

					$field_name = $this->s($item["fieldName"]);
					$field_type = $this->s($item["fieldType"]);
					$op = $item["op"];
					$val = $this->s($item["val"]);	// unquoted, could be comma delimited in case of op == "in"

					switch ($op){
						case "=":
						case ">":
						case ">=":
						case "<":
						case "<=":
						case "!=":

							if ($field_type === "datetime" || $field_type === "string")
								$where[] = $field_name . " " . $op . " '" . $val . "'";
							else
								$where[] = $field_name . " " . $op . " " . $val;

							break;

						case "in":

							if ($field_type === "datetime" || $field_type === "string")
								$where[] = $field_name . " in('" . implode("','", explode(",", $val)) . "')";
							else
								$where[] = $field_name . " in(" . $val . ")";

							break;

						case "startswith":

							if ($field_type === "datetime" || $field_type === "string")
								$where[] = $field_name . " like '" . $val . "%'";

							break;

						case "endswith":

							if ($field_type === "datetime" || $field_type === "string")
								$where[] = $field_name . " like '%" . $val . "'";

							break;

						case "includes":

							if ($field_type === "datetime" || $field_type === "string")
								$where[] = $field_name . " like '%" . $val . "%'";

							break;

					}

					if (isset($item["useDictionaryId"])){
						// TODO
					}
				}

				if (count($where) > 0)
					$sql .= " and " . implode(" and ", $where);

			}


			// handle group by

			if (isset($query["groupBy"])){

				$groupby_count = count($query["groupBy"]);

				if ($groupby_count > 0)
					$sql .= " group by ";

				$is_first = true;
				for ($i=0; $i<$groupby_count; $i++){

					if (!isset($query["groupBy"][$i]["fieldName"]))
						continue;

					$x = $this->s($query["groupBy"][$i]["fieldName"]);

					$function = "";
					if (isset($query["groupBy"][$i]["function"]))
						$function = $this->s($query["groupBy"][$i]["function"]);
						
					if (strlen($function) > 0 && array_key_exists($function, $this->mysql_aggregate_funcs))
						$x = $this->mysql_aggregate_funcs[$function][0] . $x . $this->mysql_aggregate_funcs[$function][1];

					if (!$is_first)
						$sql .= ", " . $x;
					else
						$sql .= $x;

					$is_first = false;
				}
			}


			// handle order by

			if (isset($query["orderBy"])){

				$orderby_count = count($query["orderBy"]);

				if ($orderby_count > 0)
					$sql .= " order by ";

				$is_first = true;
				for ($i=0; $i<$orderby_count; $i++){

					if (!isset($query["orderBy"][$i]["fieldName"]))
						continue;

					$x = $this->s($query["orderBy"][$i]["fieldName"]);

					$function = "";
					if (isset($query["orderBy"][$i]["function"]))
						$function = $this->s($query["orderBy"][$i]["function"]);

					if (strlen($function) > 0 && array_key_exists($function, $this->mysql_aggregate_funcs))
						$x = $this->mysql_aggregate_funcs[$function][0] . $x . $this->mysql_aggregate_funcs[$function][1];

					if (isset($query["orderBy"][$i]["dir"]) && $query["orderBy"][$i]["dir"] == "desc")
						$x .= " desc";
					else
						$x .= " asc";

					if (!$is_first)
						$sql .= ", " . $x;
					else
						$sql .= $x;

					$is_first = false;
				}
			}			


			// handle limit

			$sql .= " limit ";

			if (isset($query["limit"])){

				if (isset($query["limit"]["offset"]))
					$limit_offset = intval($query["limit"]["offset"]);
				else
					$limit_offset = "0";

				if (isset($query["limit"]["size"])){
					$limit_size = intval($query["limit"]["size"]);

					if ($limit_size > $this->limit_size_max)
						$limit_size = $this->limit_size_max;
				} else {
					$limit_size = $this->limit_size_default;
				}

			} else {

				$limit_offset = 0;
				$limit_size = $this->limit_size_default;

			}

			$sql .= $limit_offset . ", " . $limit_size;

			if (isset($this->sql_transform_cbs[$query_id]))
				$this->sql_transform_cbs[$query_id]($sql, $query);

			$sql_arr[] = $sql;
		}

		if (isset($this->options["debug"]) && is_array($this->options["debug"]))
			$this->options["debug"]["mysql"] = $sql_arr;

		try {
			$results = $this->mysql_db->query($sql_arr);
		} catch (Exception $ex){
			return false;
		}
		
		$i = 0;
		foreach ($queries as $query_id => $query){
			$this->options["results"][$query_id] = &$results[$i];
			$i++;
		}

		return true;
	}

	public function query(){

		$allowed_types = array_keys($this->allowed);

		$allowed_queries_by_real_type = array();
		$allowed_entities_by_real_type = array();

		foreach ($this->options["queries"] as $query_id => $query){

			if (!isset($query["type"]))
				continue;

			$type = $query["type"];

			if (!in_array($type, $allowed_types))
				continue;

			if (!isset($this->resource_types[$type]))
				continue;

			$real_type = $this->resource_types[$type];
			
			if (!isset($allowed_queries_by_real_type[$real_type]))
				$allowed_queries_by_real_type[$real_type] = array();

			$allowed_queries_by_real_type[$real_type][$query_id] = $query;

			if (!isset($allowed_entities_by_real_type[$real_type]))
				$allowed_entities_by_real_type[$real_type] = $this->allowed[$type];

		}
		
		foreach ($allowed_queries_by_real_type as $real_type => $queries){
			$method_name = "query_" . $real_type;
			@$this->$method_name($queries, $allowed_entities_by_real_type[$real_type]);
		}
		
		return true;
	}

	public function preset($preset_instance, $options, $sql_transform_cbs = array()){

		if ($options === "" || $options === null || !is_subclass_of($preset_instance, "VisualtaskPresetBase"))
			return false;

		if (is_array($options))	// already decoded into an array
			$this->options = $options;
		else
			$this->options = @json_decode($options, true);

		if (!isset($this->options["queries"]) || !is_array($this->options["queries"]))
			return false;
			
		if (isset($this->options["debug"]) && $this->options["debug"] === true)
			$this->options["debug"] = array();
		else
			unset($this->options["debug"]);

		$this->options["results"] = array();
		
		$this->preset = $preset_instance;
		
		$this->allowed = &$this->preset->allowed;

		$this->sql_transform_cbs = &$sql_transform_cbs;

		$this->preset->pre_query($this->options);

		$this->query();

		$this->preset->post_query($this->options);
		
		return $this->options;
	}
}






/*

	All presets must extend VisualtaskPresetBase

*/
abstract class VisualtaskPresetBase {

	public $allowed = array();

	public function pre_query(&$options){

	}

	public function post_query(&$options){

	}
}

