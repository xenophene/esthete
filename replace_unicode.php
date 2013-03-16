<?php
/**
 * Will loop through the various DB tables, and replace the unicode patterns
 * in uactors, to the corresponding symbol and write it back
 */
include 'config_survey.php';
include 'aux_functions.php';

$table = 'badminton';
$q = "SELECT * FROM `$table`";
$r = mysql_query($q);
for ($i = 0; $i < mysql_num_rows($r); $i++) {
  $row = mysql_fetch_assoc($r);
  $aid = $row['aid'];
  $patt = '/u00\d{1}\w{1}/';
  $uactors = preg_replace_callback($patt, "add_slash", $row['uactors']);
  if ($uactors !== $row['uactors']) {
    $q = "UPDATE `$table` SET `uactors`='$uactors' WHERE `aid`='$aid'";
    mysql_query($q);
  }
}
?>