<?php 

namespace VisualTask;


define("VT_DIR", __DIR__ . DIRECTORY_SEPARATOR . "visualtask");

function autoload($class_fqn){

    $parts = explode("\\", $class_fqn);

    // first part doesn't have base.php
    @array_shift($parts);

    // last part is the class name
    @array_pop($parts);

    $base_dir = VT_DIR;

    $parts_count = count($parts);

    for ($i=0; $i<$parts_count; $i++){

        $part = strtolower($parts[$i]);

        $base_dir .= DS . $part;

        @include_once $base_dir . DS . "base.php";

    }

}