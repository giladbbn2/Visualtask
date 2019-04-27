<?php

error_reporting( E_ALL );
ini_set('display_errors', 1);


if (isset($_POST["options"])){

    // include the vt_autoload_class function to load visualtask files properly
    include dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "autoload.php";

    // this makes sure all necessary files are loaded properly
    vt_autoload_class("\\VisualTask\\DB\\MysqlDB");
    vt_autoload_class("\\VisualTask\\VisualTask\\MysqlVisualTask");

    $mysqldb = new \VisualTask\DB\MysqlDB(array(
        "conn1" => array(
            "host" => "localhost",
            "user" => "root",
            "pass" => "004f0fec7eb37f3a0d735464dc6c0e0d",
            "db_name" => "test",
            "port" => 3306
        )
    ));

    $db = $mysqldb->connect("conn1");
    $db->set_fetch_assoc(false);

    // init visualtask
    $vt = new \VisualTask\VisualTask\MysqlVisualTask();
    $vt->resource_types = array(
        // exposed resource type name => real resource type in visualtask
        "res1" => "mysql"
    );
    $vt->mysql_db = $db;
    $vt->limit_size_default = 10;
    $vt->limit_size_max = 100;

    // this file returns ajax to consumers requesting visualtask

    // load preset
    include "preset1.php";

    $options = $vt->preset(new preset1(), $_POST["options"]);

    header('Content-type: application/json');
    echo json_encode($options);
    die();

}