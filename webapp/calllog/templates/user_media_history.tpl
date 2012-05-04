<!-- BEGIN: main -->
<div id="MediaHistoryInnerDiv" style="height:150px; overflow-y:auto;">
<!-- BEGIN: loan_section -->
	<table width="99%" align="center" id="user_media_table">
	<tr>
		<th>{loan_section}</th>
		<th>Start</th>
		<th>End</th>
	</tr>
	<!-- BEGIN: media_row -->
			<tr style="cursor:pointer; padding:30px; border:1px;" onclick="javascript: viewMediaHistoryDetails({media_row.reservation_idx})">
				<td>{media_row.memo_short}</td>
				<td>{media_row.start_date}</td>
				<td>{media_row.end_date}</td>
			</tr>
	<!-- END: media_row -->

	</table>
<!-- END: loan_section -->

<!-- BEGIN: no_results_message_past -->
There Are No Past Events On Record
<!-- END: no_results_message_past -->

</div>
<!-- END: main -->