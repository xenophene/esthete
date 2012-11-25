<?php
  include 'survey_config.php';
  $aid = $_GET['aid'];
  $tid = $_GET['task_id'];
  $q = "SELECT * FROM `relevance` WHERE `tid`='$tid' AND `aid`='$aid'";
  $r = mysql_query($q);
  if (mysql_num_rows($r) == 0) {
    $q = "INSERT INTO `relevance` (`tid`, `aid`) VALUES ('$tid', '$aid')";
    mysql_query($q);
  }
?>
