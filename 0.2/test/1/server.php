<?php

error_reporting( E_ALL );
ini_set('display_errors', 1);

include dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "autoload.php";

autoload("VisualTask\\DB\\Mysql");
autoload("VisualTask\\VisualTask\\Mysql");

echo "123";