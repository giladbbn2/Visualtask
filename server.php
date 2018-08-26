<?php

error_reporting( E_ALL );
ini_set('display_errors', 1);

define("VT_SERVER_ROOT", __DIR__);
define("VT_SERVER_LIBS", VT_SERVER_ROOT . DIRECTORY_SEPARATOR . "libs");
define("VT_SERVER_VT", VT_SERVER_ROOT . DIRECTORY_SEPARATOR . "vt");


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

$vt = new Visualtask();
$vt->resource_types = array(
	// exposed resource type name => real resource type in visualtask
	"res1" => "mysql"
);
$vt->mysql_db = $db;
$vt->limit_size_default = 10;
$vt->limit_size_max = 100;


// this file returns ajax to consumers requesting visualtask


// use preset1

include_once VT_SERVER_VT . DIRECTORY_SEPARATOR . "preset1.php";

$sql_transform_cbs = array("q1" => function(&$sql, &$query){

	// add joins and more conditions dynamically (mysql)
		
	//$sql = str_replace("where 1=1", "join test.tbl2 on test.tbl1.id = test.tbl2.id where 1=1 and test.tbl2.name = 'a'", $sql);

});

$options = $vt->preset(new preset1(), $_POST["options"]);

header('Content-type: application/json');
echo json_encode($options);
die();
