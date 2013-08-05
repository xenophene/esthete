<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <link rel="stylesheet" href="survey-css.css"/>
    <link rel="icon" href="nb.ico"/>
    <title>News Browsing Tool</title>
  </head>
  <body style="padding:10px 20px;background:#f9f9f9;">
    <h2>
      IIT Delhi - News Browsing Interface
    </h2>
    <p>Thank you, for taking the survey! Please try the tool by solving any of the following tasks.</p>
    <h3>The survey for the interface is for <a href="survey_v2.php?taskid=8">this task.</a></h3>
    <p>Some other predefined themes are available below.</p>
    <ol>
    <?php
      include 'tasks.php';
      for ($i = 0; $i < $total_tasks; $i++) {
        echo '<li><a href="survey_v2.php?taskid=' . $i . '">';
        show_task_question($i);
        echo '</a></li>';
      }
    ?>
    </ol>
    <div class="row">
      <div class="span5">
        <h3>Video.</h3>
        Coming soon.
        <!--<iframe title="YouTube video player" class="youtube-player" type="text/html" width="360" height="300" src="http://www.youtube.com/embed/1mXXPjg9-yg?rel=0" frameborder="0"></iframe>-->
      </div>
      <div class="span9">
        <h3>Details: An experiment on Online News Browsing.</h3>
        <p>We are currently pursuing our Masters project, and our objective is to make online news browsing (specially old archival news) very informative and contextual.
        </p>
        <p>
        Given, a set of articles, we mine the entities appearing in it, and attach them to the topics/events being discussed about in it. All these actor-topic interactions are summarized in time, and are displayed horizontally on a moving timeline. So all the articles are represented as points in time on the timeline, which you can probe at different places. For a particular set of actor-topic filters that you are focussing on, you see the relevant topics &amp; actors, and can broaden or narrow your search in that way.
        </p>
        <p>
        [1] R. Choudhary, S. Mehta, A. Bagchi, and R. Balakrishnan. Towards characterization of actor
    evolution and interactions in news corpora. In Advances in Information Retrieval.<br>
        [2] Shahaf, D., Guestrin, C.: Connecting the dots between news articles. Proceedings of the
        16th ACM SIGKDD International Conference on Knowledge Discovery and Data Mining<br>
        [3] api.opencalais.com<br>
        [4] thehindu.com
        </p>
      </div>
    </div>
  </body>
</html>