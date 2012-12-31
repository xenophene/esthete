<?php
  // central script for all ajax requests from the client
  include 'config_survey.php';
  include 'tasks.php';
  $fid = $_GET['fid'];
  switch ($fid) {
    case 1: send_article(); break;
    case 2: store_relevance(); break;
    case 3: submit_answer(); break;
    case 4: save_feedback(); break;
    default: pass();
  } 
  
  function send_article() {
    $aid = $_GET['aid'];
    $task_id = intval($_GET['task_id']);
    
    $t = get_table_name($task_id);
    $p = get_pattern($task_id);
    
    $q = "SELECT * FROM `".$t."` WHERE `aid`='$aid'";
    $r = mysql_query($q);
    $r = mysql_fetch_assoc($r);
    if ($r !== false) {
      $afull = $r['afull'];
      if ($task_id == 0 || $task_id == 2) $asumm = '';
      else $asumm = $r['asumm'];
      
      preg_match($p, $afull, $matches);
      echo json_encode(array(
        $aid,
        $matches[0],
        $asumm
      ));
    }
  }
  function store_relevance() {
    $aid = $_GET['aid'];
    $tid = $_GET['task_id'];
    $rid = $_GET['rid'];
    $q = "SELECT * FROM `relevance` WHERE `tid`='$tid' AND `aid`='$aid' AND `rid`='$rid'";
    $r = mysql_query($q);
    if (mysql_num_rows($r) == 0) {
      $q = "INSERT INTO `relevance` (`tid`, `aid`, `rid`) VALUES ('$tid', '$aid', '$rid')";
      mysql_query($q);
    }
  }
  
  function submit_answer() {
    $time = $_GET['time'];
    $tid = $_GET['task_id'];
    $atext = $_GET['answerText'];
    $answers = implode(',', $_GET['answers']);
    $q = "INSERT INTO `answers` (`tid`, `answertext`, `answerchoices`, `time`) ".
         "VALUES ('$tid', '$atext', '$answers', '$time')";
    mysql_query($q);
  }
  
  function save_feedback() {
    $e = $_GET['email'];
    $c = $_GET['comment'];
    $query = "INSERT INTO `feedback` (`email`, `comment`) VALUES ('$e', '$c')";
    mysql_query($query);
  }
  
  function pass() {}
?>
