<?php
  /* tagged database has the manually tagged articles */
  $mysql_hostname = "localhost";
  $mysql_user = "root";
  $mysql_password = "";
  $mysql_database = "tagged";
  $bd = mysql_connect($mysql_hostname, $mysql_user, $mysql_password) or die("Could not connect database");
  mysql_select_db($mysql_database, $bd) or die("Could not select database");
?>
