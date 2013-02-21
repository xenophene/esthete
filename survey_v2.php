<!DOCTYPE html>
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
  if ($task_id >= $total_tasks or $task_id < 0) $task_id = 0;
  $actors = get_actors($task_id);
  $topics = get_topics($task_id);
  $tablename = get_table_name($task_id);
  
  // get arguments
  $ts = get_time_spent($_POST);
  $pfa = get_past_filters($_POST, 'past-fa', $actors);
  $pft = get_past_filters($_POST, 'past-ft', $topics);
  $fa = get_filtered_actors($_POST, $actors, $pfa);
  $ft = get_filtered_topics($_POST, $topics, $pft);
  $fd = get_start_date($_POST, $task_start_date[$task_id]);
  $td = get_end_date($_POST, $task_end_date[$task_id]);
  $session = get_or_set_session_id($_POST);
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
    //$article->remove_actors($fa);
    //$article->remove_topics($ft);
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
  
  $timeline_topics = array_keys($rt_map);
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
  $cutoff = 10;
  $tid = $cutoff + 1;
  $period_partitions = article_periodize($articles, $ra_map, $cutoff);
  $period_partitions = create_stitched_partitions($articles, $period_partitions);
  
  $partition_events = create_stitched_partition_events($articles, $period_partitions);
  $timelinejs_events = create_stitched_timelinejs_events($articles, $period_partitions);
  
  $timelinejs = set_up_timelinejs($timelinejs_events);
  //$jsobj['events'] = get_timeline_events($timeline_actors, $articles, $ra_map, $t_color_map, $tid);
  $jsobj['events'] = $partition_events;
  
