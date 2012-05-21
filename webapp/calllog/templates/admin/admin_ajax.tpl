<!-- BEGIN: main -->

	<!-- BEGIN: getEmployeeCalls -->
	<table class="grid">
	<thead>
		<tr>
			<th>Call ID</th>
			<th>Caller Info</th>
			<th>Call Date/Time</th>
			<th>Call Details</th>
			<th>Call Assigned To</th>
			<th>Call Status</th>
		</tr>
	</thead>
	<tbody>
		<!-- BEGIN: call -->
		<tr style="cursor:pointer;"  onClick="document.location='{CALL_LOG_WEB_HOME}/ticket/{calls.call_id}'">
			<td>{calls.call_id}</td>
			<td>{calls.caller_info}</td>
			<td>{calls.call_date_time}</td>
			<td>{calls.comments}</td>
			<td>{calls.assigned_to}</td>
			<td>{calls.call_status}</td>
		</tr>
		<!-- END: call -->
	</tbody>
	</table>		
	<!-- END: getEmployeeCalls -->

	<!-- BEGIN: upgradeCallLog -->
	<blockquote>
	<h1>Call Log has been Upgraded to Version {version_number}</h1>
	<h2>All Pending Call Log Requests have been Closed</h2>
	</blockquote>
	<!-- END: upgradeCallLog -->
<!-- END: main -->
