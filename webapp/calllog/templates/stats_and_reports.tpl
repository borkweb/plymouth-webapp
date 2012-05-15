<!-- BEGIN: main -->
<h1>{page_headline}</h1>
<span class="page_content_summary">Select from the reporting functions below.</span>
<ul>
	<li><a href="javascript:openGraphWindow('{todays_top_call_loggers}')" title="View Today's Top Call Loggers.">Today's Top Call Loggers</a></li>
	<li><a href="?calllogreport=current" title="View CallLog Report.">CallLog Report</a></li>
	<li><a href="?calllogreport=current&userreport={calllog_username}" title="View All Reports.">Extended Report</a></li>
</ul>

<br/><br/>

<form name="stats_and_reports" method="post" action="{PHP.BASE_URL}/graphs/stats_and_reports.html">
	<input type="hidden" name="userreport_form" value="{calllog_username}" />
	<label for="calllog_username">Search username</label>
	<input type="text" name="calllog_username" value="{calllog_username}" onfocus="javascript:if(this.value=='{calllog_username}') {this.value='';}" onblur="javascript:if(this.value=='') {this.value='{calllog_username}'}" onclick="javascript:if(this.value!=''){this.value=''}" />
	<input type="submit" class="btn" value="Submit" />
	<input type="reset" class="btn danger" value="Reset" />
</form>
<ul>
	<li><a href="?display=log_in_history&calllog_username={my_user_name}" title="View {user_name_label}'s Log In History.">{user_name_label}'s Log In History</a></li>
	<li><a href="?display=logged_calls&calllog_username={my_user_name}" title="View Calls That {user_name_label}'s Logged.">{user_name_label}'s Logged Calls</a></li>
	<li><a href="?display=resolved_calls&calllog_username={my_user_name}" title="View Calls {user_name_label}'s Resolved.">{user_name_label}'s Resolved Calls</a></li>
</ul>

<br/><br/>

<!-- BEGIN: user_report -->
Report for <em>{calllog_username}</em>
<ul>
	<li><strong>My Total Calls Logged This Semester:</strong> {number_of_calls_logged_this_semester}</li>

	<!-- BEGIN: total_calls_resolved_this_semester -->
	<li><strong>My Total {call_type_this_semester} Calls Resolved This Semester:</strong> {number_of_calls_resolved_this_semester}</li>
	<!-- END: total_calls_resolved_this_semester -->

	<li><strong>My Total Lifetime Calls Logged:</strong> {number_of_calls_logged}</li>

	<!-- BEGIN: total_calls_resolved -->
	<li><strong>My Total Lifetime {call_type} Calls Resolved:</strong> {number_of_calls_resolved}</li>
	<!-- END: total_calls_resolved -->
</ul>
<!-- END: user_report -->

<!-- BEGIN: call_log_report -->
(this report is not currently functional)<br/>
Call Log Report
<ul>
       <li><strong>Top CallLogger This Semester:</strong> {top_calllogger_this_semester}</li>
       <li><strong>Top Remote CallLogger This Semester:</strong> {top_remote_calllogger_this_semester}</li>
       <li><strong>Top CallLogger Lifetime:</strong> {top_calllogger_lifetime}</li>
       <li><strong>Top Remote CallLogger Lifetime:</strong> {top_remote_calllogger_lifetime}</li>
       <li><strong>Total Lifetime CallLog Calls:</strong> {total_call_log_calls}</li>
</ul>
<!-- END: call_log_report -->

<!-- BEGIN: display_user_info -->
{display_name}
<table class="grid">
	<thead>
		<!-- BEGIN: call_info_head -->
		<tr>
			<th><a href="?display={display}&calllog_username={calllog_username}&sort_by=date" title="Sort By Date" class="content_head">Date</a></th>
			<th><a href="?display={display}&calllog_username={calllog_username}&sort_by=caller_last_name" title="Sort By Caller Last Name.">Caller</a></th>
			<th>Options</th>
		</tr>
		<!-- END: call_info_head -->
		<!-- BEGIN: log_in_history_head -->
		<tr>
			<th><a href="?display={display}&calllog_username={calllog_username}&sort_by=date" title="Sort By Date" class="content_head">Log in Date</a></th>
			<th>Log in Time</td>
			<th><a href="?display={display}&calllog_username={calllog_username}&sort_by=ip_address" title="Sort By IP Address." class="content_head">IP Address</a></th>
			<th><a href="?display={display}&calllog_username={calllog_username}&sort_by=host_name" title="Sort By Host Name." class="content_head">Host Name</a></th>
		</tr>
		<!-- END: log_in_history_head -->
	</thead>

	<tbody>
		<!-- BEGIN: call_info -->
		<tr>
			<td>{key.call_date}</td>
			<td>{key.caller_first_name} {key.caller_last_name} <em>{key.caller_username}</em></td>
			<td><a href="{PHP.BASE_URL}/ticket/{key.call_id}" class="action button">Call Details</a></td>
		</tr>
		<!-- END: call_info -->
		<!-- BEGIN: log_in_history -->
		<tr>
			<td>{key.login_date}</td>
			<td>{key.login_time}</td>
			<td>{key.ip_address}</td>
			<td>{key.host_name}</td>
		</tr>
		<!-- END: log_in_history -->
	</tbody>
</table>
<!-- END: display_user_info -->
<!-- END: main -->
