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
    case 5: send_topics(); break;
    case 6: send_actors(); break;
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
      $adate = $r['adate'];
      preg_match($p, $afull, $matches);
      echo json_encode(array(
        $aid,
        $matches[0],
        $asumm,
        $adate
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
  
  function send_topics() {
    $aids = $_GET['aids'];
    $tname = get_table_name($_GET['tid']);
    $topics = array();
    foreach ($aids as $aid) {
      $query = "SELECT * FROM `".$tname."` WHERE `aid`='$aid'";
      if ($r = mysql_query($query)) {
        $row = mysql_fetch_assoc($r);
        $topics[$aid] = array($row['utopics'], $row['aid'], $row['adate']);
      }
    }
    echo json_encode($topics);
  }
  
  function send_actors() {
    $aids = $_GET['aids'];
    $tname = get_table_name($_GET['tid']);
    $actors = array();
    foreach ($aids as $aid) {
      $query = "SELECT * FROM `".$tname."` WHERE `aid`='$aid'";
      if ($r = mysql_query($query)) {
        $row = mysql_fetch_assoc($r);
        $patt = '/u00\d{1}\w{1}/';
        $uactors = preg_replace_callback($patt, "add_slash", $row['uactors']);
        $actors[$aid] = array($uactors, $row['aid'], $row['adate']);
      }
    }
    echo json_encode($actors);
  }
  function add_slash($matches) {
    $m = array();
    $m['u002e'] = '.';
    $m['u002f'] = '/';
    $m['u002d'] = '-';
    $m['u002c'] = ',';
    $m['u0029'] = ')';
    $m['u0028'] = '(';
    return $m[$matches[0]];
  }
  
  function pass() {}
?>
