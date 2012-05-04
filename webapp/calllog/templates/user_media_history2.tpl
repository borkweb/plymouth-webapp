<!-- BEGIN: main -->
<div id="MediaHistoryInnerDiv" style="height:150px; overflow-y:auto;">

	<table width="99%" align="center" id="call_history_table">
		<tr>
			<th>Future Event</th>
			<th>Start</th>
			<th>End</th>
		</tr>
		<!-- BEGIN: media_row_soon -->
				<tr style="cursor:pointer; padding:30px; border:1px;" onclick="javascript: viewMediaHistoryDetails({media_row.reservation_idx})">
					<td>{media_row.memo_short}</td>
					<td>{media_row.start_date}</td>
					<td>{media_row.end_date}</td>
				</tr>
		<!-- END: media_row_soon -->
		<!-- BEGIN: media_row_future -->
				<tr style="cursor:pointer; padding:30px; border:1px;" onclick="javascript: viewMediaHistoryDetails({media_row.reservation_idx})">
					<td>{media_row.memo_short}</td>
					<td>{media_row.start_date}</td>
					<td>{media_row.end_date}</td>
				</tr>
		<!-- END: media_row_future -->
		<!-- BEGIN: no_results_message_future -->
		<tr>
			<td class="content-info no-border" colspan="3" align="center">There Are No Future Events Scheduled</td>
		</tr>
		<!-- END: no_results_message_future -->
	</table>
	<br/>
	<table width="99%" class="content" align="center" valign="top" cellpadding="3" cellspacing="1" id='call_history_table'>
		<tr>
			<th>Past Event</th>
			<th>Start</th>
			<th>End</th>
		</tr>
		<!-- BEGIN: media_row_past -->
			<tr style="cursor:pointer; padding:30px; border:1px;" onclick="javascript: viewMediaHistoryDetails({media_row.reservation_idx})">
				<td>{media_row.memo_short}</td>
				<td>{media_row.start_date}</td>
				<td>{media_row.end_date}</td>
			</tr>
		<!-- END: media_row -->
		<!-- BEGIN: no_results_message_past -->
		<tr>
			<td class="content-info no-border">There Are No Past Events On Record</td>
		</tr>
		<!-- END: no_results_message_past -->
	</table>
</div>
<!-- END: main -->