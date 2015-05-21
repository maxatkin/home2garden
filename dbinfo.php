<?
$username="hom002";
$password="2at056";
$database="hom002";

mysql_connect('217.204.9.142', 'hom002', '2at056');
@mysql_select_db($database) or die( "Unable to select database");
$query="SELECT * FROM indexpage";
$result=mysql_query($query);

$num=mysql_numrows($result);

mysql_close();

echo "<b><center>Database Output</center></b><br><br>";

$i=0;
while ($i < $num) {

$first=mysql_result($result,$i,"id");
$last=mysql_result($result,$i,"name");
$phone=mysql_result($result,$i,"description");
$mobile=mysql_result($result,$i,"displayorder");
$fax=mysql_result($result,$i,"trade");


echo "<b>$first $last</b><br>Phone: $phone<br>Mobile: $mobile<br>Fax: $fax<br>";

$i++;
}

?>