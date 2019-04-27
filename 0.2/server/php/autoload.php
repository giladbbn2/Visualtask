<?php 


define("VT_DIR", __DIR__ . DIRECTORY_SEPARATOR . "visualtask");

function vt_autoload_class($class_fqn){

    $parts = explode("\\", $class_fqn);

    if (isset($parts[0]) && $parts[0] === "")   // first slash to global scope
        @array_shift($parts);

    // first part doesn't have base.php
    @array_shift($parts);

    // last part is the class name
    $classname = @array_pop($parts);

    $base_dir = VT_DIR;

    $parts_count = count($parts);

    for ($i=0; $i<$parts_count; $i++){

        $part = strtolower($parts[$i]);

        $base_dir .= DIRECTORY_SEPARATOR . $part;

        @include_once $base_dir . DIRECTORY_SEPARATOR . "base.php";

    }

    @include_once $base_dir . DIRECTORY_SEPARATOR . $classname . ".php";

}