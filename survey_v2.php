<!DOCTYPE html>
<html>
  <head>
    
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>News Browsing Tool</title>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.8.23.custom.css"/>
    <link rel="stylesheet" href="jquery.multiselect.filter.css"/>
    <link rel="stylesheet" href="jquery.multiselect.css"/>
    <link rel="stylesheet" href="css/jquery.tagit.css"/>
    <link rel="stylesheet" href="css/tagit.ui-zendesk.css"/>
    <link rel="stylesheet" href="survey-css.css"/>
    <link rel="icon" href="nb.ico"/>
    
    <!--Thank you, David François Huynh!-->
    <!--<script src="src/webapp/api/timeline-api.js?bundle=true" type="text/javascript"></script>-->
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
  
  // check whether the url field is present, in which case we will get the
  // actors/topics from this article
  $url_data = get_filter_url($_POST, $tablename);
  if ( ! empty($url_data)) {
    $fa = $url_data[0];
    $ft = $url_data[1];
  } else {
    $fa = get_filtered_actors($_POST, $actors, $pfa);
    $ft = get_filtered_topics($_POST, $topics, $pft);
  }
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
  $indexid = array();
  $actor_count = array();
  $topic_count = array();
  
  for ($i = 0; $i < mysql_num_rows($r); $i++) {
    $row = mysql_fetch_assoc($r);
    $article = new Article($row);
    $article->remove_actors($fa);
    $article->remove_topics($ft);
    $actor_count = $article->update_actor_count($actor_count);
    $topic_count = $article->update_topic_count($topic_count);
    
    array_push($articles, $article);
    $article_identifier[$article->get_id()] = $article->get_headline();
    $indexid[$article->get_id()] = $i;
  }
  $counts = setup_top_counts($actor_count, $topic_count);
  // actors shown will be all those who occur. topics will be only the ones
  // not contained, so that a natural topic hierarchy is visualized
  $ra_map = reverse_actor_map($articles);
  $timeline_actors = get_actors_by_article_count($ra_map);
  // take an array slice here.
  $rt_map = reverse_topic_map($articles);
  //$topic_containers = get_containers($rt_map);
  $topic_containers = array();
  
  
  //$level1 = get_first_level($topic_containers);
  
  $timeline_topics = array_keys($rt_map);
  //$t_color_map = assign_colors($timeline_topics);
  
  foreach ($articles as $article) {
    $article->keep_topics($timeline_topics);
  }
  //$topics = enrich_tags($topics, $articles, 1);
  //$actors = enrich_tags($actors, $articles, 0);
  
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
  
  //$partition_events = create_stitched_partition_events($articles, $period_partitions);
  
  $clusters = get_clusters($task_id);
  $cluster_partitions = get_cluster_partitions($clusters, $articles,
                                               array_merge(
                                                          $fa,
                                                          $timeline_actors
                                                          ), $indexid);
  if ( ! empty($cluster_partitions)) {
    $timelinejs = set_up_timelinejs($cluster_partitions);
  } else {
    $timelinejs_events = create_stitched_timelinejs_events($articles,
                                                           $period_partitions,
                                                           array_map('strtolower',
                                                                       array_merge(
                                                                        $fa,
                                                                        $timeline_actors
                                                                        ))
                                                          );
    $timelinejs = set_up_timelinejs($timelinejs_events);
  }
  
  //echo json_encode($timelinejs);
  //$jsobj['events'] = get_timeline_events($timeline_actors, $articles, $ra_map, $t_color_map, $tid);
  //$jsobj['events'] = $partition_events;
  
  $jsobj['events'] = array();
  
