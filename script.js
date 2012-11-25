// regex is the actor name to be highlighted
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
                return x;
            }
        }
        return -1;
    } : null;
    for (var i = 0; i < bandIndices.length; i++) {
        var bandIndex = bandIndices[i];
        //timeline.getBand(bandIndex).getEventPainter().setFilterMatcher(filterMatcher);
        timeline.getBand(bandIndex).getEventPainter().setHighlightMatcher(highlightMatcher);
    }
    timeline.paint();
}
$(function() {
  var dates = $( "#from, #to" ).datepicker({
    minDate: new Date(2000, 0, 1),
    maxDate: new Date(2001, 0, 1),
    dateFormat: 'dd.mm.yy',
		defaultDate: new Date(2000, 5, 1),
		changeMonth: true,
		numberOfMonths: 2,
		beforeShow: function(input, inst) {
      inst.dpDiv.css({marginTop: (-input.offsetHeight - 110) + 'px', marginLeft: input.offsetWidth + 'px', 'font-size':12+'px'});
    },
		onSelect: function( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
});
