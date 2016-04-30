<?php
	setcookie("user","",time()-60*60*24*1);
  	setcookie("pass","",time()-60*60*24*1);
  	header("location:./login.php");
	exit();
?>
