<?php

namespace VisualTask\DB;


interface DB {

    public function connect($db_conn_name, $is_force_new = false);

}

interface Queryable {

    public function get_is_connected();

    public function set_fetch_assoc($b);

    public function query($sql, $assocKey = array());

    public function q($sql, $assocKey = array());

    public function close();

    public function get_error_num();

    public function get_error_desc();

    public function get_last_insert_id();

    public function get_last_affected_rows();

}