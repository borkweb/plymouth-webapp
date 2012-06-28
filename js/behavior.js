var currentTime = new Date()
var startTime= new Date();
var startTime2= new Date();

$(function(){
	$( "#startdate , #enddate" ).datepicker({ minDate: currentTime});
	
	//When the startdate changes enddates dates are blocked out from those dates and the new date is changed to the start date(if it was below the new start date) 
	$( "#startdate" ).change(function (){
		var startTime = ($("#startdate").val());
		$("#enddate").datepicker('option','minDate',startTime);

	});

	$( "#fromdate, #todate" ).datepicker();

	$( "#fromdate" ).change(function (){
		var startTime2 = ($("#fromdate").val());
	$("#todate").datepicker('option','minDate',startTime2);
	});

	$( 'body' ).PSUFeedback({});
});

