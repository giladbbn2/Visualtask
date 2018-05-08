<?php

// this file returns ajax to consumers requesting a visual task

error_reporting( E_ALL );
ini_set('display_errors', 1);

define("VT_SERVER_ROOT", __DIR__);
define("VT_SERVER_LIBS", VT_SERVER_ROOT . DIRECTORY_SEPARATOR . "libs");
define("VT_SERVER_VTG", VT_SERVER_ROOT . DIRECTORY_SEPARATOR . "vt");

$options = array();

if (isset($_POST["options"])){


	// load mysql db interface (mysqli) - currently visualtask supports only mysql query syntax

	include_once VT_SERVER_LIBS . DIRECTORY_SEPARATOR . "mysql.php";

	$mysql = new DB(array(
		"conn1" => array(
			"host" => "localhost",
			"user" => "db_user1",
			"pass" => "123456abcdeFGH",
			"db_name" => "test",
			"port" => 3306
		)
	));

	$db = $mysql->connect("conn1");			// returns Queryable
	$db->is_fetch_assoc = false;			// instead of returning an associative array queries return the old-fashioned plain arrays


	// init visualtask

	include_once VT_SERVER_LIBS . DIRECTORY_SEPARATOR . "visualtask.php";
	include_once VT_SERVER_VTG . DIRECTORY_SEPARATOR . "vtconfig.php";

	$vtg = new Visualtask();
	$vtg->config = new VTConfig();
	$vtg->mysql_db = $db;
	$vtg->limit_size_default = 10;
	$vtg->limit_size_max = 100;
	

	if (isset($_GET["preset"]) && $_GET["preset"] === "preset1"){

		// use preset1

		include_once VT_SERVER_VTG . DIRECTORY_SEPARATOR . "preset1.php";

		$options = $vtg->preset(new preset1(), $_POST["options"]);

	} else {

		// user render()



	}
	

}

header('Content-type: application/json');
echo json_encode($options);
die();