var currentTime = new Date()
var startTime= new Date();

$(function(){
	$( "#startdate , #enddate" ).datepicker({ minDate: currentTime});
	
	//When the startdate changes enddates dates are blocked out from those dates and the new date is changed to the start date(if it was below the new start date) 
	$( "#startdate" ).change(function (){
		var startTime = ($("#startdate").val());
		$("#enddate").datepicker('option','minDate',startTime);

	});
	$( 'body' ).PSUFeedback({});
});

