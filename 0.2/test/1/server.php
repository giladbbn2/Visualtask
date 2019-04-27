<?php

error_reporting( E_ALL );
ini_set('display_errors', 1);

include dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "autoload.php";

autoload("\\VisualTask\\DB\\MysqlDB");
autoload("\\VisualTask\\VisualTask\\MysqlVisualTask");

$mysqldb = new \VisualTask\DB\MysqlDB(array(
	"conn1" => array(
		"host" => "localhost",
		"user" => "db_user1",
		"pass" => "123456abcdeFGH",
		"db_name" => "test",
		"port" => 3306
	)
));

$db = $mysqldb->connect("conn1");
$db->set_fetch_assoc(false);

echo "123";