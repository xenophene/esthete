/*
 * this script sets up the data received from server onto the timeline
 */
var tl
  ,	monthNames = [ "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December" ]
  , saveReply;

function get_month_code(mon) {
  switch(mon) {
    case 'Jan': return 0;
    case 'Feb': return 1;
    case 'Mar': return 2;
    case 'Apr': return 3;
    case 'May': return 4;
    case 'Jun': return 5;
    case 'Jul': return 6;
    case 'Aug': return 7;
    case 'Sep': return 8;
    case 'Oct': return 9;
    case 'Nov': return 10;
    case 'Dec': return 11;
	default:	return 0;
  }
}

$(function () { //ready function
  var currScroll = 0;
  
  /*Ref: http://code.google.com/p/simile-widgets/wiki/Timeline_EventSources */
  var tl_el = document.getElementById("tl");
  var eventSource1 = new Timeline.DefaultEventSource(0);
  
  var theme1 = Timeline.ClassicTheme.create();
  theme1.autoWidth = true; // Set the Timeline's "width" automatically.
                           // Set autoWidth on the Timeline's first band's theme,
                           // will affect all bands.
  theme1.event.track.autoWidthMargin = 1;
  theme1.event.tape.height = 8;
  //theme1.event.bubble.height = 300;
  theme1.event.track.height = 15;
  //theme1.event.instant.icon = "no-image-40.png";
  //theme1.event.instant.iconWidth = 40;  // These are for the default stand-alone icon
  //theme1.event.instant.iconHeight = 40;

  theme1.event.highlightColors[0] = '#FFFF00';
  theme1.timeline_start = new Date(Date.UTC(fyear, fmonth, fday));
  theme1.timeline_stop  = new Date(Date.UTC(parseInt(tyear, 10), tmonth, tday));
  var myear = fmonth >= 6 ? (parseInt(fyear, 10) + 1) : parseInt(fyear, 10);
  var d = Timeline.DateTime.parseGregorianDateTime(monthNames[(fmonth + 6) % 12] + " 1 " + myear);
  var bandInfos = [
    Timeline.createBandInfo({
      width:          45, // set to a minimum, autoWidth will then adjust
      intervalUnit:   Timeline.DateTime.MONTH, 
      intervalPixels: 100,
      eventSource:    eventSource1,
      /*
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
      /*
      eventPainter:   Timeline.CompactEventPainter,
      eventPainterParams: {
          iconLabelGap:     5,
          labelRightMargin: 20,
          
          iconWidth:        80, // These are for per-event custom icons
          iconHeight:       80,
          
          stackConcurrentPreciseInstantEvents: {
              limit: 5,
              moreMessageTemplate:    "%0 More Events",
              icon:                   "no-image-80.png", // default icon in stacks
              iconWidth:              80,
              iconHeight:             80
          }
      },
      */
      date:           d,
      theme:          theme1,
      layout:         'original'  // original, overview, detailed
    })
    /*,
    Timeline.createBandInfo({
      width:          "10%", 
      intervalUnit:   Timeline.DateTime.MONTH, 
      intervalPixels: 100,
      eventSource:    eventSource1,
      date:           d,
      theme:          theme1,
      layout:         'overview'  // original, overview, detailed
    })
    */

  ];
  //bandInfos[1].syncWith = 0;
  //bandInfos[1].highlight = true;

  // create the Timeline
  tl = Timeline.create(tl_el, bandInfos, Timeline.HORIZONTAL);
  
  var url = '.'; // The base url for image, icon and background image
                 // references in the data
  eventSource1.loadJSON(data, url); // The data was stored into the 
                                   // data variable.
  tl.layout(); // display the Timeline
  // show the date selector
  var dates = $( "#fd, #td" ).datepicker({
    minDate: new Date(fyear, 0, 1),
    maxDate: new Date(fyear + 1, 0, 1),
    dateFormat: 'yy-mm-dd',
    defaultDate: new Date(fyear, 0, 1),
    changeMonth: true,
    numberOfMonths: 1,
    beforeShow: function(input, inst) {
      inst.dpDiv.css({
        marginTop: (-input.offsetHeight - 50) + 'px', 
        marginLeft: (-input.offsetWidth - 70) + 'px',
        'font-size': '14px'
      });
    },
    onSelect: function( selectedDate ) {
      var option = this.id == "fd" ? "minDate" : "maxDate",
	      instance = $( this ).data( "datepicker" ),
	      date = $.datepicker.parseDate(
		      instance.settings.dateFormat ||
		      $.datepicker._defaults.dateFormat,
		      selectedDate, instance.settings );
      dates.not( this ).datepicker( "option", option, date );
    }
  });
  
  // show the actor topic multiselect filter
  $('#actor-filter').multiselect({
    selectedList: 10,
    noneSelectedText: "Select Actor Tags"
  }).multiselectfilter({
    label: 'Filter on...',
    placeholder: 'Actor Names'
  });
  
  $('#topic-filter').multiselect({
    selectedList: 10,
    noneSelectedText: "Select Topic Tags"
  }).multiselectfilter({
    label: 'Filter on...',
    placeholder: 'Topics'
  });
  setInterval(function () {
    var m = parseInt($('#minutes').html(), 10);
    var s = parseInt($('#seconds').html(), 10);
    if (s < 59) {
      s++;
      var nt = (s < 10 ? "0" : "") + s;
      $('#seconds').html(nt);
      $('#timer-value').val(m+':'+nt);
    } else {
      m++;
      $('#seconds').html('00');
      $('#minutes').html(m);
      $('#timer-value').val(m+':00');
    }
  }, 1000);
  /*
  $('.topic-include').hover(function () {
    performFiltering(tl, [0], $(this).html());
  }, function () {
    performFiltering(tl, [0], '');
  });
  */
  $('.topic-include').click(function () {
    var tag = $(this).text().replace('&amp;', '&');
    
    $(this).toggleClass('highlight');
    var e = $("#topic-filter option[value='"+$.trim(tag.toLowerCase())+"']");
    if (e.attr("selected") == "selected") {
      e.removeAttr("selected");
    } else {
      e.attr("selected", "selected");
    }
    $('#topic-filter').multiselect('refresh');
  });
  
  function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
  }
  
  function performFiltering(timeline, bandIndices, regex) {
    var regexes = [];
    var hasHighlights = regex != "";
    regexes.push(new RegExp(regex, "i"));
    var highlightMatcher = hasHighlights ? function(evt) {
        var text = evt.getText();
        var description = evt.getDescription();
        for (var x = 0; x < regexes.length; x++) {
            var regex = regexes[x];
            if (regex != null && (regex.test(text) || regex.test(description))) {
                return 0;
            }
        }
        return -1;
    } : null;
    for (var i = 0; i < bandIndices.length; i++) {
        var bandIndex = bandIndices[i];
        timeline.getBand(bandIndex).getEventPainter().setHighlightMatcher(highlightMatcher);
    }
    timeline.paint();
  }
  var oldOnClickInstantEvent = Timeline.OriginalEventPainter.prototype._onClickInstantEvent;
  Timeline.OriginalEventPainter.prototype._onClickInstantEvent = function (icon, domEvt, evt) {
    this._showBubble(0, 0, evt);
    this._fireOnSelect(evt.getID());
    
    domEvt.cancelBubble = true;
    SimileAjax.DOM.cancelEvent(domEvt);
    return false;
  };
  
  var oldOnClickDurationEvent = Timeline.OriginalEventPainter.prototype._onClickDurationEvent;
  var oldFillInfoBubble = Timeline.DefaultEventSource.Event.prototype.fillInfoBubble;
  var oldShowBubble = Timeline.OriginalEventPainter.prototype._showBubble;
  function parseDate(d) {
    d = d.split(' ');
    var date = new Date();
    date.setDate(d[2]);
    date.setFullYear(d[3]);
    date.setMonth(get_month_code(d[1]));
    return date;
  }
  function showModal(headercode, bodycode) {
	var mheader = $('#modal-bubble .modal-header');
	headercode = '<a class="close" data-dismiss="modal" aria-hidden="true"> \
                  &times;</a>' + headercode;
	mheader.html(headercode);
	
	var mbody = $('#modal-bubble .modal-body');
	mbody.html(bodycode);
	
	$('#modal-bubble').modal('show');
  }
  Timeline.OriginalEventPainter.prototype._showBubble = function (x, y, evt) {
	if (!evt._description) return;
    var desc = evt._description.split('|');
    
    var sdate = parseDate(evt._start.toString());
    var edate = parseDate(evt._end.toString());
    var date = sdate.getDate() + ' ' + monthNames[sdate.getMonth()];
    date += ' - ' + edate.getDate() + ' ' + monthNames[edate.getMonth()];
    var headercode = '';
	
	if (desc[1] == '') {
	  
	  var gtopics = [];
	  $('#topic-filter option:selected').each(function() {gtopics.push($(this).val())});
	  desc[1] = gtopics.join('^');
	}
	if (desc[0] == '') {
	  var gactors = [];
	  $('#actor-filter option:selected').each(function() {gactors.push($(this).val())});
	  desc[1] = gactors.join('^');
	}
    headercode += '<strong><u>Sub-Actors</u></strong>: ' + desc[0].split('^').map(toTitleCase).join(', ');
	headercode += '<br><strong><u>Sub-Topics</u></strong>: ' + desc[1].split('^').map(toTitleCase).join(', ');
	var headlines = '<em>Articles relevant to this theme (Click to read full):</em><ol>';
	
    var descs = desc[2].toString();
    descs = descs.split('^');
    for (var i = 0; i < descs.length; i++) {
      var t = descs[i];
      headlines += '<li rel="tooltip" class="evt-call" \
                    name="' + t + '"><a title="Click to read this article">' +
                    article_identifier[t] + '</a></li>';
    }
    headlines += '</ol>';
	
	showModal(headercode, headlines);
    $('.add-actor').click(function () {
      var e = $("#actor-filter option[value='"+$.trim($(this).attr('id'))+"']");
      if (e.attr("selected") == "selected") {
        e.removeAttr("selected");
      } else {
        e.attr("selected", "selected");
      }
      $('#actor-filter').multiselect('refresh');
    });
    $('.evt-call').each(function () {$(this).children('a').tooltip();});
    $('.evt-call').click(showArticles);
  };
  
  $('#study-interaction').click(function() {
	if (!google.visualization) return;
	var tabs = '<div class="tabbable"><ul id="interaction-tabs" class="nav nav-tabs">';
	tabs += '<li class="active"><a href="#t1">Interaction Strength</a></li> \
			 <li><a href="#t2">Topic Hierarchy</a></li></ul>';
	
	
	tabs += '<div class="tab-content"> \
			 <div class="tab-pane active" id="t1"><div id="i-s"></div></div> \
			 <div class="tab-pane" id="t2"><div id="t-h"></div></div> \
			 </div></div>';
	
	showModal('<h3>Interactions</h3>', tabs);
	
	$('#interaction-tabs a').click(function(e) {
	  e.preventDefault();
	  $(this).tab('show');
	});
	
    // plot the appropriate charts
	
	if (!saveReply) {
	  var aids = Object.keys(article_identifier);
	  $.ajax({
		url: 'ajax_scripts.php',
		method: 'GET',
		dataType: 'json',
		data: {
		  'fid': '5',
		  'aids': aids,
		  'tid': task_id
		},
		success: plotCharts
	  });
	} else plotCharts(saveReply);
  });
  function assoc_array_sort(obj) {
	var tuples = [];
	for (var key in obj) tuples.push([key, obj[key]]);
	tuples.sort(function(a, b) {
	  a = a[1];
	  b = b[1];
	  return a < b ? 1 : (a > b ? -1 : 0);
	});
	var keys = [];
	for (var i = 0; i < tuples.length; i++) {
	  keys.push(tuples[i][0]);
	}
	return keys;
  }
  function plotCharts(reply) {
	saveReply = reply;
	// construct the first plot
	// -- monthly-bin wise frequency of articles
	// get the year
	if (reply.length) var year = reply[0][2].split('-')[0];
	var monthBins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
	  , monthTopics = [];
	
	for (k in reply) {
	  var amonth = parseInt(reply[k][2].split('-')[1], 10) - 1;
	  monthBins[amonth]++;
	  var topics = reply[k][0].split(';');
	  if (!monthTopics[amonth])	monthTopics[amonth] = [];
	  for (var i = 0; i < topics.length; i++) {
		var topic = topics[i];
		if (!monthTopics[amonth][topic]) monthTopics[amonth][topic] = 1;
		else monthTopics[amonth][topic]++;
	  }
	}
	var dataTable = new google.visualization.DataTable();
	dataTable.addColumn('string', 'Month');
	dataTable.addColumn('number', 'Article Count');
	dataTable.addColumn({type: 'string', role: 'tooltip'});
	var is_data = [];
	for (m in monthBins) {
	  var tooltip = monthTopics[m] ? assoc_array_sort(monthTopics[m]).join(', ') : '';
	  is_data.push([monthNames[m], monthBins[m], tooltip]);
	}
	dataTable.addRows(is_data);
	var options = {
	  'title': 'Interaction Influence over time'
	};
	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.LineChart(document.getElementById('i-s'));
	chart.draw(dataTable, options);
	
	var count_array = [];
	for (var k in levels) {
	  if (!count_array[k]) count_array[k] = 1;
	  else count_array[k]++;
	  for (var l in levels[k]) {
		var lkey = levels[k][l];
		if (!count_array[lkey]) count_array[lkey] = 1;
		else count_array[lkey]++;
	  }
	}
	level1 = [];
	for (var k in count_array) {
	  if (count_array[k] == 1 && levels[k]) level1.push(k);
	}
	// show the table
	var table = '<table class="table table-striped"><thead><tr><th>Main Topic</th><th>Sub Topics</th></thead><tbody>';
	for (var l in level1) {
	  table += '<tr><td>' + level1[l] + '</td><td>' + levels[level1[l]].join(', ') + '</td></tr>';
	}
	table += '</tbody></table>';
	$('#t-h').html(table);
  }
  
  function getLoadingImg() {
	return '<ul class="loading"><li><img src="loading3.gif" alt="Loading" title="Loading"/></li></ul>';
  }
  
  $('#show-all-articles').click(function() {
	var headercode = '<h3>All Articles</h3>';
	
	var headlines = '<ol>';
	for (var ai in article_identifier) {
      headlines += '<li rel="tooltip" class="evt-call" \
                    name="' + ai + '"><a title="Click to read this article">' +
                    article_identifier[ai] + '</a></li>';
    }
	headlines += '</ol>';
	showModal(headercode, headlines);
	$('.evt-call').each(function () {$(this).children('a').tooltip();});
  $('.evt-call').click(showArticles);
	
  });
  function showArticles() {
	var id = $(this).attr('name');
	console.log(id);
	if (!$(this).attr('called')) {
	  $(this).append(getLoadingImg());
	  $.ajax({
			'url': 'ajax_scripts.php',
			'method': 'GET',
			'data': {
				'fid': '1',
				'aid': id,
				'task_id': task_id
			},
			'success': function (data) {
				$('.loading').remove();
				data = $.parseJSON(data);
				var elm = $('.evt-call[name="' + data[0] + '"]');
				elm.children('a').css('font-weight', 'bold');
				// use markers
				var markers = '<ul class="use-marker">\
				<li rel="tooltip" title="Mark article as relevant">Relevant Article</li>\
				<li rel="tooltip" title="Mark article as irrelevant">Irrelevant Article</li>\
				</ul>';
				elm.append(markers);
				$('.use-marker li').each(function() {$(this).tooltip();});
				$('.use-marker li').click(sendRelevance);
				if (data[2]) {
				elm.append('<strong>' + data[3] + '</strong><br><i>' + data[2] + '</i>');
				} else {
				elm.append('<strong>' + data[3] + '</strong>');
				}
				elm.append('<p>' + data[1] + '</p>');
			}
	  });
	  $(this).attr('called', 'called');
	} else {
	  
	  $(this).children('a').css('font-weight', '');
	  $(this).removeAttr('called');
	  $(this).html('<a title="Click to read this article">' + $(this).children('a').html() + '</a>');
	  $('.evt-call').each(function () {$(this).children('a').tooltip();});
	}
  }
  
  function sendRelevance() {
    var id = $(this).parent().parent().attr('name');
    var rid = 0;
    if ($(this).text().indexOf('R') != -1) rid = 1;
    $.ajax({
      'url': 'ajax_scripts.php',
      'method' : 'GET',
      'data' : {
        'fid': '2',
        'aid': id,
        'task_id': task_id,
        'rid': rid
      }
    });
  }
  
  // register the answers block
  $('#turn-on').click(function () {
	if ($('#aggregate').val() == 'true') $('#aggregate').val('false');
	else $('#aggregate').val('true');
	$('#filter-form').submit();
  });
  $('#skip-task').click(function () {window.location.href = 'index.php';});
  $('#submit-answer').click(function () {
    // get the necessary details
    var indices = new Array();
    $('.answer-cb').each(function (i) {
      if ($(this).attr('checked')) indices[indices.length] = i;
    });
    var answerText = $('#answer-text').val();
    var time = $('#minutes').html() + ':' + $('#seconds').html();
    $.ajax({
      'url': 'ajax_scripts.php',
      'method' : 'GET',
      'data' : {
        'fid': '3',
        'time': time,
        'task_id': task_id,
        'answerText': answerText,
        'answers': indices
      },
      'success': function () {
        window.location.href = 'index.php';
      }
    });
  });
  
  
  $(window).scroll(function () {
	currScroll = $(window).scrollTop();
    var p = $('#tl').offset().top;
    var screen = $(window).scrollTop();
    var offset = p - screen < 0 ? 0 : p - screen;
	
	
    $('.timeline-band-layer-inner[name="ether-markers"]').css({
      'top': offset + 'px',
      'position': 'fixed', 
      'color': '#111',
      'font-weight': 'bold',
      'background': '#fff'
    });
  });
  function dateMarkerFix() {
    var p = $('#tl').offset();
    $(this).css({
      'position': 'fixed',
      'top':      (p.top > 0 ? p.top : 0) + 'px',
      'color':    '#111'
    });
  }
  
  
  function clickListeners() {
    $('#gi').click(function () {$('#detail-instructions').toggle();});
  }
  $('.tipsy').each(function () {$(this).tooltip();});
  clickListeners();
  setUpFeedback();
  
  $('.timeline-band-inner').click(function() {
	$(window).scrollTop(currScroll);
  });
  $('.timeline-band-layer-inner').click(function() {
	$(window).scrollTop($(window).scrollTop());
  });
  $('.timeline-event-tape').css('top', function(i, v) {
    return (parseFloat(v) + 20) + 'px';
  });
});
function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function setUpFeedback() {
  var code = '<div class="feedback-panel">' +
			 '<a class="feedback-tab">Feedback</a>' +
			 '<div id="form-wrap">' + 
			 '<form id="send-feedback">' +
			 
			 '<label for="email">Email</label><br>' +
			 '<input class="ui-widget ui-corner-all" type="email" id="email" placeholder="your email"/><br>' +
			 
			 '<label for="msg">Feedback</label><br>' +
			 '<textarea id="msg" class="no-resize ui-widget ui-corner-all" name="msg" rows="12" cols="30" placeholder="your feedback"></textarea>' +
			 
			 '<button type="submit" class="ui-widget ui-state-default ui-corner-all">submit</button>' +
			 '</form></div></div>';
  $('body').append(code);
  
  var feedbackTab = {
	speed: 300,
	containerWidth: $('.feedback-panel').outerWidth(),
	containerHeight: $('.feedback-panel').outerHeight(),
	tabWidth: $('.feedback-tab').outerWidth(),

	init: function() {
	  $('.feedback-panel').css('height',feedbackTab.containerHeight + 'px');

	  $('a.feedback-tab').click(function(event){

		if ($('.feedback-panel').hasClass('open')) {
		  $('.feedback-panel').animate({left:'-' + feedbackTab.containerWidth}, feedbackTab.speed)
		  .removeClass('open');
		} else {
		  $('.feedback-panel').animate({left:'0'},  feedbackTab.speed)
		  .addClass('open');
		}
		event.preventDefault();
	  });
	}
  };
 
  feedbackTab.init();
 
  $("#send-feedback").submit(function() {
	var email = $.trim($("#email").val());
	var message = $.trim($("#msg").val());
	if (!validateEmail(email)) {
	  $('#email').attr('placeholder', 'please enter a valid email')
	  .val('')
	  .addClass('error');
	  return false;
	} else {
	  $('#email').removeClass('error');
	}
	if (!message) {
	  $('#msg').attr('placeholder', 'please enter some feedback')
	  .addClass('error');
	  return false;
	} else {
	  $('#msg').removeClass('error');
	}
	var response_message = "Thank you for your feedback!"
	$.ajax({
	  url: "ajax_scripts.php",
	  data: {
		fid: 4,
		email: email,
		comment: message
	  },
	  success: function(data) {
		//$('#form-wrap').html("<div id='response-message'></div>");
		//$('#response-message').html("<p>" + response_message +"</p>")
		$('#email').val('');
		$('#msg').val('');
		$('#form-wrap')
		.fadeIn(0, function() {
		  $('.feedback-panel')
		  .animate({left:'-' + feedbackTab.containerWidth}, feedbackTab.speed)
		})
		.removeClass('open');
	  }
	});
	return false;
  });
}
google.load('visualization', '1.0', {'packages':['corechart']});