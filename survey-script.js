/*
 * this script sets up the data received from server onto the timeline
 */
var tl;
var monthNames = [ "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December" ];
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
  }
}
$(function () { //ready function
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
  theme1.timeline_stop  = new Date(Date.UTC(parseInt(tyear, 10) + 1, tmonth, tday));
  var myear = fmonth > 6 ? (parseInt(fyear, 10) + 1) : parseInt(fyear, 10);
  var d = Timeline.DateTime.parseGregorianDateTime(monthNames[(fmonth + 6) % 12] + " 1 " + myear);
  var bandInfos = [
    Timeline.createBandInfo({
      width:          "90%", // set to a minimum, autoWidth will then adjust
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
      */
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
  
  Timeline.OriginalEventPainter.prototype._showBubble = function (x, y, evt) {
    var mheader = $('#modal-bubble .modal-header');
    var desc = evt._description.split('|');
    
    var sdate = parseDate(evt._start.toString());
    var edate = parseDate(evt._end.toString());
    var date = sdate.getDate() + ' ' + monthNames[sdate.getMonth()];
    date += ' - ' + edate.getDate() + ' ' + monthNames[edate.getMonth()];
    
    headercode = '<a class="close" data-dismiss="modal" aria-hidden="true"> \
                  &times;</a>';
    if (desc[0].split('^').length == 1 && desc[1].split('^').length == 1) {
      headercode += '<h3>Actor: ' + toTitleCase(desc[0]) + ', Topic: ' + 
                    toTitleCase(desc[1]) + '</h3>' + date;
      
      var headlines = '<a href="#" id="' + desc[0] + '" class="add-actor">' +
                      'Add this actor in filter</a><br>' +
                      '<em>Articles relevant to this theme (Click to read full):</em><ol>';
    } else {
      headercode += '<strong><u>Actors</u></strong>: ' + desc[0].split('^').map(toTitleCase).join(', ');
      headercode += '<br><strong><u>Topics</u></strong>: ' + desc[1].split('^').map(toTitleCase).join(', ');
      var headlines = '<em>Articles relevant to this theme (Click to read full):</em><ol>';
    }
    mheader.html(headercode);
    
    var mbody = $('#modal-bubble .modal-body');
    var descs = desc[2].toString();
    descs = descs.split('^');
    for (var i = 0; i < descs.length; i++) {
      var t = descs[i].split('#');
      headlines += '<li rel="tooltip" class="evt-call" \
                    name="' + t[1] + '"><a title="Click to read this article">' +
                    t[0] + '</a></li>';
    }
    headlines += '</ol>';
    mbody.html(headlines);
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
    $('.evt-call').click(function () {
      var id = $(this).attr('name');
      if (!$(this).attr('called')) {
        $.ajax({
          'url': 'ajax_scripts.php',
          'method': 'GET',
          'data': {
            'fid': '1',
            'aid': id,
            'task_id': task_id
          },
          'success': function (data) {
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
              data[2] = '<br><i>' + data[2] + '</i><br>';
              elm.append(data[2]);
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
    });
    $('#modal-bubble').modal('show');
  };
  
  
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
  $('#skip-task').click(function () {window.location = 'submit-answer.php';});
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
        window.location = 'submit-answer.php';
      }
    });
  });
  
  
  $(window).scroll(function () {
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
});
