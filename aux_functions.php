<?php
  define ('DAY', 60 * 60 * 24);
  define ('BLAND', 'rgba(0,0,0,0.1)');
  define ('BLACK', 'rgb(0,0,0)');
  define ('DULL', 'rgba(0,0,0,0.5)');
  define ('PROMINENT', 'rgb(88, 160, 220)');
  define ('br', '<br>');
  define ('DESCRIPTION_DELIMITER', '|');
  define ('DELIMITER', '^');
  define ('ARTICLE_ID', '#');
  require_once 'Article.php';
  function add_slash($matches) {
    $m = array();
    $m['u002e'] = '.';
    $m['u002f'] = '/';
    $m['u002d'] = '-';
    $m['u002c'] = ',';
    $m['u0029'] = ')';
    $m['u0028'] = '(';
    $m['u0027'] = "'";
    return $m[$matches[0]];
  }
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
  function get_containers($map) {
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
    // $containers is the array of all topics which contain some other topics
    // we can level-ise this array in terms of level 1, level 2
    if (empty($containers)) {
      return $map;
    }
    return $containers;
  }
  function get_first_level($containers) {
    $count_array = array(); // array of counts of all topics
    foreach ($containers as $container => $contained_topics) {
      if (isset($count_array[$container])) $count_array[$container]++;
      else $count_array[$container] = 1;
      foreach ($contained_topics as $contained_topic) {
        if (isset($count_array[$contained_topic])) $count_array[$contained_topic]++;
        else $count_array[$contained_topic] = 1;  
      }
    }
    $level1 = array();
    foreach ($count_array as $k => $v) {
      if ($v == 1 and isset($containers[$k])) $level1[$k] = array();
    }
    return $level1;
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
    $go_back = isset($d['going-back']) ? intval($d['going-back']) : 0;
    $fa = array_map('strtolower', $actors);
    if (array_key_exists('fa', $d) and !empty($d['fa']) and !$go_back) {
      $fa = array_map('strtolower', $d['fa']);
    }
    if (array_key_exists('past-fa', $d) and !empty($d['past-fa']) and $go_back) {
      $fa = array_map('strtolower', explode(',', $d['past-fa']));
    }
    return $fa;
  }
  function get_filtered_topics($d, $topics) {
    $ft = array_map('strtolower', $topics);
    $go_back = isset($d['going-back']) ? intval($d['going-back']) : 0;
    if (isset($d['ft']) and !empty($d['ft']) and !$go_back) {
      $ft = array_map('strtolower', $d['ft']);
    }
    if (array_key_exists('past-ft', $d) and !empty($d['past-ft']) and $go_back) {
      $ft = array_map('strtolower', explode(';', $d['past-ft']));
    }
    return $ft;
  }
  
  function get_past_filters($d, $key) {
    if (isset($d[$key]) and !empty($d[$key])) {
      return explode(',', $d[$key]);
    } else return array();
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
  function get_or_set_session_id($d) {
    if (isset($d['sessid'])) return $d['sessid'];
    $q = "SELECT * FROM `taskfilters`";
    $r = mysql_query($q);
    return mysql_num_rows($r);
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
        if (isset($ra_map[$uactor])) array_push($ra_map[$uactor], $i);
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
      foreach ($utopics as $utopic) {
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
    $step = ceil(360 / (sizeof($arr) + 1));
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
  function show_bland_legend($timeline_topics) {
    echo '<ul>';
    foreach ($timeline_topics as $topic) {
      echo '<li class="topic-include"><span>' . ucwords($topic) . '</span></li>';
    }
    echo '</ul>';
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
      //array_push($h, $articles[$raid]->get_headline() . '#' . $articles[$raid]->get_id());
      array_push($h, $articles[$raid]->get_id());
    }
    return implode('^', $h);
  }
  
  function get_all_actors($articles, $raids) {
    $a = array();
    foreach ($raids as $raid) {
      $a = array_merge($a, $articles[$raid]->get_uactors());
    }
    return implode('^', array_unique($a));
  }
  function get_all_topics($articles, $raids) {
    $t = array();
    foreach ($raids as $raid) {
      $t = array_merge($t, $articles[$raid]->get_utopics());
    }
    return implode('^', array_unique($t));
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
          $h = get_headlines($articles, $pa);
          $h = $timeline_actor . '|' . $t . '|' . $h;
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
   * Stitched partition events are created by taking each partition and seeing
   * whether a past event shared the head, in which case we will simply extend
   * that event and append the articles on it
   * We will also do some thresholding for a binary classification of an event
   * as IMPORTANT or NOT.
  */
  function create_stitched_partitions($articles, $period_partitions) {
    if (empty($period_partitions)) return;
    $elems = array();
    $min_day_gap = 10;
    $look_behind = 60; /* look behind 3 periods */
    $stitched_period_partitions = array();
    $head_mapping = array(); /* hash map of the actor to pid */
    $pid = 0;
    foreach ($period_partitions as $partitions) {
      $part_id = 0;
      foreach ($partitions as $partition) {
        $t = array($pid, $part_id);
        if (isset($head_mapping[$partition[0]])) {
          array_push($head_mapping[$partition[0]], $t);
        } else {
          $head_mapping[$partition[0]] = array($t);
        }
        $part_id++;
      }
      $pid++;
    }
    foreach ($head_mapping as $head => $periods) {
      $i = 0;
      while ($i < sizeof($periods)) {
        $start_period = $periods[$i];
        $aids = array_values($period_partitions[$start_period[0]][$start_period[1]][1]);
        $sd = $articles[$aids[0]]->get_start_date_ts();
        $i++;
        while ($i < sizeof($periods)) {
          $paids = array_values($period_partitions[$periods[$i][0]][$periods[$i][1]][1]);
          if ($articles[$paids[0]]->days_since($sd) < $look_behind) {
            $period_partitions[$start_period[0]][$start_period[1]][1] =
                    array_merge($period_partitions[$start_period[0]][$start_period[1]][1],
                                $period_partitions[$periods[$i][0]][$periods[$i][1]][1]);
                    unset($period_partitions[$periods[$i][0]][$periods[$i][1]]);
          } else {
            $start_period = $periods[$i];
          }
          $i++;
        }
      }
    }
    return $period_partitions;
  }
  function create_stitched_partition_events($articles, $period_partitions) {
    $elems = array();
    $min_day_gap = 10;
    if (empty($period_partitions)) return;
    foreach ($period_partitions as $partitions) {
      $i = 1;
      // each partition becomes an event
      foreach (array_reverse($partitions) as $partition) {
        $head = $partition[0];
        $article_ids = array_values($partition[1]);
        $startaid = $article_ids[0];
        $endaid = end($article_ids);
        $st = $articles[$startaid]->get_start_date_ts();
        $et = $articles[$endaid]->get_start_date_ts();
        $ed = ($et - $st < $min_day_gap) ?
                                $articles[$endaid]->get_farther_end_date($min_day_gap) :
                                $articles[$endaid]->get_end_date();
        $all_actors = get_all_actors($articles, $article_ids);
        $all_topics = get_all_topics($articles, $article_ids);
        $h = get_headlines($articles, $article_ids);
        $h = implode(DESCRIPTION_DELIMITER, array($all_actors , $all_topics, $h));
        $c = sizeof($article_ids) >= 3 ? PROMINENT : DULL;
        $elem = array(
          'title'       =>  '',
          'description' =>  $h,
          'textColor'   =>  BLACK,
          'start'       =>  $articles[$startaid]->get_start_date(),
          'end'         =>  $ed,
          'color'       =>  $c
        );
        array_push($elems, $elem);
        $i++;
      }
    }
    return $elems;
  }
  
  /**
   * parse the period_partitions object and create a json object in the format
   * expected by TimelineJS
   */
  function create_stitched_timelinejs_events($articles, $period_partitions) {
    $elems = array();
    $min_day_gap = 10;
    if (empty($period_partitions)) return;
    foreach ($period_partitions as $partitions) {
      $i = 1;
      foreach(array_reverse($partitions) as $partition) {
        $head = $partition[0];
        $article_ids = array_values($partition[1]);
        $startaid = $article_ids[0];
        $endaid = end($article_ids);
        $st = $articles[$startaid]->get_start_date_ts();
        $et = $articles[$endaid]->get_start_date_ts();
        $ed = ($et - $st < $min_day_gap) ?
                                $articles[$endaid]->get_farther_end_date_timelinejs($min_day_gap) :
                                $articles[$endaid]->get_end_date_timelinejs();
        $all_actors = get_all_actors($articles, $article_ids);
        $all_topics = get_all_topics($articles, $article_ids);
        $h = get_headlines($articles, $article_ids);
        $h = implode(DESCRIPTION_DELIMITER, array($all_actors , $all_topics, $h));
        $c = sizeof($article_ids) >= 3 ? PROMINENT : DULL;
        $media = array(
                    'media'   =>  '<h2>Hello, there from media</h2>',
                    'credit'  =>  '',
                    'caption' =>  ''
                    );
        $elem = array(
          'startDate'   =>    $articles[$startaid]->get_start_date_timelinejs(),
          'headline'    =>    'Test Headline',
          'endDate'     =>    $ed,
          'text'        =>    'Text summary of the article.',
          'asset'       =>    $media
        );
        array_push($elems, $elem);
        $i++;
      }
    }
    return $elems;
  }
  
  function create_partition_events($articles, $period_partitions) {
    $elems = array();
    $min_day_gap = 10;
    foreach ($period_partitions as $partitions) {
      // each partition becomes an event
      $i = 1;
      foreach (array_reverse($partitions) as $partition) {
        $head = $partition[0];
        $article_ids = array_values($partition[1]);
        $startaid = $article_ids[0];
        $endaid = end($article_ids);
        $st = $articles[$startaid]->get_start_date_ts();
        $et = $articles[$endaid]->get_start_date_ts();
        $ed = ($et - $st < $min_day_gap) ?
                                $articles[$endaid]->get_farther_end_date($min_day_gap) :
                                $articles[$endaid]->get_end_date();
        $all_actors = get_all_actors($articles, $article_ids);
        $all_topics = get_all_topics($articles, $article_ids);
        $h = get_headlines($articles, $article_ids);
        $h = $all_actors . '|' . $all_topics . '|' . $h;
        $elem = array(
          'title'       =>  '',
          'description' =>  $h,
          'trackNum'    =>  $i,
          'textColor'   =>  BLACK,
          'start'       =>  $articles[$startaid]->get_start_date(),
          'end'         =>  $ed,
          'color'       =>  BLACK
        );
        array_push($elems, $elem);
        $i++;
      }
    }
    return $elems;
  }
  function set_up_timelinejs($timelinejs_events) {
    $timelinejs = array();
    $timeline = array(
                'headline'  =>  'Timeline Event Headline',
                'type'      =>  'default',
                'text'      =>  'The main timeline text about the task',
                'startDate' =>  '2012,1,1',
                'date'      =>  $timelinejs_events
              );
    $timelinejs['timeline'] = $timeline;
    return $timelinejs;
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
  function get_timeline_events($timeline_actors, $articles, $ra_map, $t_color_map, $tid) {
    // defaults
    $ws = 20;
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
    will be called EVENTS! Let us go ahead and visualize!
  */
  
  function rec_partition($period, $ra_map, $pa, $cutoff, $current) {
    if ($current >= $cutoff) return array();
    if (empty($period) or empty($pa)) return array();
    $psize = sizeof($period);
    $ra_pop = array();
    $max_effect = -1;
    foreach ($pa as $a) {
      $aids = $ra_map[$a];
      $score = sizeof(array_intersect($aids, $period)) / $psize;
      if ($max_effect < $score) {
        $mi_actor = $a;
        $max_effect = $score;
      }
    }
    $this_actor_ids = array_intersect($period, $ra_map[$mi_actor]);
    $other_ids = array_diff($period, $this_actor_ids);
    if (empty($this_actor_ids)) return array();
    $get_remaining = rec_partition($other_ids, $ra_map, $pa, $cutoff, $current + 1);
    array_push($get_remaining, array($mi_actor, $this_actor_ids));
    return $get_remaining;
  }
  
  function article_periodize($articles, $ra_map, $cutoff) {
    if (!sizeof($articles)) return;
    $st = $articles[0]->get_start_date_ts();
    $ws = 20;
    $periods = array(array()); // partitioning of articles id
    $period_actors = array();
    $pid = 0;
    $i = 0;
    while ($i < sizeof($articles)) {
      if (($articles[$i]->days_since($st) / $ws) <= 1) {
        array_push($periods[$pid], $i);
        if (isset($period_actors[$pid])) {  
          $period_actors[$pid] = array_merge($period_actors[$pid],
                                             $articles[$i]->get_uactors());
        } else {
          $period_actors[$pid] = $articles[$i]->get_uactors();
        }
        $i++;
      }
      else {
        $pid++;
        $st = $articles[$i]->get_start_date_ts();
        array_push($periods, array());
      }
    }
    $period_actors = array_map("array_unique", $period_actors);
    $period_partitions = array();
    for ($pid = 0; $pid < sizeof($periods); $pid++) {
      array_push($period_partitions, rec_partition($periods[$pid], $ra_map,
                                        $period_actors[$pid], $cutoff, 0));
    }
    return $period_partitions;
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
