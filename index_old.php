<?php
/*
 * sets up the main view of ESTHETE. for a given actor, show the
 * actors co-occuring and the topics that are determined
 * use the MIT Timeline code, to set up the queried actors with the
 * times in the year 2000
 */

/*
 * TO DO
 * 1. define a function from interaction score to the color intensity
 * of the tape representing that interaction - DONE
 * 2. don't show gaps, show the longest first, give fixed colors but
 * show long tapes - DONE
 * 3. Filter on time. Start and end to show interaction only in that period
 * 4. Show the graph for any interaction - DONE
 * 5. Topics as headlines of the most relevant periods - DONE
 * 
 *
 * Now, 1-day interactions are all there are. We calculate all tranformations
 * on a daily basis by averaging out the score of the same transformations that
 * appeared together. Now, we will weight each of these 1-day interactions by
 * opacity.
 */
include('config.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>ESTHETE</title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<script src="src/webapp/api/timeline-api.js?bundle=true" type="text/javascript"></script>
<script src="jquery.min.js" type="text/javascript"></script>
<script src="jquery-ui-min.js" type="text/javascript"></script>
<script src="jquery.tools.range.min.js" type="text/javascript"></script>
<script src="script.js" type="text/javascript"></script>
<?php
/*
 * we query the db for the actors relevant to this actor and place
 * then in a json object variable for js
 * start in the db corresponds to the days from January 1 2000
 * so we need to convert this into that day
 */
// allactors is the array of all the actors that have been preprocessed
// to map their related actors and the topics
function rand_colorCode() {
  $r = mt_rand(0,180); // generate the red component
  $g = mt_rand(0,180); // generate the green component
  $b = mt_rand(0,180); // generate the blue component
  $rgb = $r.','.$g.','.$b;
  return $rgb;
}
$allactors = array('George W. Bush', 'Clinton', 'Al Gore', 'Dick Cheney', 'Andre Agassi', 'New York Knicks', 'Baltimore Orioles', 'Venus Williams', 'New York Yankees', 'Fred Rodriguez', 'J. K. Rowling');

if (array_key_exists('actor', $_POST) and $_POST['actor'] != '')
  $actor_name = ucwords($_POST['actor']);
else
  $actor_name = 'George W. Bush';

if (array_key_exists('percentage', $_POST))
  $score_cutoff = $_POST['percentage'];
else
  $score_cutoff = 0.6;

/* use these dates to crop the view of the timeline & 
  calculate the important interactions within this timeframe*/
$date = strtotime("1.1.2000");
if (array_key_exists('from', $_POST) and $_POST['from'] != '') {
  $start_ts = strtotime($_POST['from']);
  $end_ts = strtotime($_POST['to']);
  $startingdate = round(($start_ts - $date) / 86400);
  $endingdate = round(($end_ts - $date) / 86400);
  $fmonth = explode('.', $_POST['from']);
  $fmonth = $fmonth[1] - 1;
  $fday = explode('.', $_POST['from']);
  $fday = $fday[0];
  $tmonth = explode('.', $_POST['to']);
  $tmonth = $tmonth[1] - 1;
  $tday = explode('.', $_POST['to']);
  $tday = $tday[0];
}
else {
  $startingdate = 0; // in days
  $endingdate = 366; // in days
  $fmonth = 0;
  $tmonth = 11;
  $fday = 1;
  $tday = 31;
}
$actor_int = array(); // array to have per actor interaction scores to take the largest at the end
$actor_times = array(); // array of 10-day slabs to store the # of continues in this slab
$times_headlines = array(); // array indexed by 10-day slabs store the prominent headlines of the slab
$actor_trans = array(); // array to have per actor interaction durations

for ($i = 0 ; $i < 40 ; $i++) {
  array_push($actor_times, 0);
  array_push($times_headlines, '');
}

$actor_info = 'Interaction threshold = '.$score_cutoff;

$rows = array(); // just the rows returned by SQL
$jsobj = array('events' => array());

$query = "SELECT * FROM actor WHERE actor1='".strtolower($actor_name)."'";
$result = mysql_query($query);

// stores all the actors into the rows array, and sets up the actor wise
// interaction scores and timeperiods so that the interaction on the timeline
// are shown in order of actor-strengths
for ($i = 0 ; $i < mysql_num_rows($result) ; $i++) {
  $actors = mysql_fetch_row($result);
  array_push($rows, $actors);
  if ($actors[0] == strtolower($actor_name))
    $otheractor = $actors[1];
  else
    $otheractor = $actors[0];
  if (array_key_exists($otheractor, $actor_trans))
    array_push($actor_trans[$otheractor], $actors);
  else
    $actor_trans[$otheractor] = array($actors);
}
$jrows = array();
$trackNumId = array();
$id = 2; // the track ID allocator
$highest_score = 0; // the highest interaction in all
// put_actor loses its significance now
foreach ($actor_trans as $actor => $trans) {
  $put_actor = true;
  $high_score = 0;
  for ($i = 0 ; $i < sizeof($trans) ; $i++) {
    if ($high_score < $trans[$i][2])
      $high_score = $trans[$i][2];
  }
  // if all interactions less than score_cutoff ignore this actor
  if ($highest_score < $high_score)
    $highest_score = $high_score;
  /* this code has simplified a lot since now we will only add a special
     type of GAP event in between end of previous and start of next
     iterate over trans for every actor, and add the events as well as
     the GAP events into $jrows */
  if (!array_key_exists($actor, $trackNumId) and $high_score > $score_cutoff) {
    $trackNumId[$actor] = array();
    array_push($trackNumId[$actor], $id);
    array_push($trackNumId[$actor], false);
    array_push($trackNumId[$actor], rand_colorCode());
    $id++;
  }
  if ($high_score > $score_cutoff) {
    for ($i = 0 ; $i < sizeof($trans) - 1 ; $i++) {
      array_push($jrows, $trans[$i]);
      if ($trans[$i][4] < $trans[$i + 1][3] - 1)
        array_push($jrows, array($trans[$i][0], $trans[$i][1], "100", $trans[$i][4] + 1, $trans[$i + 1][3] - 1));
    }
    array_push($jrows, $trans[sizeof($trans) - 1]);
  }
}
//echo json_encode($trackNumId);
//echo json_encode($jrows);
for ($i = 0 ; $i < sizeof($jrows) ; $i++) {
  $actors = $jrows[$i];
  if ($actors[3] < $startingdate or $actors[4] > $endingdate)
    continue;
  $score = $actors[2];
  // map the number of days from Jan 1 in month than day
  $elem = array();
  $elem['start'] = date('F j Y', strtotime("+".$actors[3]." days", $date));
  $elem['end'] = date('F j Y', strtotime("+".($actors[4] + 1)." days", $date));
  if (!$trackNumId[$actors[1]][1]) {
    $trackNumId[$actors[1]][1] = true;
    $elem['title'] = $actors[1];
  }
  else
    $elem['title'] = '';
  if ($actors[2] == "100")
    $elem['color'] = 'rgba(0,0,0,0.1)';
  else { // determine the color from the intensity
    $intensity = $score / $highest_score;
    $elem['color'] = 'rgba('.$trackNumId[$actors[1]][2].','.$intensity.')';
  }
  /* here we need to append to the description, the start and end dates of
     this ongoing slab. for this, we find another element in elem at the same
     trackNum which has end  as this end - 1 */
  /* i think it would be better and *easier* if we just showed the graph from
     5 days before and ahead of the current time point*/
  $startdate = $actors[3];
  /*
  foreach ($jsobj['events'] as $e) {
    $d = explode(':', $e['caption']);
    if ($e['trackNum'] == $trackNumId[$actors[1]][0]) {
      if ($d[1] == $actors[4] - 1) {
        $startdate = $d[0];
        break;
      }
    }
  }
  */
  $elem['caption'] = $startdate;
  $elem['description'] = $actors[1];
  $elem['trackNum'] = $trackNumId[$actors[1]][0];
  $elem['textColor'] = '#000';
  // to label this interaction, we need to get all the articles common
  // to both these actors $actors[1] and $actors[0] at the time slabs
  // start to end, and keep adding these as part of description
  // this will require to somehow join the filename on the actor pair
  // or create the table in the actor-pair form. will require some thoughts, 
  // come back later
  array_push($jsobj['events'], $elem);
  // update $actor_int
  if ($actors[0] == strtolower($actor_name))
    $otheractor = $actors[1];
  else
    $otheractor = $actors[0];
  if (array_key_exists($otheractor, $actor_int))
    $actor_int[$otheractor] += $actors[2];
  else
    $actor_int[$otheractor] = $actors[2];
  // put this transformation in the appropriate 10-day bin of array_times
  // for this time-period $actors[3]/10, also add the headlines seen in this
  // period into a 10-day bin indexed JS array
  $actor_times[$actors[3] / 10] += $actors[2];
}
arsort($actor_int);
// iterate over jsobj and reassign the trackNum according to the sorted scores
foreach($jsobj['events'] as $i => $elem) {
  //$a = explode(' ', $elem['description']);
  $a = $elem['description'];
  $tid = array_search($a, array_keys($actor_int)) + 2; // this is the trackNumId
  $jsobj['events'][$i]['trackNum'] = $tid;
}
// loop over the time periods and show the appropriate article headline
// for this actor. this can later be changed to a lda based scheme
$query = "SELECT * FROM articlemap WHERE actor='".strtolower($actor_name)."'";
$q2 = "SELECT * FROM eventtags WHERE actor='".strtolower($actor_name)."'";
$result = mysql_query($query);
$r2 = mysql_query($q2);
$datemapping = array(); //associative array to keep track of which dates dealt with
for ($i = 0 ; $i < mysql_num_rows($result) ; $i++) {
  $mapping = mysql_fetch_row($result);
  $m2 = mysql_fetch_row($r2);
  // create the url link of $mapping[2]
  $url = 'file:///media/c/Users/xenoph/Desktop/A%20New%20Life/WorkStuff/SemVIII/CSD750/2000/summarized_html/'.$mapping[2];
  // look for $mapping[2] in the eventtag table and get the meaningful events if any from Calais
  // place these alongside the article headlines. only for US people
  $tags = array('','','','');
  if (in_array(strtolower($actor_name), array('bill clinton', 'george w. bush', 'dick cheney', 'al gore'))) {
    $tags = $m2; // hopefully is just one
  }
  $headlines = explode('/', $mapping[2]);
  $headline = $headlines[3];
  $headline = str_replace('-', ' ', $headline);
  $headline = str_replace('.html', '', $headline);
  //$headline = $headline.'<br/><b>'.$tags[3].'</b>';
  if (array_key_exists($mapping[1], $datemapping))
    //$datemapping[$mapping[1]] = $datemapping[$mapping[1]].'<li><a href="'.$url.'" target="_blank">'.$headline.'</a></li>';
    $datemapping[$mapping[1]] = $datemapping[$mapping[1]].'<li>'.$headline.'</li>';
  else
    $datemapping[$mapping[1]] = '<li>'.$headline.'</li>';
}
// now go about defining and appending these headline events
foreach ($datemapping as $startdate => $desc) {
  $elem = array();
  if ($startdate < $startingdate or $startdate > $endingdate)
    continue;
  $elem['start'] = date('F j Y', strtotime("+".$startdate." days", $date));
  $elem['title'] = '';
  $elem['description'] = $desc;
  $elem['trackNum'] = 1;
  array_push($jsobj['events'], $elem);
  $times_headlines[$startdate / 10] = $times_headlines[$startdate / 10] . $desc; 
}
echo '<script>
var data = ' . json_encode($jsobj) . ';
var headlines = ' . json_encode($times_headlines) . ';
var mainactor = ' . json_encode(strtolower($actor_name)). ';
</script>';
?>

<script>
/*
 * this script sets up the data received from server onto the timeline
 */
var tl;
$(function () { //ready function
  var tl_el = document.getElementById("tl");
  var eventSource1 = new Timeline.DefaultEventSource();
  
  var theme1 = Timeline.ClassicTheme.create();
  theme1.autoWidth = true; // Set the Timeline's "width" automatically.
                           // Set autoWidth on the Timeline's first band's theme,
                           // will affect all bands.
  theme1.event.tape.height = 8;
  theme1.event.bubble.height = 300;
  theme1.event.track.height = 15;
  theme1.timeline_start = new Date(Date.UTC(2000, <?php echo $fmonth;?>, <?php echo $fday;?>));
  theme1.timeline_stop  = new Date(Date.UTC(2001, <?php echo $tmonth;?>, <?php echo $tday;?>));
  var d = Timeline.DateTime.parseGregorianDateTime("July 1 2000")
  var bandInfos = [
      Timeline.createBandInfo({
          width:          45, // set to a minimum, autoWidth will then adjust
          intervalUnit:   Timeline.DateTime.MONTH, 
          intervalPixels: 100,
          eventSource:    eventSource1,
          zoomIndex:      10,
          zoomSteps:      new Array(
            {pixelsPerInterval: 280,  unit: Timeline.DateTime.HOUR},
            {pixelsPerInterval: 140,  unit: Timeline.DateTime.HOUR},
            {pixelsPerInterval:  70,  unit: Timeline.DateTime.HOUR},
            {pixelsPerInterval:  35,  unit: Timeline.DateTime.HOUR},
            {pixelsPerInterval: 400,  unit: Timeline.DateTime.DAY},
            {pixelsPerInterval: 200,  unit: Timeline.DateTime.DAY},
            {pixelsPerInterval: 100,  unit: Timeline.DateTime.DAY},
            {pixelsPerInterval:  50,  unit: Timeline.DateTime.DAY},
            {pixelsPerInterval: 400,  unit: Timeline.DateTime.MONTH},
            {pixelsPerInterval: 200,  unit: Timeline.DateTime.MONTH},
            {pixelsPerInterval: 100,  unit: Timeline.DateTime.MONTH} // DEFAULT zoomIndex
          ),
          date:           d,
          theme:          theme1,
          layout:         'original'  // original, overview, detailed
      })
  ];
  
  // create the Timeline
  tl = Timeline.create(tl_el, bandInfos, Timeline.HORIZONTAL);
  
  var url = '.'; // The base url for image, icon and background image
                 // references in the data
  eventSource1.loadJSON(data, url); // The data was stored into the 
                                   // data variable.
  tl.layout(); // display the Timeline
  $(":range").rangeinput();
  Timeline.OriginalEventPainter.prototype._showBubble = function(x, y, evt) {
    var month = new Array();
    month[0] = "Jan";
    month[1] = "Feb";
    month[2] = "Mar";
    month[3] = "Apr";
    month[4] = "May";
    month[5] = "Jun";
    month[6] = "Jul";
    month[7] = "Aug";
    month[8] = "Sept";
    month[9] = "Oct";
    month[10] = "Nov";
    month[11] = "Dec";
    var oneDay = 24*60*60*1000;	// hours*minutes*seconds*milliseconds
    var firstDate = new Date(2000,0,1);
    var endDate = evt.getEnd().toString().split(' ');
    var mm = month.indexOf(endDate[1]);
    var secondDate = new Date(2000, mm, endDate[2]);
    var diffDays = Math.floor((secondDate.getTime() - firstDate.getTime())/(oneDay));
    // here send the Ajax request for the 5 days on either side
    var l = diffDays > 3 ? diffDays - 3 : 1;
    var h = diffDays < 362 ? diffDays + 3 : 365;
    var request = $.ajax({
      url: 'temp.php',
      type: 'POST',
      data: {start: l, end: h, actors:[mainactor, evt.getDescription()]}
    });
    request.done(function (msg) {
      //alert(msg);
      var dt = new Date().toString();
      //var URLToOpen =  'test_svg.html?TimeStamp=' + dt;
      //window.open(URLToOpen, 'window');
      //window.open("./test_svg.svg",'window');
      dirtypop(jQuery.parseJSON(msg));
    });
    request.fail(function(jqXHR, textStatus) {
      alert( "Request failed: " + textStatus );
    });
  }
  //setupFilterHighlightControls(document.getElementById("controls"), tl, [0], theme1);
  $('.actor-link').click(function() {
    performFiltering(tl, [0], $(this).attr('title'));
  });
  $('.times').click(function() {
    $('.times').each(function() {
      $(this).css('color', '#333');
    });
    var slab = $(this).attr('title');
    $('#mrt .header').html(headlines[slab]);
    $(this).css('color', '#B18922');
  });
  //$('#tl').attr('style', 'height:400px');
});
</script>
<script>
function dirtypop(msg)
{
  var generator=window.open('','name','height=600,width=1100,zoom=4');
  generator.document.write(msg);
  generator.document.close();
}
</script>
<link href="jquery-ui.css" rel="stylesheet"/>
<link rel="stylesheet" href="includes/jquery.multiselect.filter.css"/>
<link rel="stylesheet" href="includes/jquery.multiselect.css"/>
<link href="style.css" rel="stylesheet"/>
</head>
<body>
<!--<center><h1>ESTHETE: Exploring newS THrough Entity-Topic Extraction</h1></center>-->
<div id="header">
  <div id="info">
    <h2><?php echo $actor_name;?></h2>
    <!--<p><?php echo $actor_info; ?>-->
    <form action="" method="POST">
    <select id="actor" name="actor">
    <?php
      foreach ($allactors as $actor) {
        if ($actor == $actor_name)
          echo '<option value="'.$actor.'" selected="selected">'.$actor.'</option>';
        else
          echo '<option value="'.$actor.'">'.$actor.'</option>';
      }
    ?>
    </select><br/>
    <!--<input type="text" id="actor" name="actor" value="<?php echo $actor_name;?>" placeholder="<?php echo $actor_name;?>" autocomplete="off"/><br/>-->
    <table id="bar-table"><tr>
		<td width="70px">Weak interactions</td><td> <input id="percentage" max="1" min="0" name="percentage" type="range" value="<?php echo $score_cutoff;?>" step="0.02" /></td><td width="70px">Strong Interactions</td>
		</tr>
		<label for="from">From</label>
    <input type="text" id="from" name="from" autocomplete="off" value="<?php if (array_key_exists('from', $_POST) and $_POST['from'] != '') echo $_POST['from'];?>"/>
    <label for="to">to</label>
    <input type="text" id="to" name="to" autocomplete="off" value="<?php if (array_key_exists('to', $_POST) and $_POST['to'] != '') echo $_POST['to'];?>"/>
		</table>
		<input type="submit" value="Show Map"/>
    </form>
	  <div class="controls" id="controls"></div>
  </div>
  <div id="mra">
    <h3>Most relevant actors</h3>
    <ul class="header">
    <?php
      // set up the most important actors as the ones with the most interaction score
      $c = 0;
      foreach ($actor_int as $a => $v) {
        echo '<b><a title="'.$a.'" class="actor-link"><li style="color:rgb('.$trackNumId[$a][2].');">'.$a.'</li></a></b>';
        $c++;
      }
    ?>
    </ul>
  </div>
  <div id="mrt">
    <h3>Most relevant topics</h3>
    <!--Shown as the most important headlines in each of the clicked time period-->
    <ul class="header">
    </ul>
  </div>
  <div id="mrp">
    <h3>Most relevant time period</h3>
    <ul class="header">
    <?php
      // set up the most important timeperiod as the one with the most interaction
      arsort($actor_times);
      $c = 0;
      // $a is the index to the headlines JS array
      foreach ($actor_times as $a => $v) {
        if (10 * $a >= $startingdate and 10 * $a <= $endingdate) {
          $e = date('F j', strtotime("+".($a * 10)." days", $date));
          $f = date('F j', strtotime("+".($a * 10 + 10)." days", $date));
          echo '<b><li><a class="times" title="'.$a.'">'.$e.' - '.$f.'</a></li></b>';
          $c++;
          if ($c == 8)
            break;
        }
      }
    ?>
    </ul>
  </div>
</div>
<div id="tl" style="height: 600px;"></div>
</body>
</html>