?>
    <!--<div class="holder"></div> A trick to make always visible filter-->
    <!--    The filter system is in a grid. -->
    <div id="header">News Browsing Tool - <?php show_task_question($task_id);?></div>
    <div class="filters row">
      <!--
      <div class="span3">
        <span class="task tipsy" rel="tooltip" title="Task Question" data-placement="bottom">
          <?php //show_task_question($task_id); ?></span>
          <?php //show_task_options($task_id, $_POST);?>
      </div>
      -->
      <div class="span7">
        <ul id="omni-search" name="omni-search">
          <?php
            foreach ($fa as $a)  {
              echo '<li>' . $a . '</li>';
            }
            foreach ($ft as $t)  {
              echo '<li>' . $t . '</li>';
            }
          ?>
        </ul>
        <form method="POST" id="filter-form">
          <table>
            <tbody>
              <tr>
                <td class="head">Select from one or more actors below:</td>
                <td class="head">From the date</td>
              </tr>
              <tr>
                <td>
                  <select data-placeholder="Filter on Actors..." multiple id="actor-filter" name="fa[]">
                    <?php get_options($fa, $timeline_actors); ?>
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
                <td class="head">Select from one or more topics below:</td>
                <td class="head">To the date</td>
              </tr>
              <tr>
                <td>
                  <select data-placeholder="Filter on Topics..." multiple id="topic-filter" name="ft[]">
                    <?php get_options($ft, $timeline_topics); ?>
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
                  <button rel="tooltip" title="Click here to query for articles on the specified filter"
                          type="submit" id="query"
                          class="ui-widget ui-state-default ui-corner-all tipsy">query</button>
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
            <textarea rel="tooltip" title="Enter your answer here. Incase you find this task difficult, please enter your feedback here." name="answer-text" id="answer-text" placeholder="<?php //echo $answer;?>" cols=120 rows=2 class="ui-widget ui-state-default ui-corner-all no-resize tipsy"></textarea>
          </p>
          -->
          
            Want the story around an article? Select its URL from here!: 
            <?php
              $url_value = isset($_POST['url']) ? $_POST['url'] : '';
              $url_placeholder = isset($_POST['url']) ? '' : "Enter an article's url";
              $urls = array(
'http://www.thehindu.com/news/national/mamata-offers-support-for-stringent-punishment/article4253461.ece',
'http://www.thehindu.com/news/national/other-states/fouryearold-dalit-girl-raped-in-karad/article2840603.ece',                           'http://www.thehindu.com/news/strausskahn-maid-reach-agreement/article4151318.ece',
'http://www.thehindu.com/news/cities/chennai/rape-accused-remanded-to-judicial-custody/article2876321.ece','http://www.thehindu.com/news/cities/Madurai/crime-against-mentally-ill-women-on-the-rise/article2810812.ece'
                        );
            ?>
            <select name="url" id="url">
              <option value="">No url selected</option>
              <?php foreach ($urls as $url): ?>
              <option value="<?php echo $url;?>">
              <?php
                foreach (explode('/', $url) as $elem) {
                  if (strpos($elem, '-') !== false) {
                    $headline = ucwords(str_replace('-', ' ', $elem));
                  }
                }
                echo $headline;
              ?></option>
              <?php endforeach; ?>
            </select>
        </form>
      </div>
      <div class="span3" id="mpa-list">
        <strong><u>Most Popular Actors</u></strong>
      </div>
      <div class="span3" id="mpt-list">
        <strong><u>Most Popular Topics</u></strong>
        
      </div>
      <div class="span4" id="mpe-chart">
        <strong><u>Overall News Coverage</u></strong>
        <div id="mpe-chart-chart"></div>
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
    <div class="row">
      <div class="span12 gi">
        <!--<a target="_blank" class="tipsy small" href="https://docs.google.com/forms/d/1zLtIDKitaQ6sZy_jNYRnADd7is-lq6qi9ETJvzRgk4U/viewform">After using this tool, please answer this questionnaire here</a><br/>-->
        <?php if (sizeof($articles)): ?>
        <strong>The following news features
        <?php if (empty($fa)): ?> all actors
        <?php else: echo 'the actors: ' . implode(' and ', $fa); endif; ?>
        on
        <?php if (empty($ft)): ?> all topics
        <?php else: echo 'the topics: ' . implode(' and ', $ft); endif; ?>
        </strong>
        <?php endif; ?>
        <!--<a id="gi" href="#" rel="tooltip" data-placement="right" class="tipsy small" data-original-title="Click to read some general instructions. Click again to hide.">Please read the General Instructions here</a>-->
        <span class="hide" id="detail-instructions">
          <a target="_blank" href="https://docs.google.com/forms/d/1zLtIDKitaQ6sZy_jNYRnADd7is-lq6qi9ETJvzRgk4U/viewform">Click here for giving the survey</a>
          For the specific theme of this task, you are requested to navigate and browse through the articles, using the tool. You would later be asked to rate the tool on usability, quality of content returned, coverage of content, etc. Clicking on an event of the time-line below displays a summary, and the articles that appeared. The select boxes below are for filtering on people, topics and from-to date, and studying only these attributes. A timer is kept to track this session. 
