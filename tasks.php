<?php
  $total_tasks = 9;
  // constants functions based on task
  
  function show_task_question($task_id) {
    
    $task_questions = array(
      0 => "Find and mark relevant the article which helps you to answer which of the following statements is true.",
      1 => "Find and mark relevant articles which verify the following statements involving <strong>Nitin Gadkari</strong>.",
      2 => "Find and mark relevant articles which talk of the sports rivalry between Andre Agassi and Pete Sampras.",
      3 => "Find and mark relevant articles which bring out the following facts about news featuring <strong>Robert Vadra</strong>",
      4 => "Find and mark relevant articles which bring out the controversy sorrounding Subodh Kant Sahay and his brother SKS Ispat." ,
      5 => "Find and mark relevant articles which bring out the following facts regarding S. Swamy and P. Chidambaram.",
      6 => "Find and mark relevant articles for each of the following facts regarding recent developments in India's space program.",
      7 => "Find and mark relevant articles which bring out the following events involving Saina Nehwal last year.",
      8 => "Find and mark articles that capture the following facts regarding stories around rape cases in India",
      9 => "Find articles which portray how different sections of society have reacted to the frequent petrol price hikes in the past"
    );
    echo $task_questions[$task_id];
  }
  function show_task_options($task_id, $d) {
    $task_options = array(
      0 => array(
            'John McEnroe, being the USA Tennis coach, was unhappy with Pete Sampras not performing up to the mark',
            'John McEnroe mocked Sampras for not being as passionate for the game as he is',
            'John McEnroe remarked that Sampras only played for money and personal pride over national glory'
          ),
      1 => array(
            'Nitin Gadkari was in the news for scams, revolving around his companies',
            'Nitin Gadkari played accusatory politics, blaming the government and highlighting corruption',
            'Nitin Gadkari was in news for developmental politics, campaigning on issues not related to corruption'
          ),
      2 => array(
            'Andre Agassi dominated Pete Sampras for all of the year',
            'Pete Samprass crossed Agassi in rankings in mid-year',
            'Agassi and Samprass were dominated by another player, and they did not play many matches together'
          ), 
      3 => array(
            'The controversy sorrounding Robert Vadra has been highlighted and brought into limelight by a small section of society',
            'Robert Vadra has been in the news most prominently during election campaigns',
            'Robert Vadra has come forward to defend himself openly on numerous occasions'
          ),
      4 => array(
            "Subodh Kant Sahay allegedly wrote a letter to PM recommending the illegal coal block allocation for his brother's company",
            "Subodh Kant Sahay resigned from the cabinet following the allegations and protests by the opposition",
            "The coal ministry immediately took action following the allegations and deallocated the coal-blocks of SKS Ispat"
          ),
      5 => array(
            "Special Court dismissed Swamy's plea to make Chidambaram a co-accused in 2G-scam.",
            "Swamy sought review of the decision of special court by again moving the Supreme Court against Chidambaram",
            "The govt. openly defended Chidambram and called Swamy's accusations baseless.",
            "Supreme Court dismissed Swamy's plea for probe against Chidambaram."
          ),
      6 => array(
            "ISRO, India's space agency was caught in a scam related to the Defence sector",
            "ISRO was able to successfully launch its 100th PSLV, named SHAR",
            "ISRO announced a plan to launch a Mars rover by the year 2020",
            "Former ISRO chairmen got into a fued with the government"
          ),
      7 => array(
            "Saina was in news for being gifted a BMW by Sachin Tendular",
            "Saina offered a token of appreciation to a fellow olympian for an inspiring victory",
            "Saina was honoured by the PM Manmohan Singh for her performance at the Olympics",
            "Saina was involved in a corruption scam"
          ),
      8 => array(
            "Incidents of rape cases have gone up over the past year",
            "Rape cases in the state of West Bengal have been dealth with harshly",
            "Majority of politicians have openly come out and held women partly responsible in a rape case"
          ),
      9 => array(
              "The opposition has regularly called and implemented a nation bandh",
              "The state governments have on some occasions lowered the taxes on fuel"
          )
    
    );

    $questions = $task_options[$task_id];
    echo '<ul><li>' . implode($questions, '</li><li>') . '</li></ul>';
    /*
    $resp = '';
    if (isset($d['answer-options'])) $options = $d['answer-options'];
    else $options = array();
    $resp = '';
    $i = 0;
    foreach ($questions as $question) {
      $chkbox_code = '<input name="answer-options[]" class="answer-cb" id="a'.$i.'" type="checkbox"';
      if (isset($options[$i]) and $options[$i]) $chkbox_code .= ' checked="checked"';
      $resp .= $chkbox_code . '/><label for="a'.$i.'">' . $question . '</label><br>';
      $i++;
    }
    echo $resp;
    */
  }
  $task_start_date = array(
    0 => '2000-01-01',
    1 => '2012-01-01',
    2 => '2000-01-01',
    3 => '2012-01-01',
    4 => '2012-01-01',
    5 => '2012-01-01',
    6 => '2012-01-01',
    7 => '2012-01-01',
    8 => '2012-01-01',
    9 => '2012-01-01',
    10 => '2012-01-01'
  );
  $task_end_date = array(
    0 => '2000-12-31',
    1 => '2012-11-31',
    2 => '2000-12-31',
    3 => '2012-11-31',
    4 => '2012-11-31',
    5 => '2012-11-31',
    6 => '2012-11-31',
    7 => '2012-11-31',
    8 => '2012-11-31',
    9 => '2012-11-31',
    10 => '2012-11-31'
  );
  
  function get_actors($task_id) {
    $task_actors = array(
      0 => array(
            "Pete Sampras",
            "John McEnroe"
          ),
      1 => array(
            "Nitin Gadkari"
          ),
      2 => array(
            "Andre Agassi", 
            "Pete Sampras"
          ),
      3 => array(
            "Robert Vadra"
          ),
      4 => array(
            "Subodh Kant Sahay"
          ),
      5 => array(
            "P. Chidambaram",
            "Subramanian Swamy"
          ),
      6 => array(),
      7 => array(
            "Saina Nehwal"
          ),
      8 => array(),
      9 => array()
    );
    return $task_actors[$task_id];
  }
  function get_topics($task_id) {
    $task_topics = array(
      0 => array(),
      1 => array(),
      2 => array(),
      3 => array(),
      4 => array(),
      5 => array(),
      6 => array(
            "Science and Technology",
          ),
      7 => array(
            "Sport",
            "Badminton"
          ),
      8 => array(
            "Sexual Assault & Rape",
            "Crime"
          ),
      9 => array()
    );
    return $task_topics[$task_id];
  }
  
  function get_pattern($task_id) {
    $task_pattern = array(
      0 => '/<p>.*?<\/body>/',
      1 => '/.*/',
      2 => '/<p>.*?<\/body>/',
      3 => '/.*/',
      4 => '/.*/',
      5 => '/.*/',
      6 => '/.*/',
      7 => '/.*/',
      8 => '/.*/',
      9 => '/.*/',
      10 => '/.*/',
      11 => '/.*/'
    );
    return $task_pattern[$task_id];
  }
  function get_table_name($task_id) {
    $task_table = array(
      0 => 'tagdata',
      1 => 'hindu',
      2 => 'tagdata',
      3 => 'hindu',
      4 => 'corruption',
      5 => 'corruption',
      6 => 'hindu',
      7 => 'badminton',
      8 => 'rape',
      9 => 'petrol'
    );
    return $task_table[$task_id];
  }
?>
