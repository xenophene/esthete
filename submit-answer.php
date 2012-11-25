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
  </body>
</html>