?>
    <!--<div class="holder"></div> A trick to make always visible filter-->
    <!--    The filter system is in a grid. -->
    <div id="header">News Browsing Tool</div>
    <div class="filters row">
      <div class="span7">
        <span class="task tipsy" rel="tooltip" title="Task Question" data-placement="bottom"><strong>Task Question: </strong> <?php show_task_question($task_id); ?></span>
          <?php show_task_options($task_id, $_POST);?>
      </div>
      <div class="span7">
        <form method="POST" id="filter-form">
          <table>
            <tbody>
              <tr>
                <td class="head">Filter on the actors below</td>
                <td class="head">From the date</td>
              </tr>
              <tr>
                <td>
                  <select data-placeholder="Filter on Actors..." multiple id="actor-filter" name="fa[]">
                    <?php get_options($fa, $actors); ?>
                  </select>
                </td>
                <td>
                  <input type="text" id="fd" name="fd" placeholder="from ..." autocomplete="off"
                         class="ui-widget ui-state-default ui-corner-all" value="<?php echo $fd;?>"/>
                  <button rel="tooltip" title="Go back to the previous filter settings" id="go-back"
                          class="ui-widget ui-state-default ui-corner-all tipsy">back</button>
                </td>
              </tr>
              <tr>
                <td class="head">Filter on the topics below</td>
                
                <td class="head">To the date</td>
              </tr>
              <tr>
                <td>
                  <select data-placeholder="Filter on Topics..." multiple id="topic-filter" name="ft[]">
                    <?php get_options($ft, $topics); ?>
                  </select>
                </td>
                <td>
                  <input type="text" id="td" name="td" placeholder="till ..." autocomplete="off"
                         class="ui-widget ui-state-default ui-corner-all" value="<?php echo $td;?>"/>
                  <input type="hidden" name="ts" id="timer-value"/>
                  <input type="hidden" name="sessid" id="sessid" value="<?php echo $session;?>"/>
                  <input type="hidden" name="past-fa" id="past-fa"
                         value="<?php echo implode(',', $pfa); ?>"/>
                  <input type="hidden" name="past-ft" id="past-ft"
                         value="<?php echo implode(';', $pft); ?>"/>
                  <input type="hidden" name="going-back" id="going-back" value="0"/>
                  <button rel="tooltip" title="Query for articles on the specified filter"
                          type="submit" class="ui-widget ui-state-default ui-corner-all tipsy">query</button>
                </td>
              </tr>
            </tbody>
          </table>
          <!--
          <p class="footer">
            <?php //show_task_options($task_id, $_POST); ?>
            <?php /*if (isset($_POST['answer-text']) and !empty($_POST['answer-text'])) 
                    $answer = $_POST['answer-text'];
                  else $answer = 
                  "Your answer...(Kindly include your name as well). If you already know the answer, please skip this task.";*/
            ?>
            <textarea rel="tooltip" title="Enter your answer here. Incase you find this task difficult, please enter your feedback here." name="answer-text" id="answer-text" placeholder="<?php echo $answer;?>" cols=120 rows=2 class="ui-widget ui-state-default ui-corner-all no-resize tipsy"></textarea>
          </p>
          -->
        </form>
      </div>
    </div>
    <div class="time tipsy" title="Time elapsed in this session" data-placement="left">
      Timer:
      <span id="minutes"><?php echo $ts[0];?></span>:<span id="seconds"><?php echo $ts[1];?></span>
    </div>
    
    
    <!--
    <div rel="tooltip" class="legend tipsy" title="Select topics to go into the filter">
      <?php //show_bland_legend($timeline_topics);?>
    </div>
    -->
    <!--<span style="padding-left:20px;font-weight:bold;color:<?php echo BLACK;?>">Timeline events around the filtered actors and topics. </span><span style="font-weight:bold;color:<?php echo PROMINENT;?>">(Events in this color are most significant)</span><br>-->
    <!--<div id="tl"></div>-->
    <div id="timeline-embed"></div>
    <div class="row">
      <div class="span12 gi">
        <a id="gi" href="#" rel="tooltip" data-placement="right" class="tipsy small" data-original-title="Click to read some general instructions. Click again to hide.">General Instructions</a>
        <span class="hide" id="detail-instructions">
        For the above task, you have to find relevant articles for one or more of the points. When you find a relevant article, you should mark it as relevant for the corresponding point. Clicking on a bar on the timeline below displays more information about it. The select boxes below are for filtering on people, topics and from-to date. A timer is kept to track this session. <em>Please avoid pressing Back, instead deselect the filters.</em>
        </span>
      </div>
      
      <!--<a id="zout" class="icon-zoom-in tipsy" title="Zoom Into the Timeline"></a>
      <a id="zin" class="icon-zoom-out tipsy" title="Zoom Out From the Timeline"></a>-->
    </div>
    
    <div class="row shift-right">
      <div class="controls span2">
        <?php if (!sizeof($articles)): ?>
          <span style="font-weight:bold;"><?php  echo 'No Articles Found.';?></span>
        <?php endif; ?>
        <br>
        <button rel="tooltip" data-placement="right" title="Submit this answer." id="submit-answer" class="ui-widget ui-state-default ui-corner-all tipsy">finish task</button>
        <button rel="tooltip" data-placement="right" title="Skip this task and go back to the survey home page." id="skip-task" class="ui-widget ui-state-default ui-corner-all tipsy">skip task</button>
        <!--<button rel="tooltip" title="Aggregation tries to aggregate all the articles related by one or more common actors and topics into a single black block." id="turn-on" class="ui-widget ui-state-default ui-corner-all tipsy">toggle aggregation feature</button>-->
        <button rel="tooltip" data-placement="right" title="Show all articles relevant to the filtered actors and topics" id="show-all-articles" class="ui-widget ui-state-default ui-corner-all tipsy">show all articles</button>
        <button rel="tooltip" data-placement="right" title="Study the interaction among the filtered set of actors" id="study-interaction" class="ui-widget ui-state-default ui-corner-all tipsy">study interaction</button>
      </div>
      <div class="span12" id="headline-key"></div>
    </div>
    
    <!--  Placeholder for Google Maps on the annual interaction -->
    <div class="row tipsy">
      <div class="span12" id="i-graph">
        <ul class="loading"><li><img src="loading3.gif" alt="Loading" title="Loading"/></li></ul>
      </div>
    </div>
    
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
    <!--TimelineJS API-->
    <script type="text/javascript" src="TimelineJS-master/compiled/js/storyjs-embed.js"></script>
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
      var session = ' . json_encode($session) . ';
      var article_identifier = ' . json_encode($article_identifier) . ';
      var levels = ' . json_encode($topic_containers) . ';
      var timelinejsobj = ' . json_encode($timelinejs) . ';
          </script>';
    ?>
  </body>
</html>