Once, you become sufficiently well-versed with this tool, you will be asked to answer a small questionnaire to rate the aspects of this tool, and some story-related questions to know how much you learnt about the presented story through this tool (<a target="_blank" href="https://docs.google.com/forms/d/1zLtIDKitaQ6sZy_jNYRnADd7is-lq6qi9ETJvzRgk4U/viewform">here</a>). Please don't press browser Back button, instead deselect the filters.
        </span>
      </div>
      
      <!--<a id="zout" class="icon-zoom-in tipsy" title="Zoom Into the Timeline"></a>
      <a id="zin" class="icon-zoom-out tipsy" title="Zoom Out From the Timeline"></a>-->
    </div>
    <div id="timeline-embed"></div>
    
    
    <div class="row">
      <div class="controls span8">
        <?php if (!sizeof($articles)): ?>
          <span style="font-weight:bold;">No Articles Found. Please click Back or remove some topics or actors from the filter.</span><br>
        <?php endif; ?>
        <button rel="tooltip" data-placement="right" title="Submit this answer." id="submit-answer" class="ui-widget ui-state-default ui-corner-all tipsy">finish task</button>
        <button rel="tooltip" data-placement="right" title="Skip this task and go back to the survey home page." id="skip-task" class="ui-widget ui-state-default ui-corner-all tipsy">skip and study other stories</button>
        <!--<button rel="tooltip" title="Aggregation tries to aggregate all the articles related by one or more common actors and topics into a single black block." id="turn-on" class="ui-widget ui-state-default ui-corner-all tipsy">toggle aggregation feature</button>-->
        <button rel="tooltip" data-placement="right" title="Show all articles relevant to the filtered actors and topics" id="show-all-articles" class="ui-widget ui-state-default ui-corner-all tipsy">show all articles</button>
        <!--<button rel="tooltip" data-placement="right" title="Study the interaction among the filtered set of actors" id="study-interaction" class="ui-widget ui-state-default ui-corner-all tipsy">study interaction</button>-->
      </div>
      <div class="span2" id="headline-key"></div>
    </div>
    
    <!--  Placeholder for Google Maps on the annual interaction -->
    <!--
    <div class="row tipsy">
      <div class="span12" id="i-graph">
        <ul class="loading"><li><img src="loading3.gif" alt="Loading" title="Loading"/></li></ul>
      </div>
    </div>
    -->
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
    <!--Fancy UI components from jQuery-UI-->
    <script src="jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
    <script src="jquery.multiselect.filter.min.js" type="text/javascript"></script>
    <script src="jquery.multiselect.min.js" type="text/javascript"></script>
    <!--TimelineJS API-->
    <script type="text/javascript" src="TimelineJS-master/compiled/js/storyjs-embed.js"></script>
    <!--Google Charting API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <!--jQuery Tag It API-->
    <script type="text/javascript" src="js/tag-it-min.js"></script>
    <!--D3 Library-->
    <!--<script src="http://d3js.org/d3.v2.min.js?2.8.1"></script>-->
    <!--My Script-->
    <script src="survey-script_v2.js" type="text/javascript"></script>
    <?php
      echo '<script>
      var task_id = ' . json_encode($task_id) . ';
      var data = ' . json_encode($jsobj) . ';
      var top_actors = ' . json_encode($counts[0]) . ';
      var top_topics = ' . json_encode($counts[1]) . ';
      var fmonth = ' . json_encode($fmonth) . ';
      var tmonth = ' . json_encode($tmonth) . ';
      var fday = ' . json_encode($fday) . ';
      var tday = ' . json_encode($tday) . ';
      var fyear = ' . json_encode($fyear) . ';
      var tyear = ' . json_encode($tyear) . ';
      var session = ' . json_encode($session) . ';
      var article_identifier = ' . json_encode($article_identifier) . ';
      var levels = ' . json_encode($topic_containers) . ';
      var timeline_filters = ' . json_encode(array_merge($timeline_topics, $timeline_actors)) . ';
      var timeline_selected_filters = ' . json_encode(array_merge($ft, $fa)) . ';
      var timelinejsobj = ' . json_encode($timelinejs) . ';
          </script>';
    ?>
  </body>
</html>