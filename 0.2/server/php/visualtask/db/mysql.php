<?php

namespace VisualTask\DB\Mysql;


class DB implements \VisualTask\DB\VisualTaskDB {

	private $qs = array();
	public $db_connections = array();



	function __construct($db_connections){
		$this->db_connections = $db_connections;
	}

	public function connect($db_conn_name, $is_force_new = false){
		
		if ($is_force_new !== true && array_key_exists($db_conn_name, $this->qs) && $this->qs[$db_conn_name]->is_connected)
            return $this->qs[$db_conn_name];
            
		if (!is_array($this->db_connections) || !array_key_exists($db_conn_name, $this->db_connections))
            throw new Exception("db conn not found");
            
        $conn = $this->db_connections[$db_conn_name];
        
        $q = new Queryable($conn["host"], $conn["user"], $conn["pass"], $conn["db_name"], $conn["port"]);
        
        $this->qs[$db_conn_name] = $q;
        
        return $q;
        
    }
    
}

class Queryable implements \VisualTask\DB\VisualTaskQueryable {

    private $conn;
	private $is_fetch_assoc = true;
	private $is_connected = false;



    function __construct($host, $user, $pass, $db_name, $port = 3306) {

		$conn = @mysqli_connect($host, $user, $pass, $db_name, $port);

		if (!$conn){
			$this->is_connected = false;
		} else {
			$this->is_connected = true;
			@mysqli_set_charset($conn, "utf8");
			$this->conn = $conn;
        }
        
    }

    public function get_is_connected(){
        return $this->is_connected;
    }

    public function set_fetch_assoc($b){

        if ($b !== true)
            $this->is_fetch_assoc = false;
        else
            $this->is_fetch_assoc = true;

    }

    public function query($sql, $assocKey = array()){

		$return_type = ($this->is_fetch_assoc ? MYSQLI_ASSOC : MYSQLI_NUM);

        $conn = $this->conn;

        $isArray = false;
        if (is_array($sql)){
            $sql = implode(";", $sql);
            $isArray = true;
        }

        $out = array();

        if (mysqli_multi_query($conn, $sql)){
            $sqlId = 0;
            do {

                $list = array();
                if ($result = mysqli_store_result($conn)){

                    // select

					if (isset($assocKey[$sqlId]) && $assocKey[$sqlId] != "")
						while ($row = mysqli_fetch_array($result, $return_type))
							$list[$row[$assocKey[$sqlId]]] = $row;
					else
						while ($row = mysqli_fetch_array($result, $return_type))
							$list[] = $row;

                    $out[] = $list;
                    //echo "Selected rows = ".mysqli_num_rows($result)."<br><br>";
                    mysqli_free_result($result);

                }
                $sqlId++;

            } while(mysqli_more_results($conn) && mysqli_next_result($conn));
        } else
            throw new Exception(mysqli_error($conn), mysqli_errno($conn));

        if (!$isArray)
            if (is_array($out))
                if (isset($out[0]))
                    $out = $out[0];

        return $out;
    }

	public function q($sql, $assocKey = array()){
		return $this->query($sql, $assocKey);
	}

	public function close(){

		@mysqli_close($this->conn);
        $this->is_connected = false;
        
	}

    public function get_error_num(){

        if (!$this->is_connected)
            return mysqli_connect_errno();

        return mysqli_errno($this->conn);

    }

    public function get_error_desc(){

        if (!$this->is_connected)
            return mysqli_connect_error();

        return mysqli_error($this->conn);

    }

    public function get_last_insert_id(){
        return mysqli_insert_id($this->conn);
    }

    public function get_last_affected_rows(){
        return mysqli_affected_rows($this->conn);
    }

}