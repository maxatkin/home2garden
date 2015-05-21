<?php

if(!defined("_SECUREDB_H_")){
define("_SECUREDB_H_", 1);

include "global.php";
include "file.php";

function ParseBadChars($str){
  $find_these = array(";",",","\"", "\\");
  foreach($find_these as $badchar){
   // if(strpos($str, $badchar) !== FALSE) return packReturn(0, "Error", "Bad characters found in db request: " . $badchar);
  }
  return packReturn(0, "No Value");
}

function InsertDB($table, $fields){
  $field_values = "";
  $field_names  = "";
  foreach($fields as $field => $field_value){
    $ret = ParseBadChars($field);
    if($ret["status"] == "Error") return $ret;
    $ret = ParseBadChars($field_value);
    if($ret["status"] == "Error") return $ret;
    if(strlen($field_names) > 0) $field_names = $field_names . ",";
    if(strlen($field_values) > 0) $field_values = $field_values . ",";

    $field_names = $field_names . $field;
    if(is_numeric($field_value)) $field_values = $field_values . $field_value ;
    else if(is_string($field_value)) $field_values = $field_values . "'" . $field_value . "'";
  }

  $ret = ParseBadChars($table);
  if($ret["status"] == "Error") return $ret;
  $dbquery = "INSERT INTO " . $table . " (" . $field_names . ")" . " VALUES (" . $field_values . ");";

  $retquery = mysql_query($dbquery);
  if($retquery) return packReturn(mysql_insert_id(), "Value");
  if(!$retquery) return packReturn(0, "Error", "Update failed");
}

function DeleteDB($table, $condition = ""){
  if($condition != ""){
    $condition_params = array_slice($condition, 1);
    $condition = $condition[0];
    $ret = ParseBadChars($condition);
    if($ret["status"] == "Error") return $ret;
    $condition = vsprintf(" WHERE " . $condition, $condition_params);
  }
  $ret = ParseBadChars($table);
  if($ret["status"] == "Error") return $ret;
  $dbquery = "DELETE FROM " . $table . $condition;
  $retquery = mysql_query($dbquery);
  if($retquery) return packReturn(0, "No value");
  if(!$retquery) return packReturn(0, "Error", "Update failed");
}

function UpdateDB($table, $fields, $condition = ""){
  if($condition != ""){
    $condition_params = array_slice($condition, 1);
    $condition = $condition[0];
    $ret = ParseBadChars($condition);
    if($ret["status"] == "Error") return $ret;
    $condition = vsprintf(" WHERE " . $condition, $condition_params);
  }

  $field_to_update = "";
  foreach($fields as $field => $field_value){
    $ret = ParseBadChars($field);
    if($ret["status"] == "Error") return $ret;
    $ret = ParseBadChars($field_value);
    if($ret["status"] == "Error") return $ret;
    if(strlen($fields_to_update) > 0) $fields_to_update = $fields_to_update . ",";

    if(is_numeric($field_value)) $fields_to_update = $fields_to_update . $field . "=" . $field_value ;
    else if(is_string($field_value)) $fields_to_update = $fields_to_update . $field . "='" . $field_value . "'";
  }

  $ret = ParseBadChars($table);
  if($ret["status"] == "Error") return $ret;
  $dbquery = "UPDATE " . $table. " SET " . $fields_to_update . $condition . ";";

  $retquery = mysql_query($dbquery);
  if($retquery) return packReturn(0, "No value");
  if(!$retquery) return packReturn(0, "Error", "Update failed");
}

function BuildSelect($table, $fields, $condition = "", $sort_method = "", $addcolon = true){
  if($condition != ""){
    $condition_params = array_slice($condition, 1);
    $condition = $condition[0];
    $ret = ParseBadChars($condition);
    if($ret["status"] == "Error") return $ret;
    $condition = vsprintf(" WHERE " . $condition, $condition_params);
  }

  if($sort_method != ""){
    $sort_method_params = array_slice($sort_method, 1);
    $sort_method = $sort_method[0];
    $ret = ParseBadChars($sort_method);
    if($ret["status"] == "Error") return $ret;
    $sort_method = vsprintf(" ORDER BY " . $sort_method, $sort_method_params);
  }
  
  $fields_to_read = "";

  foreach($fields as $field){
    $ret = ParseBadChars($field);
    if($ret["status"] == "Error") return $ret;
    if(strlen($fields_to_read) > 0) $fields_to_read = $fields_to_read . ",";
    $fields_to_read = $fields_to_read . $field;
  }

  //$ret = ParseBadChars($table);
  //if($ret["status"] == "Error") return $ret;
  $dbquery = "SELECT " . $fields_to_read . " FROM " . $table . $condition . $sort_method;
  if($addcolon == true) $dbquery = $dbquery . ";";
  return packReturn($dbquery, "Value");
}

function ReadDB($table, $fields, $condition = "", $sort_method = ""){
  $dbquery = BuildSelect($table, $fields, $condition, $sort_method);
  if($dbquery["status"] == "Error") return $dbquery;
  $dbquery = $dbquery["value"];

  $retquery = mysql_query($dbquery);
  $results = array();
  while($row = mysql_fetch_array($retquery, MYSQL_ASSOC)){
    array_push($results, $row);
  }
  return packReturn($results, "Value");

}

function UnionReadDB($queries){
  $finalquery = "";
  $joiner = "";
  foreach($queries as $query){
    $dbquery = BuildSelect($query["table"], $query["fields"], $query["condition"], $query["sort_method"], false);
    if($dbquery["status"] == "Error") return $dbquery;
    $dbquery = $dbquery["value"];
    $finalquery = $finalquery . $joiner . " (" . $dbquery . ") ";
    $joiner = "UNION";
  }
  echo $finalquery;
  $retquery = mysql_query($finalquery . ";");
  $results = array();
  while($row = mysql_fetch_array($retquery, MYSQL_ASSOC)){
    array_push($results, $row);
  }
  return packReturn($results, "Value");
}

function MakeOptionList($table, $field, $value = "no value", $where = "", $order = "", $selectcondition = ""){
  $option_list = "";
  $options = ReadDB($table, $field, $where, $order);
  if($options["status"] == "Error") return $options;
  $options = $options["value"];
  foreach($options as $option){
    $selected = "";
    if($selectcondition != ""){
      if($option[$selectcondition["fieldtomatch"]] == $selectcondition["fieldvalue"]) $selected = " selected = 'true' ";
    }

    if($value == "no value") $option_list = $option_list . "<option" . $selected . ">" . $option[$field[0]] . "</option>\n";
    else $option_list = $option_list . "<option " . $selected . "value = '". $option[$field[1]] ."'>" . $option[$field[0]] . "</option>\n";
  }
  return packReturn($option_list, "Value");
}
//old
//$link = mysql_connect('217.204.9.142', 'hom002', '2at056');
//$ret = mysql_select_db('hom002');
//current 09_2008
//$link = mysql_connect('217.33.249.42', 'hom002', '2at056');
//$ret = mysql_select_db('hom002');
//ASL
//$link = mysql_connect('79.99.43.20', 'arcavitco1', 's68377');
//$ret = mysql_select_db('arcavitco1');
//$link = mysql_connect('79.99.43.20', 'arcavitco2', 's68377');
//$ret = mysql_select_db('arcavitco2');
$link = mysql_connect('217.174.249.239', 'home2garde1', 'ux69vqr7');
$ret = mysql_select_db('home2garde1');
}

?>
