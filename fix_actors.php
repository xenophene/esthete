<?php
  include 'config_survey.php';
  include 'Article.php';
  $table = 'badminton';
  $q = "SELECT * FROM `".$table."`";
  $r = mysql_query($q);
  $actors = array();
  $articles = array();
  for ($i = 0; $i < mysql_num_rows($r); $i++) {
    $row = mysql_fetch_assoc($r);
    $article = new Article($row);
    array_push($articles, $article);
    $actors = array_merge($actors, $article->get_uactors());
  }
  $actors = array_values(array_unique($actors));
  $correct_mapping = array();
  foreach($actors as $actor) {
    if (substr($actor, -1) == 's') {
      $k = array_search(substr($actor, 0, -1), $actors);
      if ($k !== false) {
        $correct_mapping[$actor] = true;
      }
    }
  }
  for ($i = 0; $i < sizeof($articles); $i++) {
    $article = $articles[$i];
    $actors = $article->get_uactors();
    $new_actors = array();
    foreach ($actors as $actor) {
      if (isset($correct_mapping[$actor])) {
        array_push($new_actors, substr($actor, 0, -1));
      } else {
        array_push($new_actors, $actor);
      }
    }
    $article->set_new_uactors($new_actors);
    $actors = $article->get_uactors_string();
    $aid = $article->get_id();
    $q = "UPDATE `".$table."` SET `uactors`='$actors' WHERE `aid`='$aid'";
    mysql_query($q);
  }
?>
