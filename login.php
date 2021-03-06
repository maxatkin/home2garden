<?php
if(!defined("_LOGIN_H_")){
define("_LOGIN_H_", 1);

include "global.php";
include "file.php";
include "securedb.php";
include "sidemenu.php";

function MakeSessionID(){
  $sessionid = "";
  for($i = 0; $i <= 16; $i++){
    $newchar = chr(rand(48,90));
    if($newchar == "'") continue;
    $check = ParseBadChars($newchar);
    if($check["status"] == "Error") continue;
    $sessionid = $sessionid . $newchar;
  }
  return $sessionid;
}

function AddUser($username, $password){
  $userinfo = ReadDB("users", array("id", "name"), array("name = '%s'", $username)); 
  if($userinfo["status"] == "Value"){
    if(count($userinfo["value"]) > 0) return packReturn(0, "Error", "User already exists");
  }
  $cryptpass = crypt($password);
  $ret = InsertDB("users", array("name" => $username, "groupid" => 3, "password" => $cryptpass, "sessionid" => ""));
  return $ret;
}

function logout(){
  if(setcookie("userid", False) == False) return packReturn(False, "Error");
  if(setcookie("sessionid", False) == False) return packReturn(False, "Error");
  $GLOBALS['userid'] = 1;
  $GLOBALS['groupid'] = 1;
  return packReturn(True, "Value");
}

function login($username, $password){
  $userinfo = ReadDB("users", array("id", "name", "groupid", "password"), array("name = '%s'", $username));
  //echo "here:";
  //print_r($userinfo);

  if($userinfo["status"] == "Error") return $userinfo;
  $userinfo = $userinfo["value"][0];
  $loggedin = FALSE;
  if($userinfo["password"] == crypt($password, $userinfo["password"])){
    setcookie("userid", $userinfo["id"]);
    $new_sessionid = MakeSessionID();
    setcookie("sessionid", $new_sessionid);
    //echo "<pre>" . $new_sessionid . "</pre>";
    UpdateDB("users", array("sessionid" => $new_sessionid), array("id = %d", $userinfo["id"]));
    $GLOBALS['userid'] = $userinfo["id"];
    $GLOBALS['groupid'] = $userinfo["groupid"];
    $loggedin = TRUE;
  }
  return packReturn($loggedin, "No value");
}

$setnobody = FALSE;
//echo "blah<p/>";
$temp_userid = GetCookie("userid");
$temp_sessionid = GetCookie("sessionid");
//print_r($_COOKIE);
//print_r($_COOKIE["sessionid"]);
//print_r($temp_sessionid);
//echo "blah2<p/>";
if($temp_userid["status"] == "No value") $setnobody = TRUE;
else $temp_userid = (integer)$temp_userid["value"];

if($temp_sessionid["status"] == "No value") $setnobody = TRUE;
else $temp_sessionid = $temp_sessionid["value"];
if($setnobody) $temp_userid = 1;

//print_r($tmp_sessionid);
$userinfo = ReadDB("users", array("id", "groupid", "sessionid"), array("id = %d", $temp_userid));
//print_r($userinfo);
if($userinfo["status"] == "Error"){
  logout();
  $GLOBALS['userid'] = 1;
  $GLOBALS['groupid'] = 1;
}
else $userinfo = $userinfo["value"][0];

//print_r($userinfo);
//echo "blah <br/>";
//print_r($temp_sessionid);

if($userinfo["sessionid"] != $temp_sessionid){
  logout();
  $GLOBALS['userid'] = 1;
  $GLOBALS['groupid'] = 1;
}
else{
  $GLOBALS['userid'] = $userinfo["id"];
  $GLOBALS['groupid'] = $userinfo["groupid"];
}
//echo $GLOBALS['userid'] . "and" . $GLOBALS['groupid'];
}

?>
