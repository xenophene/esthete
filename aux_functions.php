<?php
  define ('DAY', 60 * 60 * 24);
  define ('BLAND', 'rgba(0,0,0,0.1)');
  define ('BLACK', 'rgb(0,0,0)');
  define ('br', '<br>');
  define ('DESCRIPTION_DELIMITER', '|');
  require_once 'Article.php';
  // is array subarr contained in arr, assuming both are already sorted
  function is_contained($subarr, $arr) {
    if (sizeof($subarr) > sizeof($arr)) return false;
    $i = 0; // index into $arr
    $j = 0; // index into $subarr
    while ($i < sizeof($arr) && $j < sizeof($subarr)) {
      if ($subarr[$j] > $arr[$i]) $i++;
      else if ($subarr[$j] < $arr[$i]) return false;
      else {
        $i++;
        $j++;
      }
    }
    if ($i == sizeof($arr)) return false;
    return true;
  }
  function get_not_contained($map) {
    $not_contained = array();
    $containers = array();
    foreach ($map as $k => $v) {
      $contains_k = array();
      foreach ($map as $k1 => $v1) {
        if ($k1 == $k) continue;
        $contains = is_contained($v, $v1);
        if ($contains) array_push($contains_k, $k1);
      }
      foreach ($contains_k as $k_container) {
        if (isset($containers[$k_container])) {
          array_push($containers[$k_container], $k);
        } else {
          $containers[$k_container] = array($k);
        }
      }
    }
    if (empty($containers)) {
      return $map;
    }
    return $containers;
  }
  function intersect($arr1, $arr2) {  //assumes arr1 & arr2 are SORTED
    $inter = array();
    $i = 0;
    $j = 0;
    while ($i < sizeof($arr1) && $j < sizeof($arr2)) {
      if ($arr1[$i] == $arr2[$j]) {
        array_push($inter, $arr1[$i]);
        $i++;
        $j++;
      }
      else if ($arr1[$i] > $arr2[$j]) $j++;
      else $i++;
    }
    return $inter;
  }
  function date_english($ts) {
    return date('F j Y', $ts);
  }
  function get_time_spent($d) {
    if (isset($d['ts']) and sizeof(explode(':', $d['ts'])) == 2) {
      return explode(':', $d['ts']);
    } else return array('0', '00');
  }
  
  // if these are null we will send the task-specific preset actors
  function get_task_id($d) {
    if (array_key_exists('taskid', $d)) return intval($d['taskid']);
    else return 0;
  }
  function get_filtered_actors($d, $actors) {
    $fa = array_map('strtolower', $actors);
    if (array_key_exists('fa', $d) and !empty($d['fa'])) {
      $fa = array_map('strtolower', $d['fa']);
    }
    return $fa;
  }
  function get_filtered_topics($d, $topics) {
    $ft = array_map('strtolower', $topics);
    if (array_key_exists('ft', $d) and !empty($d['ft'])) {
      $ft = array_map('strtolower', $d['ft']);
    }
    return $ft;
  }
  function get_start_date($d, $default) {
    $fd = $default;
    if (array_key_exists('fd', $d) and $d['fd'] != '') {
      $fd = $d['fd'];
    }
    return $fd;
  }
  function get_end_date($d, $default) {
    $td = $default;
    if (array_key_exists('td', $d) and $d['td'] != '') {
      $td = $d['td'];
    }
    return $td;
  }
  function get_year($d) {
    $m = explode('-', $d);
    return $m[0];
  }
  function get_month($d) {
    $m = explode('-', $d);
    return $m[1] - 1;
  }
  function get_day($d) {
    $d = explode('-', $d);
    return $d[2];
  }
  function store_relevance($fa, $ft, $task_id) {
    $fa = implode('|', $fa);
    $ft = implode('|', $ft);
    $q = "INSERT INTO `taskfilters` (`tid`, `actors`, `topics`) ".
         "VALUES ('$task_id', '$fa', '$ft')";
    mysql_query($q);
  }
  /* runs and sends back the results from the queries. */
  function craft_and_run_query($fa, $ft, $fd, $td, $tablename, $limit) {
    $q = "SELECT * FROM `".$tablename."` WHERE ";
    $q_params = array();
    foreach ($fa as $a) {
      array_push($q_params, "`uactors` LIKE '%$a%'");
    }
    foreach ($ft as $t) {
      array_push($q_params, "`utopics` LIKE '%$t%'");
    }
    array_push($q_params, "`adate` >= '$fd'");
    array_push($q_params, "`adate` <= '$td'");
    $q .= implode(" AND ", $q_params) . " ORDER BY `adate` LIMIT " . $limit;
    return mysql_query($q);
  }
  function enrich($tags, $arr) {
    foreach ($arr as $a) {
      $k = array_search($a, array_map('strtolower', $tags));
      if ($k === false) array_push($tags, ucwords($a));
    }
    return $tags;
  }
  function enrich_tags($tags, $articles, $code) {
    foreach ($articles as $article) {
      if ($code == 1) $arr = $article->get_utopics();
      else $arr = $article->get_uactors();
      $tags = enrich($tags, $arr);
    }
    return $tags;
  }
  function reverse_actor_map($articles) {
    $ra_map = array();
    for ($i = 0; $i < sizeof($articles); $i++) {
      $article = $articles[$i];
      $uactors = $article->get_uactors();
      foreach ($uactors as $uactor) {
        if (array_key_exists($uactor, $ra_map)) array_push($ra_map[$uactor], $i);
        else $ra_map[$uactor] = array($i);
      }
    }
    return $ra_map;
  }
  function reverse_topic_map($articles) {
    $rt_map = array();
    for ($i = 0; $i < sizeof($articles); $i++) {
      $article = $articles[$i];
      $utopics = $article->get_utopics();
      $done = array();
      foreach ($utopics as $utopic) {
        if (isset($done[$utopic])) continue;
        else $done[$utopic] = true;
        if (array_key_exists($utopic, $rt_map)) array_push($rt_map[$utopic], $i);
        else $rt_map[$utopic] = array($i);
      }
    }
    return $rt_map;
  }
  function rand_colorCode($hue) {
    $r = mt_rand(20,180); // generate the red component
    $g = mt_rand(20,180); // generate the green component
    $b = mt_rand(20,180); // generate the blue component
    $rgb = $r.','.$g.','.$b;
    return 'rgb(' . $rgb . ')';
  }
  function assign_colors($arr) {
    $step = 360 / sizeof($arr);
    $colors = array();
    $hue = 0;
    foreach ($arr as $val) {
      $colors[$val] = 'hsl('.$hue.',50%,50%)';
      $hue += $step;
    }
    return $colors;
  }
  
  
  function get_description($article) {
    $topics = $article->get_utopics();
    return '';
  }
  
  function show_legend($t_color_map) {
    echo '<ul>';
    foreach ($t_color_map as $t => $color) {
      echo '<li class="topic-include" style="color:' . $color . '"><span>' . ucwords($t) . '</span></li>';
    }
    echo '</ul>';
  }
  
  // fill the gaps along elems.
  function gap_fill($elems, $tid) {
    $ge = array();
    for ($i = 0; $i < sizeof($elems) - 1; $i++) {
      $e1 = $elems[$i];
      $e2 = $elems[$i + 1];
      
      $s = strtotime($e1['end']);
      $e = strtotime($e2['start']);
      //echo $s .' ' . $e . '<br>';
      if ((($e - $s) / DAY) > 1) {
        $elem = array(
          'trackNum'    =>  $tid,
          'start'       =>  date_english($s),
          'end'         =>  date_english($e),
          'color'       =>  BLAND
        );
        array_push($ge, $elem);
      }
    }
    return array_merge($elems, $ge);
  }
  
  // Apologize for the rather ugly reference to AIDS but i mean
  // $raids: relevant article ids: relevant to the timeperiod of the event
  function get_headlines($articles, $raids) {
    $h = array();
    foreach ($raids as $raid) {
      array_push($h, $articles[$raid]->get_headline() . '#' . $articles[$raid]->get_id());
    }
    return implode('^', $h);
  }
  // timeline_actor: the actor whose events are constructed
  // timeline_topics: topics at this level. only these are shown
  // article_ids: relevant ids of this actor in the entire $articles list
  // tid: track id to be assigned to this actors
  // sd: the starting time stamp for this actor (usually strtotime(Jan 1))
  // ws: the window size to be considered
  function get_actor_events($timeline_actor, $article_ids, $articles, $tid, 
                            $ws, $topic_colors, $ra_map) {
    if (empty($article_ids)) return array();
    $sd = $articles[$article_ids[0]]->get_start_date_ts();
    $elems = array();
    $i = 0;
    $put_title = true;
    $pt = array(); // unique topics of a period
    $pa = array(); // unique article ids of the corresponding period
    while ($i < sizeof($article_ids)) {
      $aid = $article_ids[$i];
      $article = $articles[$aid];
      // does this article belong to current period? No if ap >= 1
      if (($article->days_since($sd) / $ws) <= 1) {  // include topics
        $topics = $article->get_utopics();
        $pt = array_merge($pt, $topics);
        array_push($pa, $aid);
        $i++;
      }
      if (($i == sizeof($article_ids)) || (($article->days_since($sd) / $ws) > 1)) {
        $pt = array_unique($pt);
        $width = ceil($ws / (sizeof($pt) + 1));
        // create an elem for all of these topics
        $j = 0;
        foreach ($pt as $t) {
          $h = $timeline_actor . '|' . $t . '|' . get_headlines($articles, $pa);
          $elem = array(
            'title'       =>  $put_title ? $timeline_actor : '',
            'description' =>  $h,
            'trackNum'    =>  $tid,
            'textColor'   =>  BLACK,
            'start'       =>  date_english($sd + (($width * $j) * DAY)),
            'end'         =>  date_english($sd + (($width * ($j + 1)) * DAY)),
            'color'       =>  $topic_colors[$t]
          );
          array_push($elems, $elem);
          $put_title = false;
          $j++;
        }
        $pt = array();
        $pa = array();
        $sd = $article->get_start_date_ts();
      }
    }
    $elems = gap_fill($elems, $tid);
    return $elems;
  }
  /*
  The key function! The things to be done in this function:
    1. Isolate out the unique topics being talked about, and assign a color
    2. For every actor, color code each article as an event with the topic
       it talked about. This may NOT be unique. Moreover, this actor may appear
       in multiple articles around the same time with different topics. For both
       these situations, do the following:
       -- create a band of X days, and if N topics appear in this band, create
       N events each of width X/N days and color coded. 
       X = 10 here
  */
  function get_timeline_events($timeline_actors, $articles, $ra_map, $t_color_map) {
    // defaults
    $ws = 20;
    
    $tid = 1;
    $elems = array();
    
    foreach ($timeline_actors as $timeline_actor) {
      $article_ids = $ra_map[$timeline_actor];
      $actor_elems = get_actor_events($timeline_actor, $article_ids, $articles, 
                                      $tid, $ws, $t_color_map, $ra_map);
      if (empty($actor_elems)) continue;
      $elems = array_merge($elems, $actor_elems);
      $tid++;
    }
    return $elems;
  }
  
  /*
    The second alternative. We first time segment articles based on windows,
    and for these periods, we find the actor-based article partitions which
    will be called EVENTS! Let us go ahead and visualized!
  */
  function article_periodize($articles) {
    $st = $articles[0]->get_start_date_ts();
    $ws = 20;
    $periods = array(array()); // partitioning of articles id
    $pid = 0;
    $i = 0;
    while ($i < sizeof($articles)) {
      if (($articles[$i]->days_since($st) / $ws) <= 1) {
        array_push($periods[$pid], $i);
        $i++;
      }
      else {
        $pid++;
        $st = $articles[$i]->get_start_date_ts();
        array_push($periods, array());
      }
    }
    foreach ($periods as $p) {
      echo $articles[$p[0]]->get_start_date();
    }
    echo json_encode($periods);
  }
  
  // the keys have to be sorted by the length of the array it indexes
  function get_actors_by_article_count($ra_map) {
    $arr = array();
    $lens = array();
    $sorted_actors = array();
    foreach (array_keys($ra_map) as $key) {
      array_push($arr, array($key, sizeof($ra_map[$key])));
      array_push($lens, sizeof($ra_map[$key]));
    }
    array_multisort($lens, SORT_DESC, $arr);
    foreach ($arr as $p) array_push($sorted_actors, $p[0]);
    return $sorted_actors;
  }
  function get_options($f, $all) {
    foreach ($f as $a) {
      echo '<option value="' . $a . '" selected>' . ucwords($a) . '</option>';
    }
    foreach ($all as $a) {
      $t = strtolower($a);
      if (in_array($t, $f)) continue;
      echo '<option value="' . $t . '">' . ucwords($a) . '</option>';
    }
  }
?>
