<?php

// this file returns ajax to consumers requesting a visual task

error_reporting( E_ALL );
ini_set('display_errors', 1);

define("VT_SERVER_ROOT", __DIR__);
define("VT_SERVER_LIBS", VT_SERVER_ROOT . DIRECTORY_SEPARATOR . "libs");
define("VT_SERVER_VTG", VT_SERVER_ROOT . DIRECTORY_SEPARATOR . "vt");


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


$options = array();


if (isset($_POST["options"]) && isset($_GET["preset"]) && $_GET["preset"] === "preset1"){

	// use preset1

	include_once VT_SERVER_VTG . DIRECTORY_SEPARATOR . "preset1.php";

	$options = $vtg->preset(new preset1(), $_POST["options"]);

	header('Content-type: application/json');
	echo json_encode($options);
	die();

}


// user render()

// this task uses preset1 for interaction with server

$task = '{"taskId":"task1","tplId":"tpl1","queries":{"q1":{"type":"res1","select":[{"function":"date","fieldName":"insert_datetime","alias":"t"},{"function":"count","fieldName":"id"}],"from":"test.users","where":[{"fieldName":"id","fieldType":"num","op":">","val":0}],"groupBy":[{"function":"date","fieldName":"insert_datetime"}],"orderBy":[{"function":"date","fieldName":"insert_datetime","dir":"desc"}],"limit":{"offset":0,"size":10}}},"graphs":{"g1":{"HTMLElementId":"graph1","lib":"visualtaskgrid","queryId":"q1","fields":[{"fieldName":"t","fieldType":"datetime"},{"fieldName":"id","fieldType":"num","isSortable":false}],"header":{"values":["Insert Datetime","User ID"]},"isShowFiltering":true,"isShowAdvancedBox":true,"isShowFieldSelector":true,"isShowPager":true},"g2":{"HTMLElementId":"graph2","lib":"plotly","queryId":"q1","fields":[{"fieldName":"t"},{"fieldName":"id"}],"config":{"data":[{"type":"scatter","xFieldId":0,"yFieldId":1,"line":{"color":"red"}}],"layout":{"title":"number of inserted users each day","xaxis":{"type":"date"}}}}}}';

$config = array(
	"task" => json_decode($task, true),
	"entities" => array(
		"mysql" => array("test.users")
	),
	"endpoint" => "server.php?preset=preset1",
	"errorHTMLElement" => "vtgerror",
	"isAutoQuery" => true,
	"debug" => true
);

$vtg->render("tpl1", $config);