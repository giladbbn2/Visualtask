<?php


class VTConfig extends VisualtaskConfigBase {
	
	public $resource_types = array(

		// exposed resource type name => real resource type in visualtask

		"res1" => "mysql"
	);

	public $tpls = array(

		"tpl1" => VT_SERVER_VTG . DIRECTORY_SEPARATOR . "tpl1.php"

	);

	public $entities = array(

		"mysql" => array(

			"test.users" => array(

				array(

					"fieldName" => "username",
					"header" => "Username",
					"fieldType" => "string" 

				),

				array (

					"fieldName" => "id",
					"header" => "User ID",
					"fieldType" => "num" 
				),

				array (

					"fieldName" => "insert_datetime",
					"header" => "Insert Datetime",
					"fieldType" => "datetime" 
				)

			)

		)

	);

}