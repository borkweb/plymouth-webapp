<!-- BEGIN: main -->
<div id="CallHistoryInnerDiv">
	<div class="content-info-top content-info" style="width:30%;">Date</div>
	<div class="content-info-top content-info" style="width:65%;">Subject</div>
	<!-- BEGIN: user_call_history -->
		<!-- BEGIN: call -->
		<div id="CallHistoryRow{i}" style="cursor:pointer;" onclick="viewCallHistoryDetails({call.call_id})">
			<div id="CallHistoryTD1_{i}" class="content-info-main content-info" style="width:30%;">{call.date_assigned}</div>
			<div id="CallHistoryTD2_{i}" class="content-info-main content-info" style="width:65%;">{call.comments}</div>
		</div>
		<!-- END: call -->
	<!-- END: user_call_history -->
	<!-- BEGIN: no_results_message -->
	<div id="CallHistoryRow1" class="content-info-none content-info" align="center">This user has no Call History</div>
	<!-- END: no_results_message -->
</div>
<!-- END: main -->
