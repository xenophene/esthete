<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>News Browsing</title>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.8.23.custom.css"/>
    <link rel="stylesheet" href="jquery.multiselect.filter.css"/>
    <link rel="stylesheet" href="jquery.multiselect.css"/>
    <link rel="stylesheet" href="survey-css.css"/>
    
    <!--Thank you, David François Huynh!-->
    <script src="src/webapp/api/timeline-api.js?bundle=true" type="text/javascript"></script>
    <!--Thank you, John Resig!-->
    <script src="jquery.min.js" type="text/javascript"></script>
    <!--The friendly Bootstrap library!-->
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    <script src="js/bootstrap-tooltip.js" type="text/javascript"></script>
    <script src="js/bootstrap-popover.js" type="text/javascript"></script>
    <!--Fancy UI components from jquery-ui-->
    <script src="jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
    <script src="jquery.multiselect.filter.min.js" type="text/javascript"></script>
    <script src="jquery.multiselect.min.js" type="text/javascript"></script>
    
    <script src="survey-script.js" type="text/javascript"></script>
  </head>
  <body>

<?php
  // THIS IS THE ACTOR VIEW. HAS THE ACTORS ON THE TIMELINE AND THE TOPICS
  // WILL BE AS POPOVERS ON THE TIMELINE STRIP
  include 'config_survey.php';
  include 'Article.php';
  include 'aux_functions.php';
  include 'tasks.php';
  
  // the task-dependent constants
  $limit = 200;
  $task_id = get_task_id($_GET);// encoding for the different tasks
  if ($task_id >= $total_tasks) $task_id = 0;
  $actors = get_actors($task_id);
  $topics = get_topics($task_id);
  $tablename = get_table_name($task_id);
  
  // get arguments
  $ts = get_time_spent($_POST);
  $fa = get_filtered_actors($_POST, $actors);
  $ft = get_filtered_topics($_POST, $topics);
  $fd = get_start_date($_POST, $task_start_date[$task_id]);
  $td = get_end_date($_POST, $task_end_date[$task_id]);
  $fyear = get_year($fd);
  $fmonth = get_month($fd);
  $fday = get_day($fd);
  $tyear = get_year($td);
  $tmonth = get_month($td);
  $tday = get_day($td);
  
  store_relevance($fa, $ft, $task_id);
  $r = craft_and_run_query($fa, $ft, $fd, $td, $tablename, $limit);
  
  $articles = array();
  for ($i = 0; $i < mysql_num_rows($r); $i++) {
    $row = mysql_fetch_assoc($r);
    $article = new Article($row);
    $article->remove_actors($fa);
    $article->remove_topics($ft);
    array_push($articles, $article);
  }
  
  // actors shown will be all those who occur. topics will be only the ones
  // not contained, so that a natural topic hierarchy is visualized
  $ra_map = reverse_actor_map($articles);
  $timeline_actors = get_actors_by_article_count($ra_map);
  
  $rt_map = reverse_topic_map($articles);
  $topic_containers = get_not_contained($rt_map);
  $timeline_topics = array_keys($topic_containers);
  $t_color_map = assign_colors($timeline_topics);
  
  foreach ($articles as $article) {
    $article->keep_topics($timeline_topics);
  }
  $topics = enrich_tags($topics, $articles, 1);
  $actors = enrich_tags($actors, $articles, 0);
  

  // go about showing these events. for each actors in ra_map, show the articles
  // that talk about the actor, by the topic mentioned. have a further description
  // with headline and link
  
  $jsobj = array('events' => array()); // the Timeline javascript object
  $tid = 1;
  $partition_events = array();
  
  if (isset($_GET['on'])) {
    $tid = 4;
    $period_partitions = article_periodize($articles, $ra_map);
    $partition_events = create_partition_events($articles, $period_partitions);
  }
  $jsobj['events'] = get_timeline_events($timeline_actors, $articles, $ra_map, $t_color_map, $tid);
  $jsobj['events'] = array_merge($jsobj['events'], $partition_events);
  
  echo '<script>
    var task_id = ' . json_encode($task_id) . ';
    var data = ' . json_encode($jsobj) . ';
    var fmonth = ' . json_encode($fmonth) . ';
    var tmonth = ' . json_encode($tmonth) . ';
    var fday = ' . json_encode($fday) . ';
    var tday = ' . json_encode($tday) . ';
    var fyear = ' . json_encode($fyear) . ';
    var tyear = ' . json_encode($tyear) . ';
        </script>';
