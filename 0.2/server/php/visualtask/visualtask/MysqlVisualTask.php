<?php

namespace VisualTask\VisualTask;


class MysqlVisualTask extends VisualTaskBase {

		protected $mysql_aggregate_funcs = array(
				"count" => array("count(",")"),
				"countd" => array("count(distinct ",")"),
				"sum" => array("sum(",")"),
				"avg" => array("avg(",")"),
				"max" => array("max(",")"),
				"min" => array("min(",")"),
				"gconcat" => array("group_concat(",")"),
				"gconcatd" => array("group_concat(distinct ",")"),
				"year" => array("year(",")"),
				"month" => array("month(",")"),
				"day" => array("day(",")"),
				"date" => array("date(",")"),
				"hour" => array("hour(",")"),
				"yearmonth" => array("date_format(",",'%Y-%m')")
		);


			
		protected function query_mysql(&$queries, &$allowed_entities){

				if ($this->db === null)
						return false;
					
				if (@$this->db->is_connected !== true)
						return false;

				$sql_arr = array();

				foreach ($queries as $query_id => $query){

					if (!isset($query["select"]) || !isset($query["from"]))
							continue;


					// check that table is allowed

					if (!in_array($query["from"], $allowed_entities))
							continue;


					// handle select

					$sql = "select ";

					$select_count = count($query["select"]);
					$is_first = true;
					for ($i=0; $i<$select_count; $i++){

							if (!isset($query["select"][$i]["fieldName"]))
									continue;

							$x = $this->s($query["select"][$i]["fieldName"]);

							$function = "";
							if (isset($query["select"][$i]["function"]))
									$function = $this->s($query["select"][$i]["function"]);
								
							if (strlen($function) > 0 && array_key_exists($function, $this->mysql_aggregate_funcs))
									$x = $this->mysql_aggregate_funcs[$function][0] . $x . $this->mysql_aggregate_funcs[$function][1];

							if (isset($query["select"][$i]["alias"]))
									$x .= " as " . $this->s($query["select"][$i]["alias"]);

							if (!$is_first)
									$sql .= ", " . $x;
							else
									$sql .= $x;

							$is_first = false;

					}


					// handle from

					$sql .= " from " . $this->s($query["from"]);


					// handle where

					// for simplicity $query["where"] is a list of AND criteria

					// automatically add WHERE 1=1

					$sql .= " where 1=1";

					if (isset($query["where"]) && is_array($query["where"])){

							$where = array();

							$where_count = count($query["where"]);
							for ($i=0; $i<$where_count; $i++){
									$item = $query["where"][$i];

									if (!isset($item["fieldName"]) || !isset($item["fieldType"]) || !isset($item["op"]) || !isset($item["val"]))
											continue;

									$field_name = $this->s($item["fieldName"]);
									$field_type = $this->s($item["fieldType"]);
									$op = $item["op"];
									$val = $this->s($item["val"]);	// unquoted, could be comma delimited in case of op == "in"

									switch ($op){
											case "=":
											case ">":
											case ">=":
											case "<":
											case "<=":
											case "!=":

													if ($field_type === "datetime" || $field_type === "string")
															$where[] = $field_name . " " . $op . " '" . $val . "'";
													else
															$where[] = $field_name . " " . $op . " " . $val;

													break;

											case "in":

													if ($field_type === "datetime" || $field_type === "string")
															$where[] = $field_name . " in('" . implode("','", explode(",", $val)) . "')";
													else
															$where[] = $field_name . " in(" . $val . ")";

													break;

											case "startswith":

													if ($field_type === "datetime" || $field_type === "string")
															$where[] = $field_name . " like '" . $val . "%'";

													break;

											case "endswith":

													if ($field_type === "datetime" || $field_type === "string")
															$where[] = $field_name . " like '%" . $val . "'";

													break;

											case "includes":

													if ($field_type === "datetime" || $field_type === "string")
															$where[] = $field_name . " like '%" . $val . "%'";

													break;

									}

									if (isset($item["useDictionaryId"])){
											// TODO
									}

							}

							if (count($where) > 0)
									$sql .= " and " . implode(" and ", $where);

					}


					// handle group by

					if (isset($query["groupBy"])){

							$groupby_count = count($query["groupBy"]);

							if ($groupby_count > 0)
									$sql .= " group by ";

							$is_first = true;
							for ($i=0; $i<$groupby_count; $i++){

									if (!isset($query["groupBy"][$i]["fieldName"]))
											continue;

									$x = $this->s($query["groupBy"][$i]["fieldName"]);

									$function = "";
									if (isset($query["groupBy"][$i]["function"]))
											$function = $this->s($query["groupBy"][$i]["function"]);
										
									if (strlen($function) > 0 && array_key_exists($function, $this->mysql_aggregate_funcs))
											$x = $this->mysql_aggregate_funcs[$function][0] . $x . $this->mysql_aggregate_funcs[$function][1];

									if (!$is_first)
											$sql .= ", " . $x;
									else
											$sql .= $x;

									$is_first = false;
								
							}

					}


					// handle order by

					if (isset($query["orderBy"])){

							$orderby_count = count($query["orderBy"]);

							if ($orderby_count > 0)
									$sql .= " order by ";

							$is_first = true;
							for ($i=0; $i<$orderby_count; $i++){

									if (!isset($query["orderBy"][$i]["fieldName"]))
											continue;

								$x = $this->s($query["orderBy"][$i]["fieldName"]);

								$function = "";
								if (isset($query["orderBy"][$i]["function"]))
										$function = $this->s($query["orderBy"][$i]["function"]);

								if (strlen($function) > 0 && array_key_exists($function, $this->mysql_aggregate_funcs))
										$x = $this->mysql_aggregate_funcs[$function][0] . $x . $this->mysql_aggregate_funcs[$function][1];

								if (isset($query["orderBy"][$i]["dir"]) && $query["orderBy"][$i]["dir"] == "desc")
										$x .= " desc";
								else
										$x .= " asc";

								if (!$is_first)
										$sql .= ", " . $x;
								else
										$sql .= $x;

								$is_first = false;

							}
					}			


					// handle limit

					$sql .= " limit ";

					if (isset($query["limit"])){

							if (isset($query["limit"]["offset"]))
									$limit_offset = intval($query["limit"]["offset"]);
							else
									$limit_offset = "0";

							if (isset($query["limit"]["size"])){

									$limit_size = intval($query["limit"]["size"]);

									if ($limit_size > $this->limit_size_max)
											$limit_size = $this->limit_size_max;

							} else {

									$limit_size = $this->limit_size_default;

							}

					} else {

							$limit_offset = 0;
							$limit_size = $this->limit_size_default;

					}

					$sql .= $limit_offset . ", " . $limit_size;

					if (isset($this->sql_transform_cbs[$query_id]))
							$this->sql_transform_cbs[$query_id]($sql, $query);

					$sql_arr[] = $sql;
								
				}

				if (isset($this->options["debug"]) && is_array($this->options["debug"]))
						$this->options["debug"]["mysql"] = $sql_arr;

				try {
						$results = $this->db->query($sql_arr);
				} catch (Exception $ex){
						return false;
				}
				
				$i = 0;
				foreach ($queries as $query_id => $query){
						$this->options["results"][$query_id] = &$results[$i];
						$i++;
				}

				return true;
					
		}

}