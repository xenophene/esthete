/*
 * this script sets up the data received from server onto the timeline
 */
var tl,
		monthNames = [ "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December" ],
		monthShortNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
		saveReply,
		globalActorMap,
		globalTopicMap,
		globalParentObj,
		globalGrandparentObj,
		prevATag,
		nextATag;
	
function getLoadingImg() {
	return '<ul class="loading"><li><img src="loading3.gif" alt="Loading" title="Loading"/></li></ul>';
}
function sendRelevance() {
	var id = $(this).parent().parent().attr('name');
	var rid = 0;
	rid = $(this).attr('name');
	$.ajax({
		'url': 'ajax_scripts.php',
		'method' : 'GET',
		'data' : {
			'fid': '2',
			'aid': id,
			'task_id': task_id,
			'rid': rid,
			'session': session
		}
	});
}
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
function showModal(headercode, bodycode) {
	var mheader = $('#modal-bubble .modal-header');
	headercode = '<a class="close" data-dismiss="modal" aria-hidden="true"> \
									&times;</a>' + headercode;
	mheader.html(headercode);
	
	var mbody = $('#modal-bubble .modal-body');
	mbody.html(bodycode);
	
	$('#modal-bubble').modal('show');
}
/**
	* TimelineJS modified JS code
	*/
