<?php
require_once "config.php";
Session_start();
?>
<html lang="zh">
<head>
<meta charset="utf-8">
<title>Register</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="Zzm">
</head>
<form class="cmxform" id="register" method="POST" action="index.php">
  <input id="username" name="username" type="text" placeholder="Username" required/>
  <input id="password" type="password" name="password" placeholder="Password" required/>
  <input id="verify" type="verify" name="verify" placeholder="VerifyCode" required/>
  <img title="Refresh" id="refresh"  src='/picture_verify.php' onclick="document.getElementById('refresh').src='/picture_verify.php?t='+Math.random()"/>
  <input type="submit" value="Register">
</form>  
<?php
if(isset($_POST['username'])){
	if(isset($_POST['verify']) and $_POST['verify'] == $_SESSION["verify"]){
		$paneldb=mysql_connect(Mysql_Host, Mysql_Username,Mysql_Password);
		$panelsqlname=Database;
		mysql_select_db($panelsqlname,$paneldb);
		mysql_query("SET NAMES 'utf8'",$paneldb);
		$username = $_POST['username'];
    		$panelsql = "SELECT * FROM Players where ID = '$username'";
		$panelquery=mysql_query($panelsql);
		if (!mysql_num_rows($panelquery)){
		  $name = $_POST['username'];
			$password = $_POST['password'];
			$resql = "insert into Players(ID,Password,LastIP,LastTime) values('$name','$password','0','0')";
			$requery=mysql_query($resql);
			if(!$requery){
				echo("<h4><font color = 'RED'>Unknown Error</font></h4>");
			}else{
				echo("<h4><font color = 'GREEN'>Register Success</font></h4>");
			}
		  
		}else{
			echo("<h4><font color = 'RED'>ID Has Been Registered</font></h4>");
		}
	}
}
?>
