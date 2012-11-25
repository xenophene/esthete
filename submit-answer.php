<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <link rel="stylesheet" href="survey-css.css"/>
  </head>
  <body style="padding:20px">
    <h3>
      Thank you, for taking the survey! Please find the links to other tasks below.
    </h3>
    <p>
      <ol>
      <?php
        include 'tasks.php';
        for ($i = 0; $i < $total_tasks; $i++) {
          echo '<li><a href="survey.php?taskid=' . $i . '">';
          show_task_question($i);
          echo '</a></li>';
        }
      ?>
      </ol>
    </p>
    <h3>Details: An experiment on Online News Browsing.</h3>
    <p>Hi! We are currently pursuing our Masters project, and our objective is to make online news browsing (specially old archival news) very informative and contextual.
    </p>
    <p>
    Given, a set of articles, we mine the entities appearing in it, and attach them to the topics/events being discussed about in it. All these actor-topic interactions are summarized in time, and are displayed horizontally on a moving timeline. So all the articles are represented as points in time on the timeline, which you can probe at different places. For a particular set of actor-topic filters that you are focussing on, you see the relevant topics &amp; actors, and can broaden or narrow your search in that way. Finally, your task is to answer a particular subjective question from the articles. In most cases, such information, is not readily available from other sources like Wikipedia, or it may be biased. 
    </p>
    <p>
    [1] R. Choudhary, S. Mehta, A. Bagchi, and R. Balakrishnan. Towards characterization of actor
evolution and interactions in news corpora. In Advances in Information Retrieval.<br>
    [2] Shahaf, D., Guestrin, C.: Connecting the dots between news articles. Proceedings of the
    16th ACM SIGKDD International Conference on Knowledge Discovery and Data Mining<br>
    [3] api.opencalais.com<br>
    [4] thehindu.com
    </p>
  </body>
</html>
