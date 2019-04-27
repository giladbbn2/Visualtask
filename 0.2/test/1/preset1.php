<?php

class preset1 extends \VisualTask\VisualTask\VisualtaskPresetBase {

	public $allowed = array(
		"res1" => array("test.users", "test.tbl1")
	);

	public function pre_query(&$options){
		// check user session!
	}

	public function post_query(&$options){
		// add calculations into data
	}

}