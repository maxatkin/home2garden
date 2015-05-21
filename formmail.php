<?
$name=$_POST['name']; 
$telephone=$_POST['telephone']; 
$email=$_POST['email']; 
$comments=$_POST['comments']; 
//assign a few headers
$headers="from:info@home2garden.co.uk;l\r\n";
$headers="Content-Type: text/html;\r\n charset=\"iso-8859-1\"\r\n";

//create body of message

$body="
<html>
<head><title>Home2Garden.co.uk. Contact us Form Submission</title></head>
<body>
Name: $name<br />
Telephone: $telephone<br />
Email: $email<br />
Comments: $comments<br />


</body>
</html>";

//send the message
   mail("entry@arcavitsystems.com", "Home2Garden Contact form submission", $body, $headers,"-fmick.mckenna@arcavitsystems.com");
   //mail("sales@home2garden.co.uk", "Home2Garden Contact form submission", $body, $headers,"-fadmin@domain.com");
?>
<? header( "Location: http://www.home2garden.co.uk/index.php?thankyou" ); ?>
