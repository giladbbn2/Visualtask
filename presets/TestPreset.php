<?php

class TestPreset extends VisualtaskPresetBase {

	public $allowed = array(
		"res1" => array("main.users", "main.tbl1")
	);

	public function pre_query(&$options){
		// check user session!
	}

	public function post_query(&$options){
		// add calculations into data
	}

}