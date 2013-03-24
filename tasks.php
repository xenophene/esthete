<?php
  $total_tasks = 10;
  // constants functions based on task
  
  function show_task_question($task_id) {
    
    $task_questions = array(
      0 => "Tennis in the year 2000"//"Find and mark relevant articles which evidence the following statement about Pete Sampras and John McEnroe."
      ,
      1 => "Nitin Gadkari's News Coverage in 2012"//"Find and mark relevant articles which verify the following statements involving <strong>Nitin Gadkari</strong>."
      ,
      2 => "Andre Agassi and Pete Samprass News Coverage"//"Find and mark relevant articles which lead you to conclude one of the following statements of the sports rivalry between Andre Agassi and Pete Sampras in the year 2000."
      ,
      3 => "Robert Vadra's News Coverage in 2012"//"Find and mark relevant articles which bring out the following facts about news featuring <strong>Robert Vadra</strong>"
      ,
      4 => "Subodh Kant Sahay News Coverage"//"Find and mark relevant articles which bring out the controversy sorrounding Subodh Kant Sahay and his brother SKS Ispat."
      ,
      5 => "News Coverage around Swamy and Chidambaram"//"Find and mark relevant articles which bring out the following facts regarding S. Swamy and P. Chidambaram."
      ,
      6 => "ISRO's News Coverage in 2012"//"Find and mark relevant articles for each of the following facts regarding recent developments in India's space program."
      ,
      7 => "Saina Nehwal's News Coverage in 2012"//"Find and mark relevant articles which bring out the following events involving Saina Nehwal last year."
      ,
      8 => "Rape Incidents and News Stories in 2012"//"Find and mark articles that capture the following facts regarding stories around rape cases in India"
      ,
      9 => "News Stories around Petrol prices in 2012"//"Find articles which portray how different sections of society have reacted to the frequent petrol price hikes in the past"
      ,
    );
    echo $task_questions[$task_id];
  }
  function show_task_options($task_id, $d) {
    $task_options = array(
      0 => array(
            'John McEnroe remarked that Sampras only played for money and personal pride over national glory'
          ),
      1 => array(
            'Nitin Gadkari and Narendra Modi have reported to be against each other.',
            'Nitin Gadkari played accusatory politics, blaming the government and highlighting corruption',
            'Nitin Gadkari was in news for developmental politics, campaigning on issues
            not related to corruption'
          ),
      2 => array(
            'Andre Agassi dominated Pete Sampras for all of the year',
            'Pete Samprass crossed Agassi in rankings in mid-year',
            'Agassi and Samprass were dominated by another player, and they did not play many matches together'
          ), 
      3 => array(
            'The controversy sorrounding Robert Vadra has been highlighted by various groups of society',
            'Robert Vadra has been in the news expressing desire to participate in elections',
            'Robert Vadra has not come forward to defend himself'
          ),
      4 => array(
"What is the nature of news coverage around Subodh Kumar Sahay?<br/>-- Politics<br/>-- Corruption<br/>",
"Was Subodh Kumar Sahay accused of influencing coal allocation through the PM?",
"Write a summary of the key actors and companies like SKS Ispat and what was the stand of the BJP, and the response by Congress"

        /*
            "Subodh Kant Sahay allegedly wrote a letter to PM recommending the illegal coal block allocation for his brother's company",
            "Subodh Kant Sahay resigned from the cabinet following the allegations and protests by the opposition",
            "The coal ministry immediately took action following the allegations and deallocated the coal-blocks of SKS Ispat"
            */
          ),
      5 => array(
            "Swamy sought review of the decision of special court by again moving the Supreme Court against Chidambaram",
            "The govt. openly defended Chidambram and called Swamy's accusations baseless.",
            "Supreme Court dismissed Swamy's plea for probe against Chidambaram."
          ),
      6 => array(
        "What was the name that ISRO had done that was under scrutiny and debate in early 2012?",
"Name the ISRO chief at the time of the controversial deal, and write a summary of the events around that time.",
"Write a summary of ISRO's 100th mission."
        /*
            "ISRO, India's space agency was caught in a scam related to the Defence sector",
            "ISRO was able to successfully launch its 100th PSLV, named SHAR",
            "Former ISRO chairmen got into a fued with the government"
            */
          ),
      7 => array(
            "Saina was in news for being gifted a BMW by Sachin Tendular",
            "Saina offered a token of appreciation to a fellow olympian for an inspiring victory",
            "Saina was honoured by the PM Manmohan Singh for her performance at the Olympics",
          ),
      8 => array(
        "Write a summary about the rape incidents reported from West Bengal during 2012. What were some reactions from WB CM towards these incidents",
"Can you find news reports where women were reported to have killed men in response to them trying to attacking/raping them?",
"Write a summary about the events sorrounding Pascal Mazurier during
the year 2012."
/*
            "Incidents of rape cases have gone up over the past year",
            "Rape cases in the state of West Bengal have been dealth with harshly",
            "Majority of politicians have openly come out and held women partly responsible in a rape case"
            */
          ),
      9 => array(
              "The opposition has regularly called and implemented a nation bandh",
              "The state governments have on some occasions lowered the taxes on fuel"
          )
    
    );

    $questions = $task_options[$task_id];
    echo '<ol><li>' . implode($questions, '</li><li>') . '</li></ol>';
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
    1 => '2012-12-31',
    2 => '2000-12-31',
    3 => '2012-12-31',
    4 => '2012-12-31',
    5 => '2012-12-31',
    6 => '2012-12-31',
    7 => '2012-12-31',
    8 => '2012-12-31',
    9 => '2012-12-31',
    10 => '2012-12-31'
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
  function get_clusters($task_id) {

    $task_clusters = array(
      1 =>  array(array(1028),array(1206,1207),array(1553,1435),array(1158,1116,1180,1061,1012,1022,1031),array(658),array(616,617,628),array(549,688,544,539,672,674),array(687,689,550,596,579,576,588,612,613,436,432,469,413,422),array(725,726),array(1128,1170,1368),array(461,466,318,458,288,457,293,309,291,299,304,307),array(871,985,818,824,852,866,831,869,908,1034,874,885),array(426),array(1182,1185,1009,1048,967,990),array(1309,1300,1284,1303,1295,1251,1262,1213,1223,1226,1099,1205,1374,1375),array(511),array(232,337,335,336),array(821,813,814,775,844,735,692,894,910,919),array(1493,1505,1313,1515,1436,1442,1469,1571,1497,1487,1511,1512,1519,1641,1718,1724,1741,1572,1737),array(492,1710,1717,1704,1627,1611,1629,1625,1631,1610,1746,1748,1707,1683,1663,1676,1679,1712,1701,1702,1664,1684,1672,1646,1657,1670,1668,1671,1675,1649,1686,1622,1644)
      ),
      3 =>  array(
array(3853,3693,3846,3848,3681,3679,3836,3675),array(3757,3763,3772),array(2633),array(3788,3807,2642),array(3726,3735,3778,3759,3762),array(3737,3786),array(3833,3839,3834,3825,3842,3843,3845,3840,3850),array(2644,3813,3799,2649),array(3736,3717,3721,3727,3729,3741,3755,3756),array(3854,3676,3847,3680,3690,3684,3702,3699,3856,3709)
      ),
      8 =>  array(
      ),
      9 =>  array(
array(72),array(412,414),array(316),array(394,420,387,390,388,389),array(143,149,182,275),array(197,198,132,166,169),array(97,98,101),array(120,122,123,126,138,130,142),array(186,188,175,180,178,151,117,124),array(241,199,133,134),array(246,280,254,253,274),array(399,352)
      ),
    );
    return isset($task_clusters[$task_id]) ? $task_clusters[$task_id] : array();
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
      9 => array(
            "Petrol"
          )
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
      3 => 'corruption',
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
