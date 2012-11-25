<?php
  include 'config_survey.php';
  // send the requested article
  $aid = $_GET['aid'];
  $task_id = intval($_GET['task_id']);
  
  switch ($task_id) {
    case 0:
      $t = 'tagdata';
      $p = '/<p>.*?<\/body>/';
      break;
    case 1:
      $t = 'hindu';
      $p = '/.*/';
      break;
    case 2:
      $t = 'tagdata';
      $p = '/<p>.*?<\/body>/';
      break;
    case 3:
      $t = 'hindu';
      $p = '/.*/';
      break;
    default:
      $t = 'tagdata';
      $p = '/<p>.*?<\/body>/';
  }
  $q = "SELECT * FROM `".$t."` WHERE `aid`='$aid'";
  $r = mysql_query($q);
  $r = mysql_fetch_assoc($r);
  if ($r !== false) {
    $afull = $r['afull'];
    if ($task_id == 1) $asumm = $r['asumm'];
    else $asumm = '';
    preg_match($p, $afull, $matches);
    echo json_encode(array(
      $aid, 
      $matches[0],
      $asumm
    ));
  }
?>