?>
    <!--<div class="holder"></div> A trick to make always visible filter-->
    <div class="filters">
      <p class="task-desc">
        <span class="task tipsy" rel="tooltip" title="Task Question" data-placement="bottom"><?php show_task_question($task_id); ?></span><br>
        <a id="gi" href="#" rel="tooltip" data-placement="right" class="tipsy" data-original-title="Click to read some general instructions. Click again to hide.">General Instructions</a>
        <span class="hide" id="detail-instructions">
        For the above task, you have to form an opinion and answer based on the relevant articles that appeared. The select boxes below are for filtering on people, topics and from-to date. You can see the color-coded map of all topics that appeared related to the people &amp; topics that are being filtered currently, on the right. You can click on one to append to the search. For every task, you need to submit the appropriate answer in text and/or by selecting the options. You can click on the timeline of actors appearing below to have a look at the topics and articles. A timer is kept to track this session. CAUTION: Clicking at a blank region of the timeline can cause it to shift, we apologize for this, and working to fix it.
        </span>
      </p>
      <form method="POST" id="filter-form">
        <table>
          <thead>
            <th>Filter on actors</th>
            <th>Filter on topics</th>
            <th>From date</th>
            <th>To date</th>
          </thead>
          <tbody>
            <tr>
              <td>
                <select data-placeholder="Filter on Actors..." multiple id="actor-filter" name="fa[]">
                  <?php get_options($fa, $actors); ?>
                </select>
              </td>
              <td>
                <select data-placeholder="Filter on Topics..." multiple id="topic-filter" name="ft[]">
                  <?php get_options($ft, $topics); ?>
                </select>
              </td>
              <td>
                <input type="text" id="fd" name="fd" placeholder="from ..." autocomplete="off" class="ui-widget ui-state-default ui-corner-all" value="<?php echo $fd;?>"/>
              </td>
              <td>
                <input type="text" id="td" name="td" placeholder="till ..." autocomplete="off" class="ui-widget ui-state-default ui-corner-all" value="<?php echo $td;?>"/>
              <input type="hidden" name="ts" id="timer-value">
              <button rel="tooltip" title="Query for articles on the specified filter" type="submit" class="ui-widget ui-state-default ui-corner-all tipsy">query</button>
              </td>
            </tr>
          </tbody>
        </table>
        <p class="footer">
          <!--<form method="POST" action="submit-answer.php">-->
          <?php show_task_options($task_id, $_POST); ?>
          <?php if (isset($_POST['answer-text']) and !empty($_POST['answer-text'])) 
                  $answer = $_POST['answer-text'];
                else $answer = 
                "Your answer...(Kindly include your name as well). If you already know the answer, please skip this task.";
          ?>
          <textarea rel="tooltip" title="Enter your answer here. Incase you find this task difficult, please enter your feedback here." name="answer-text" id="answer-text" placeholder="<?php echo $answer;?>" cols=120 rows=2 class="ui-widget ui-state-default ui-corner-all no-resize tipsy"></textarea>
        </p>
      </form>
      <button rel="tooltip" title="Submit this answer" id="submit-answer" class="ui-widget ui-state-default ui-corner-all tipsy">submit answer</button>
      <button rel="tooltip" title="Skip this task" id="skip-task" class="ui-widget ui-state-default ui-corner-all tipsy">skip task</button>
          <!--</form>-->
    </div>
    <div class="time tipsy" title="Time elapsed in this session" data-placement="left">
      Timer:
      <span id="minutes"><?php echo $ts[0];?></span>:<span id="seconds"><?php echo $ts[1];?></span>
    </div>
    <div rel="tooltip" class="legend tipsy" title="Select topics to go into the filter">
      <?php show_legend($t_color_map);?>
    </div>
    <div id="tl"></div>
    <div id="modal-bubble" class="modal hide fade">
      <div class="modal-header"></div>
      <div class="modal-body"></div>
      <div class="modal-footer"></div>
    </div>
  </body>
</html>
