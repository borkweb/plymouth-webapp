<!-- BEGIN: main -->
<div id="CallHistoryInnerDiv">
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Date</th>
				<th>Subject</th>
			</tr>
		</thead>
		<tbody>	
			<!-- BEGIN: user_call_history -->
			<!-- BEGIN: call -->
			<tr id="CallHistoryRow{i}" style="cursor:pointer;" onclick="viewCallHistoryDetails({call.call_id})">
				<td id="CallHistoryTD1_{i}">{call.date_assigned}</td>
				<td id="CallHistoryTD2_{i}" class="history_comments">{call.comments}</td>
			</tr>
			<!-- END: call -->
			<!-- END: user_call_history -->

			<!-- BEGIN: no_results_message -->
			<tr><td>This user has no Call History</td></tr>
			<!-- END: no_results_message -->
		</tbody>
	</table>
</div>
<!-- END: main -->