function urlclick(obj, parent_obj, grandparent_obj) {
	
	globalParentObj = parent_obj;
	globalGrandparentObj = grandparent_obj;
	
	if (parent_obj.tagName === 'H3') {
		return;
	}
	
	var aid = obj.name,
			article_headline = obj.innerHTML,
			code = '<ul><li name="' + aid + '" class="evt-call"></li></ul>';
			
	showModal('<h2>' + article_headline + '</h2>', code + getLoadingImg());
	$.ajax({
		'url': 'ajax_scripts.php',
		'method': 'GET',
		'data': {
			'fid': '1',
			'aid': aid,
			'task_id': task_id
		},
		'success': function (data) {
			$('.loading').remove();
			data = $.parseJSON(data);
			var elm = $('.evt-call');
			// use markers
			var markers = '<ul class="use-marker">\
			<li rel="tooltip" name="1" title="Mark article as relevant for point 1">Relevant for 1</li>\
			<li rel="tooltip" name="2" title="Mark article as relevant for point 2 (if given)">Relevant for 2</li>\
			<li rel="tooltip" name="3" title="Mark article as relevant for point 3 (if given)">Relevant for 3</li>\
			<li rel="tooltip" name="4" title="Mark article as irrelevant for task">Mark Irrelevant</li>\
			</ul>';
			//elm.append(markers);
			$('.use-marker li').each(function() {$(this).tooltip();});
			$('.use-marker li').click(sendRelevance);
			var date = data[3].split('-')[2] + ' ' +
								 monthNames[parseInt(data[3].split('-')[1], 10) - 1] +
								 ' ' + data[3].split('-')[0];
			
			if (data[2]) {
				elm.append('<strong>Date: ' + date + '</strong><br><i>' + data[2] + '</i>');
			} else {
				elm.append('<strong>Date: ' + date + '</strong>');
			}
			if (data[1].indexOf('<p>') === -1) {
				data[1] = sprinklePTags(data[1]);
			}
			elm.append('<p>' + data[1] + '</p>');
			
			/* AND NOW BEGINS THE BIGGEST HACK IN THE HISTORY OF MANKIND */
			var a_tags = grandparent_obj.getElementsByTagName('a'),
					event_aids = [];
			
			for (var i = 0; i < a_tags.length; i++ ) {
				event_aids.push(a_tags[i].name);
			}
			
			var thisIndex = $.inArray(aid, event_aids),
					prevIndex = thisIndex - 1,
					nextIndex = thisIndex + 1;
					
			var navigationCode = '<ul>';
			if (prevIndex >= 0) {
				prevATag = a_tags[prevIndex];
				navigationCode += '<li><a class="pointer" onclick="urlclick(prevATag, globalParentObj, \
											globalGrandparentObj)">Previous Article in Story</a></li>';
			}
			if (nextIndex < a_tags.length) {
				nextATag = a_tags[nextIndex];
				navigationCode += '<li><a class="pointer" onclick="urlclick(nextATag, globalParentObj, \
											globalGrandparentObj)">Next Article in Story</a></li>';
			}
			navigationCode += '</ul>';
			
			$('#modal-bubble .modal-header').append(navigationCode);
		}
	});
}
function sprinklePTags(str) {
	var sentences = str.split('.');
	var brokenSentences = '<p>';
	for (var i = 0; i < sentences.length; i++) {
		if (!(i % 4)) {
			brokenSentences += '</p><p>';
		}
		brokenSentences += sentences[i];
	}
	return brokenSentences;
}
$(function () { //ready function
  var currScroll = 0;
  var currEvt;
	/* TimelineJS initialization code */
	if (timelinejsobj.timeline.date) {
		createStoryJS({
			type:	'timeline',
			width:	'95%',
			height:	'550',
			source:	timelinejsobj,
			embed_id:	'timeline-embed'
		});
	}
	
	
	
	
	
  /* Ref: http://code.google.com/p/simile-widgets/wiki/Timeline_EventSources */
  //var tl_el = document.getElementById("tl");
  //var eventSource1 = new Timeline.DefaultEventSource(0);
  //
  //var theme1 = Timeline.ClassicTheme.create();
  //theme1.autoWidth = true; // Set the Timeline's "width" automatically.
  //                         // Set autoWidth on the Timeline's first band's theme,
  //                         // will affect all bands.
  //theme1.event.track.autoWidthMargin = 1;
  //theme1.event.tape.height = 8;
  ////theme1.event.bubble.height = 300;
  //theme1.event.track.height = 15;
  ////theme1.event.instant.icon = "no-image-40.png";
  ////theme1.event.instant.iconWidth = 40;  // These are for the default stand-alone icon
  ////theme1.event.instant.iconHeight = 40;
  //
  //theme1.event.highlightColors[0] = '#FFFF00';
  //theme1.timeline_start = new Date(Date.UTC(fyear, fmonth, fday));
  //theme1.timeline_stop  = new Date(Date.UTC(parseInt(tyear, 10), tmonth, tday));
  //var myear = fmonth >= 6 ? (parseInt(fyear, 10) + 1) : parseInt(fyear, 10);
  //var d = Timeline.DateTime.parseGregorianDateTime(monthNames[(fmonth + 6) % 12] + " 1 " + myear);
  //var bandInfos = [
  //  Timeline.createBandInfo({
  //    width:          45, // set to a minimum, autoWidth will then adjust
  //    intervalUnit:   Timeline.DateTime.MONTH, 
  //    intervalPixels: 100,
  //    eventSource:    eventSource1,
  //    /*
  //    zoomIndex:      10,
  //    zoomSteps:      new Array(
  //      {pixelsPerInterval: 280,  unit: Timeline.DateTime.HOUR},
  //      {pixelsPerInterval: 140,  unit: Timeline.DateTime.HOUR},
  //      {pixelsPerInterval:  70,  unit: Timeline.DateTime.HOUR},
  //      {pixelsPerInterval:  35,  unit: Timeline.DateTime.HOUR},
  //      {pixelsPerInterval: 400,  unit: Timeline.DateTime.DAY},
  //      {pixelsPerInterval: 200,  unit: Timeline.DateTime.DAY},
  //      {pixelsPerInterval: 100,  unit: Timeline.DateTime.DAY},
  //      {pixelsPerInterval:  50,  unit: Timeline.DateTime.DAY},
  //      {pixelsPerInterval: 400,  unit: Timeline.DateTime.MONTH},
  //      {pixelsPerInterval: 200,  unit: Timeline.DateTime.MONTH},
  //      {pixelsPerInterval: 100,  unit: Timeline.DateTime.MONTH} // DEFAULT zoomIndex
  //    ),
  //    /*
  //    eventPainter:   Timeline.CompactEventPainter,
  //    eventPainterParams: {
  //        iconLabelGap:     5,
  //        labelRightMargin: 20,
  //        
  //        iconWidth:        80, // These are for per-event custom icons
  //        iconHeight:       80,
  //        
  //        stackConcurrentPreciseInstantEvents: {
  //            limit: 5,
  //            moreMessageTemplate:    "%0 More Events",
  //            icon:                   "no-image-80.png", // default icon in stacks
  //            iconWidth:              80,
  //            iconHeight:             80
  //        }
  //    },
  //    */
  //    date:           d,
  //    theme:          theme1,
  //    layout:         'original'  // original, overview, detailed
  //  })
  //  /*,
  //  Timeline.createBandInfo({
  //    width:          "10%", 
  //    intervalUnit:   Timeline.DateTime.MONTH, 
  //    intervalPixels: 100,
  //    eventSource:    eventSource1,
  //    date:           d,
  //    theme:          theme1,
  //    layout:         'overview'  // original, overview, detailed
  //  })
  //  */
  //
  //];
  ////bandInfos[1].syncWith = 0;
  ////bandInfos[1].highlight = true;
  //
  //// create the Timeline
  ////tl = Timeline.create(tl_el, bandInfos, Timeline.HORIZONTAL);
  //
  //var url = '.'; // The base url for image, icon and background image
  //               // references in the data
  //eventSource1.loadJSON(data, url);
	/* data consists of the base url echoed */
  //tl.layout(); // display the Timeline
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
        marginTop: (-input.offsetHeight - 70) + 'px', 
        marginLeft: (-input.offsetWidth + 280) + 'px'
      });
    },
		afterShow: function () {
			console.log('hello');
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
  
  Timeline.OriginalEventPainter.prototype._showBubble = function (x, y, evt) {
		if (!evt._description) return;
		currEvt = evt;
		var tabs = '<div class="tabbable"><ul id="event-tabs" class="nav nav-tabs">';
		tabs += '<li><a href="#t1">Interacting Actors</a></li> \
						 <li><a href="#t2">Interacting Topics</a></li> \
						 <li class="active"><a href="#t3">Read Articles</a></li></ul>';
		
		
		tabs += '<div class="tab-content"> \
				 <div class="tab-pane" id="t1"><div style="padding-left:50px" id="i-a"></div></div> \
				 <div class="tab-pane" id="t2"><div style="padding-left:50px" id="i-t"></div></div> \
				 <div class="tab-pane active" id="t3"><div id="r-a"></div></div> \
				 </div></div>';
		
		showModal('<h3>Event Details</h3>', tabs);
		
		$('#event-tabs a').click(function(e) {
			e.preventDefault();
			$(this).tab('show');
		});
	
		showArticleList(evt);
		showActorBubbles(evt);
		showTopicBubbles(evt)
  };
  function showActorBubbles(evt) {
		var desc = evt._description.split('|');
		var aids = desc[2].toString().split('^');
		$('#i-a').append(getLoadingImg());
		$.ajax({
			url: 'ajax_scripts.php',
			method: 'GET',
			dataType: 'json',
			data: {
				'fid': '6',
				'aids': aids,
				'tid': task_id
			},
			success: successD3BubblePlot
		});
  }
	function showTopicBubbles(evt) {
		var desc = evt._description.split('|');
		var aids = desc[2].toString().split('^');
		$('#i-t').append(getLoadingImg());
		$.ajax({
			url: 'ajax_scripts.php',
			method: 'GET',
			dataType: 'json',
			data: {
			'fid': '5',
			'aids': aids,
			'tid': task_id
			},
			success: successD3BubblePlot
		});
	}
	// iterate through desc[0] and put the actor as name, size where size is number of occurences in desc[2]
  function successD3BubblePlot(data) {
		$('.loading').remove();
		var actors = [],
				len = 0,
				threshold = 10,
				sep = data[Object.keys(data)[0]][3],
				id = data[Object.keys(data)[0]][4];
		actors['name'] = 'actors';
		actors['children'] = [];
		var actor_counts = [];
		for (k in data) {
			data[k][0].split(sep).map(function(str) {
				if (actor_counts[str]) {
					actor_counts[str]++;
				} else {
					actor_counts[str] = 1;
				}
			});
		}
		var values = [];
		for (k in actor_counts) {
			values.push(actor_counts[k]);
		}
		values = values.sort(function(a,b){return b - a;});
		var lim = values.length > threshold ? values[threshold - 1] : values[values.length - 1];
		for (k in actor_counts) {
			var elm = [];
			if (actor_counts[k] < lim) continue;
			if (actor_counts[k] == lim && actors['children'].length >= threshold) continue;
			elm['name'] = k;
			elm['size'] = actor_counts[k];
			actors['children'].push(elm);
			len++;
		}
		var diameter = len * 100 > 300 ? 300 : len * 100,
			format = d3.format(",d"),
			color = d3.scale.category20c();
	
		var bubble = d3.layout.pack()
			.sort(null)
			.size([diameter, diameter])
			.padding(1.2);
		
		var svg = d3.select("#" + id).append("svg")
			.attr("width", diameter)
			.attr("height", diameter)
			.style("left", "40px")
			.attr("class", "bubble");
		
		root = actors;
		var node = svg.selectAll(".node")
			.data(bubble.nodes(classes(root))
			.filter(function(d) { return !d.children; }))
			.enter().append("g")
			.attr("class", "node")
			.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
		
		node.append("title")
			.text(function(d) { return d.className + ": " + format(d.value); });
		
		node.append("circle")
			.attr("r", function(d) { return d.r; })
			.style("fill", function(d) { return color(d.packageName); });
		
		node.append("text")
			.attr("dy", ".3em")
			.style("text-anchor", "middle")
			.text(function(d) { return d.className.substring(0, d.r / 3); });

  }
  function classes(root) {
		var classes = [];
		
		function recurse(name, node) {
			if (node.children) node.children.forEach(function(child) { recurse(node.name, child); });
			else classes.push({packageName: name, className: node.name, value: node.size});
		}
		
		recurse(null, root);
		return {children: classes};
  }
	
	
	
  function showArticleList(evt) {
		var desc = evt._description.split('|');
		/*
		var sdate = parseDate(evt._start.toString());
		var edate = parseDate(evt._end.toString());
		var date = sdate.getDate() + ' ' + monthNames[sdate.getMonth()];
		date += ' - ' + edate.getDate() + ' ' + monthNames[edate.getMonth()];
		*/
		var headercode = '';
		
		if (desc[1] == '') {
			var gtopics = [];
			$('#topic-filter option:selected').each(function() {gtopics.push($(this).val())});
			desc[1] = gtopics.join('^');
		}
		if (desc[0] == '') {
			var gactors = [];
			$('#actor-filter option:selected').each(function() {gactors.push($(this).val())});
			desc[0] = gactors.join('^');
		}
    headercode += '<strong><u>Sub-Actors</u></strong>: ' +
									desc[0].split('^').map(toTitleCase).join(', ');
		headercode += '<br><strong><u>Sub-Topics</u></strong>: ' +
									desc[1].split('^').map(toTitleCase).join(', ');
									
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
	
		$('#r-a').html(headlines);
    $('.add-actor').click(function () {
      var e = $("#actor-filter option[value='"+$.trim($(this).attr('id'))+"']");
      if (e.attr("selected") == "selected") {
        e.removeAttr("selected");
      } else {
        e.attr("selected", "selected");
      }
      $('#actor-filter').multiselect('refresh');
    });
    //$('.evt-call').each(function () {$(this).children('a').tooltip();});
    $('.evt-call').click(showArticles);
  }
  
  
  
  $('#study-interaction').click(function() {
		if (!google.visualization) return;
		var tabs = '<div class="tabbable"><ul id="interaction-tabs" class="nav nav-tabs">';
		tabs += '<li class="active"><a href="#t1">Interaction Strength</a></li>'
						//<li><a href="#t2">Topic Hierarchy</a></li>
						+'<li><a href="#t3">Popular Actors</a></li>\
						<li><a href="#t4">Popular Topics</a></li></ul>';
		
		
		tabs += '<div class="tab-content"> \
				 <div class="tab-pane active" id="t1"><div id="i-s">' + getLoadingImg() + '</div></div> \
				 '//<div class="tab-pane" id="t2"><div id="t-h"></div></div>
				 +'<div class="tab-pane" id="t3"><div id="mpa">' + getLoadingImg() + '</div></div> \
				 <div class="tab-pane" id="t4"><div id="mta">' + getLoadingImg() + '</div></div> \
				 </div></div>';
		
		showModal('<h3>Interactions</h3>', tabs);
		
		$('#interaction-tabs a').click(function(e) {
			e.preventDefault();
			$(this).tab('show');
		});
		
		// plot the appropriate charts
		
		getDataAndPlot(plotCharts);
  });
	function getDataAndPlot(fp) {
		if (!saveReply) {
			var aids = Object.keys(article_identifier);
			if (aids.length >= 500) {
				var aids1 = aids.slice(0, 500);
				var aids2 = aids.slice(500, aids.length);
				$.ajax({
					url: 'ajax_scripts.php',
					method: 'GET',
					dataType: 'json',
					data: {
						'fid': '5',
						'aids': aids1,
						'tid': task_id
					},
					success: function (reply1) {
						$.ajax({
							url: 'ajax_scripts.php',
							method: 'GET',
							dataType: 'json',
							data: {
								'fid': '5',
								'aids': aids2,
								'tid': task_id
							},
							success: function(reply2) {
								fp($.extend(reply1, reply2));
							}
						});
					}
				});
			} else {
				$.ajax({
				url: 'ajax_scripts.php',
				method: 'GET',
				dataType: 'json',
				data: {
					'fid': '5',
					'aids': aids,
					'tid': task_id
				},
				success: fp
				});
			}
		} else fp(saveReply);
	}
	function plotAsLegend(reply) {
		saveReply = reply;
		console.log(reply);
		if (reply.length) var year = reply[0][2].split('-')[0];
		var monthBins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
			, sortMonthBins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
			, monthTopics = []
			,	monthAids = [];
		for (k in reply) {
			var amonth = parseInt(reply[k][2].split('-')[1], 10) - 1;
			monthBins[amonth]++;
			if (monthAids[amonth]) monthAids[amonth].push(k);
			else monthAids[amonth] = [k];
			sortMonthBins[amonth]++;
			var topics = reply[k][0].split(reply[k][3]);
			if (!monthTopics[amonth])	monthTopics[amonth] = [];
			for (var i = 0; i < topics.length; i++) {
				var topic = topics[i];
				if (!monthTopics[amonth][topic]) monthTopics[amonth][topic] = 1;
				else monthTopics[amonth][topic]++;
			}
		}
		sortMonthBins = sortMonthBins.sort(function(a,b){return b - a;});
		var dataTable = new google.visualization.DataTable();
		dataTable.addColumn('string', 'Month');
		dataTable.addColumn('number', 'Article Count');
		dataTable.addColumn({type: 'string', role: 'tooltip'});
		dataTable.addColumn({type: 'string', role: 'annotation'});
		var key = 'A';
		var keyMonth = {};
		var is_data = [];
		for (m in monthBins) {
			var tooltip = monthTopics[m] ? assoc_array_sort(monthTopics[m]).join(', ') : '';
			var annoText = '';
			if (monthBins[m] >= sortMonthBins[5] && monthBins[m]) {
				annoText = key;
				keyMonth[key] = m;
				key = String.fromCharCode(key.charCodeAt() + 1);
			}
			if (annoText == 'remove') annoText = '';
			is_data.push([monthShortNames[m], monthBins[m], tooltip, annoText]);
		}
		var code = '';
		for (k in keyMonth) {
			var headlines = [];
			monthAids[keyMonth[k]].slice(0, 2).map(function(v) {
				headlines.push('<u name="' + v + '" class="legend-links">' +
											 $.trim(article_identifier[v]) + '</u>');
			});
			var others;
			if (monthAids[keyMonth[k]].length <= 2) {
				others = '';
			} else if (monthAids[keyMonth[k]].length == 3) {
				others = '<span class="grey"> and 1 other</span>';
			} else {
				others = '<span class="grey"> and ' + (monthAids[keyMonth[k]].length - 2) + ' others</span>';
			}
			code += k + '. ' + headlines.join(', ') + others + '<br>';
		}
		//$('#headline-key').html(code);
		//$('.legend-links').click(renderArticle);
		dataTable.addRows(is_data.slice(0, 12));	// will not work tennis
		var options = {
			'title': 'Interaction Influence over time',
			'height': 90,
			'hAxis': {
				'showTextEvery': 3
			}
		};
		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.LineChart(document.getElementById('mpe-chart-chart'));
		chart.draw(dataTable, options);
	}
	
	
	
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
		$('.loading').remove();
		saveReply = reply;
		// construct the first plot
		// -- monthly-bin wise frequency of articles
		// get the year
		if (reply.length) var year = reply[0][2].split('-')[0];
		var monthBins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
			, monthTopics = [];
		
		for (var k in reply) {
			var amonth = parseInt(reply[k][2].split('-')[1], 10) - 1;
			monthBins[amonth]++;
			var topics = reply[k][0].split(reply[k][3]);
			if (!monthTopics[amonth])	monthTopics[amonth] = [];
			for (var i = 0; i < topics.length; i++) {
				var topic = $.trim(topics[i]);
				if (!monthTopics[amonth][topic]) monthTopics[amonth][topic] = 1;
				else monthTopics[amonth][topic]++;
			}
		}
		var dataTable = new google.visualization.DataTable();
		dataTable.addColumn('string', 'Month');
		dataTable.addColumn('number', 'Article Count');
		dataTable.addColumn({type: 'string', role: 'tooltip'});
		var is_data = [];
		for (var m in monthBins) {
			var tooltip = monthTopics[m] ? assoc_array_sort(monthTopics[m]).join(', ') : '';
			is_data.push([monthNames[m], monthBins[m], tooltip]);
		}
		dataTable.addRows(is_data.slice(0,11)); // why did i need this? figure out!
		var options = {
			'title': 'Interaction Influence over time'
		};
		// Instantiate and draw our chart, passing in some options.
		
		var chart = new google.visualization.LineChart(document.getElementById('i-s'));
		chart.draw(dataTable, options);
		
		// get actors from all articles in reply, and create a popularity list
		if (globalActorMap) successD3BubblePlot(globalActorMap);
		else {
			globalActorMap = {};
			for (k in reply) {
				var actors = [];
				var thisActors = reply[k][5].split(',');
				thisActors.map(function (s) {if ($.trim(s) != "") actors.push(s);});
				if (!actors.length) continue;
				globalActorMap[k] = [actors.join(','), k, reply[k][2], ',', 'mpa'];
			}
			successD3BubblePlot(globalActorMap);
		}
		if (globalTopicMap) successD3BubblePlot(globalTopicMap);
		else {
			globalTopicMap = {};
			for (k in reply) {
				globalTopicMap[k] = [reply[k][0], k, reply[k][2], ';', 'mta'];
			}
			successD3BubblePlot(globalTopicMap);
		}
		
  }
  
	
	$('#go-back').click(function () {
		$('#going-back').val('1');
		
		console.log($('#going-back').val());
		$('#filter-form').submit();
	});
  
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
		//$('.evt-call').each(function () {$(this).children('a').tooltip();});
		$('.evt-call').click(showArticles);
  });
	function renderArticle() {
		// search for aid in data
		var aid = $(this).attr('name');
		for (var k in data.events) {
			var d = data.events[k].description.split("|")[2].split('^');
			if (d.indexOf(aid) != -1) {
				Timeline.OriginalEventPainter.prototype._showBubble(0, 0, {
					'_description': data.events[k].description
				});
			}
		}
	}
	
  function showArticles() {
		var id = $(this).attr('name');
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
					<li rel="tooltip" name="1" title="Mark article as relevant for point 1">\
					Relevant for 1</li>\
					<li rel="tooltip" name="2" title="Mark article as relevant for point 2 (if given)">\
					Relevant for 2</li>\
					<li rel="tooltip" name="3" title="Mark article as relevant for point 3 (if given)">\
					Relevant for 3</li>\
					<li rel="tooltip" name="4" title="Mark article as irrelevant for task">Mark Irrelevant</li>\
					</ul>';
					//elm.append(markers);
					$('.use-marker li').each(function() {$(this).tooltip();});
					$('.use-marker li').click(sendRelevance);
					var date = data[3].split('-')[2] + ' ' +
									monthNames[parseInt(data[3].split('-')[1], 10) - 1] + ' ' +
									data[3].split('-')[0];
					
					if (data[2]) {
						elm.append('<br><strong>Date: ' + date + '</strong><i>' + data[2] + '</i>');
					} else {
						elm.append('<br><strong>Date: ' + date + '</strong>');
					}
					elm.append('<p>' + data[1] + '</p>');
				}
			});
			$(this).attr('called', 'called');
		} else {
			$(this).children('a').css('font-weight', '');
			$(this).removeAttr('called');
			$(this).html('<a title="Click to read this article">' + $(this).children('a').html() + '</a>');
			//$('.evt-call').each(function () {$(this).children('a').tooltip();});
		}
  }
  
  
  // register the answers block
  $('#turn-on').click(function () {
		if ($('#aggregate').val() == 'true') $('#aggregate').val('false');
		else $('#aggregate').val('true');
		$('#filter-form').submit();
  });
  $('#skip-task').click(function () {
		var answerText = '';
		var indices = '';
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
  
  getDataAndPlot(plotAsLegend);
	
	function parseDataForPopularity() {
		var code = '<ul>';
		for (var i = 0; i < top_actors.length; i++) {
			code += '<li><a href="#" title="Click to add this actor to filter" id="' + top_actors[i] +
							'" class="add-actor tipsy">' + toTitleCase(top_actors[i]) + '</a></li>';
		}
		code += '</ul>';
		$('#mpa-list').append(code);
		
		code = '<ul>';
		for (var i = 0; i < top_topics.length; i++) {
			code += '<li><a href="#" title="Click to add this topic to filter" id="' + top_topics[i] +
							'" class="add-topic tipsy">' + toTitleCase(top_topics[i]) + '</a></li>';
		}
		code += '</ul>';
		$('#mpt-list').append(code);
		
		$('.add-topic').click(function () {
      var e = $("#topic-filter option[value='"+$.trim($(this).attr('id'))+"']");
      if (e.attr("selected") == "selected") {
        e.removeAttr("selected");
      } else {
        e.attr("selected", "selected");
      }
      $('#topic-filter').multiselect('refresh');
    });
		$('.add-actor').click(function () {
      var e = $("#actor-filter option[value='"+$.trim($(this).attr('id'))+"']");
      if (e.attr("selected") == "selected") {
        e.removeAttr("selected");
      } else {
        e.attr("selected", "selected");
      }
      $('#actor-filter').multiselect('refresh');
    });
		
	}
	parseDataForPopularity();
  
  
  function clickListeners() {
    $('#gi').click(function () {showModal('<h2>General Instructions</h2>',
																					$('#detail-instructions').html())});
  }
  $('.tipsy').each(function () {$(this).tooltip();});
  clickListeners();
  setUpFeedback();
  
});

function validateEmail(email) { 
  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

function setUpFeedback() {
  var code = '<div class="feedback-panel"> \
			 <a class="feedback-tab">Feedback</a>\
			 <div id="form-wrap">\
			 <form id="send-feedback">\
			 <label for="email">Email</label><br>\
			 <input class="ui-widget ui-corner-all" type="email" id="email" placeholder="your email"/><br>\
			 <label for="msg">Feedback</label><br>\
			 <textarea id="msg" class="no-resize ui-widget ui-corner-all" name="msg" rows="12" cols="30" placeholder="your feedback"></textarea>\
			 <button type="submit" class="ui-widget ui-state-default ui-corner-all">submit</button>\
			 </form></div></div>';
  
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