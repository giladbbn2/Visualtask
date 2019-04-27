<?php

namespace VisualTask\VisualTask;


/* All presets must extend VisualTaskPresetBase */
abstract class VisualTaskPresetBase {

	public $allowed = array();

	public function pre_query(&$options){

	}

	public function post_query(&$options){

	}
}

abstract class VisualTaskBase { 

	public $resource_types = array();
	public $db = null;	// Queryable interface
	public $limit_size_default = 10;
	public $limit_size_max = 10;

	protected $sanitize_search = ["\"", "'", "<", ">", "\0", "\b", "\r", "\t", "\Z", "\\", "\x00", "\n", "\x1a"];   //"(", ")"

	protected $preset = null;
	protected $options = null;
	protected $sql_transform_cbs = array();
	protected $allowed = array();



	protected function s($str){
		
        return str_replace($this->sanitize_search, "", $str);
        
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
		echo "1";
		if ($options === "" || $options === null || !is_subclass_of($preset_instance, "\\VisualTask\\VisualTask\\VisualTaskPresetBase"))
			return false;
		echo "2";
		if (is_array($options))	// already decoded into an array
			$this->options = $options;
		else
			$this->options = @json_decode($options, true);
		echo "3";
		if (!isset($this->options["queries"]) || !is_array($this->options["queries"]))
			return false;
		echo "4";
		if (isset($this->options["debug"]) && $this->options["debug"] === true)
			$this->options["debug"] = array();
		else
			unset($this->options["debug"]);

		$this->options["results"] = array();
		
		$this->preset = $preset_instance;
		
		$this->allowed = &$this->preset->allowed;

		$this->sql_transform_cbs = &$sql_transform_cbs;

		$this->preset->pre_query($this->options);
		echo "123123123123123";die();
		$this->query();

		$this->preset->post_query($this->options);
		
        return $this->options;
        
    }
    
}
