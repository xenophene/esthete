<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>News Browsing Tool</title>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.8.23.custom.css"/>
    <link rel="stylesheet" href="jquery.multiselect.filter.css"/>
    <link rel="stylesheet" href="jquery.multiselect.css"/>
    <link rel="stylesheet" href="survey-css.css"/>
    <link rel="icon" href="nb.ico"/>
    
    <!--Thank you, David François Huynh!-->
    <script src="src/webapp/api/timeline-api.js?bundle=true" type="text/javascript"></script>
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
  $limit = 1000;
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
  $article_identifier = array();
  
  for ($i = 0; $i < mysql_num_rows($r); $i++) {
    $row = mysql_fetch_assoc($r);
    $article = new Article($row);
    $article->remove_actors($fa);
    $article->remove_topics($ft);
    array_push($articles, $article);
    $article_identifier[$article->get_id()] = $article->get_headline();
  }
  
  // actors shown will be all those who occur. topics will be only the ones
  // not contained, so that a natural topic hierarchy is visualized
  $ra_map = reverse_actor_map($articles);
  $timeline_actors = get_actors_by_article_count($ra_map);
  // take an array slice here.
  
  $rt_map = reverse_topic_map($articles);
  $topic_containers = get_containers($rt_map);
  
  $level1 = get_first_level($topic_containers);
  
  $timeline_topics = array_keys($level1);
  //$t_color_map = assign_colors($timeline_topics);
  
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
  // do aggregation always!
  $cutoff = 5;
  $tid = $cutoff + 1;
  $period_partitions = article_periodize($articles, $ra_map, $cutoff);
  $period_partitions = create_stitched_partitions($articles, $period_partitions);
  $partition_events = create_stitched_partition_events($articles, $period_partitions);
  
  
  //$jsobj['events'] = get_timeline_events($timeline_actors, $articles, $ra_map, $t_color_map, $tid);
  $jsobj['events'] = $partition_events;
  
?>
    <!--<div class="holder"></div> A trick to make always visible filter-->
    <div class="filters">
      <p class="task-desc">
        <span class="task tipsy" rel="tooltip" title="Task Question" data-placement="bottom"><?php show_task_question($task_id); ?></span><br>
        <a id="gi" href="#" rel="tooltip" data-placement="right" class="tipsy" data-original-title="Click to read some general instructions. Click again to hide.">General Instructions</a>
        <span class="hide" id="detail-instructions">
        For the above task, you have to form an opinion and answer based on the relevant articles that appeared. The select boxes below are for filtering on people, topics and from-to date. A list of events that occured through the year are shown on the timeline below. A timer is kept to track this session. <em>Please avoid pressing Back, instead deselect the filters.</em>
        </span>
      </p>
      <form method="POST" id="filter-form">
        <table>
          <thead>
            <th>Filter on the actors below</th>
            <th>Filter on the topics below</th>
            <th>From the date</th>
            <th>Till the date</th>
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
          <?php show_task_options($task_id, $_POST); ?>
          <?php if (isset($_POST['answer-text']) and !empty($_POST['answer-text'])) 
                  $answer = $_POST['answer-text'];
                else $answer = 
                "Your answer...(Kindly include your name as well). If you already know the answer, please skip this task.";
          ?>
          <textarea rel="tooltip" title="Enter your answer here. Incase you find this task difficult, please enter your feedback here." name="answer-text" id="answer-text" placeholder="<?php echo $answer;?>" cols=120 rows=2 class="ui-widget ui-state-default ui-corner-all no-resize tipsy"></textarea>
        </p>
      </form>
      <button rel="tooltip" title="Submit this answer." id="submit-answer" class="ui-widget ui-state-default ui-corner-all tipsy">submit answer</button>
      <button rel="tooltip" title="Skip this task and go back to the survey home page." id="skip-task" class="ui-widget ui-state-default ui-corner-all tipsy">skip task</button>
      <!--<button rel="tooltip" title="Aggregation tries to aggregate all the articles related by one or more common actors and topics into a single black block." id="turn-on" class="ui-widget ui-state-default ui-corner-all tipsy">toggle aggregation feature</button>-->
      <button rel="tooltip" title="Show all articles relevant to the filtered actors and topics" id="show-all-articles" class="ui-widget ui-state-default ui-corner-all tipsy">show all articles</button>
      <button rel="tooltip" title="Study the interaction among the filtered set of actors" id="study-interaction" class="ui-widget ui-state-default ui-corner-all tipsy">study interaction</button>
      <!--<a id="zout" class="icon-zoom-in tipsy" title="Zoom Into the Timeline"></a>
      <a id="zin" class="icon-zoom-out tipsy" title="Zoom Out From the Timeline"></a>-->
    </div>
    <div class="time tipsy" title="Time elapsed in this session" data-placement="left">
      Timer:
      <span id="minutes"><?php echo $ts[0];?></span>:<span id="seconds"><?php echo $ts[1];?></span>
    </div>
    <div rel="tooltip" class="legend tipsy" title="Select topics to go into the filter">
      <?php show_bland_legend($timeline_topics);?>
    </div>
    <span style="padding-left:30px;font-weight:bold;color:<?php echo BLACK;?>">Timeline events involving the filtered actors and topics:</span>
    <div id="tl"></div>
    <?php if (sizeof($articles)): ?>
      <span style="padding-left:30px;font-weight:bold;color:<?php echo PROMINENT;?>">Significant Event</span><br>
    <?php else: ?>
      <span style="padding-left:30px;font-weight:bold;"><?php  echo 'No Articles Found.';?></span>
    <?php endif; ?>
    <div id="modal-bubble" class="modal hide fade">
      <div class="modal-header"></div>
      <div class="modal-body"></div>
      <div class="modal-footer"></div>
    </div>
    <!--Thank you, John Resig!-->
    <script src="jquery.min.js" type="text/javascript"></script>
    <!--The friendly Bootstrap library!-->
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    <script src="js/bootstrap-tooltip.js" type="text/javascript"></script>
    <script src="js/bootstrap-popover.js" type="text/javascript"></script>
    <script src="js/bootstrap-tab.js" type="text/javascript"></script>
    <!--Fancy UI components from jquery-ui-->
    <script src="jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
    <script src="jquery.multiselect.filter.min.js" type="text/javascript"></script>
    <script src="jquery.multiselect.min.js" type="text/javascript"></script>
    <!--Google Charting API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <!--D3 Library-->
    <script src="http://d3js.org/d3.v2.min.js?2.8.1"></script>
    <!--My Script-->
    <script src="survey-script_v2.js" type="text/javascript"></script>
    <?php
      echo '<script>
      var task_id = ' . json_encode($task_id) . ';
      var data = ' . json_encode($jsobj) . ';
      var fmonth = ' . json_encode($fmonth) . ';
      var tmonth = ' . json_encode($tmonth) . ';
      var fday = ' . json_encode($fday) . ';
      var tday = ' . json_encode($tday) . ';
      var fyear = ' . json_encode($fyear) . ';
      var tyear = ' . json_encode($tyear) . ';
      var article_identifier = ' . json_encode($article_identifier) . ';
      var levels = ' . json_encode($topic_containers) . ';
          </script>';
    ?>
  </body>
</html>
