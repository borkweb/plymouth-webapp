<!-- BEGIN: main -->
<table width="100%" align="center" valign="top" cellpadding="0" cellspacing="0">
<tr>
<td width="100%" align="center">
	<table width="100%" align="center" cellpadding="3" cellspacing="0">
	<tr>
		<td width="100%" align="left" valign="top">
			<h2>&#187; {page_headline}</h2>
			<span class="page_content_summary">Select from the reporting functions below.</span>
		</td>
	</tr>
	<tr>
		<td width="100%" align="left" valign="top">
            <li class="page_nav_link"><a href="javascript:openGraphWindow('{todays_top_call_loggers}')" title="View Today's Top Call Loggers.">Today's Top Call Loggers</a></li>
			<li class="page_nav_link"><a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?calllogreport=current" title="View CallLog Report.">CallLog Report</a></li>
			<li class="page_nav_link_last"><a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?calllogreport=current&userreport={calllog_username}" title="View All Reports.">Extended Report</a></li>
			<br />
		</td>
	</tr>
	<tr>
		<td width="100%" align="left">
			<table width="100%" align="center" valign="top" cellpadding="5" cellspacing="1" class="content">
			<tr>
				<td valign="middle" class="content" colspan="2">
					<p>
					Search username
					&nbsp;&nbsp;
					<form name="stats_and_reports" method="post" action="{PHP.BASE_URL}/graphs/stats_and_reports.html">
						<input type="hidden" name="userreport_form" value="{calllog_username}" />
						<input type="text" name="calllog_username" value="{calllog_username}" onfocus="javascript:if(this.value=='{calllog_username}') {this.value='';}" onblur="javascript:if(this.value=='') {this.value='{calllog_username}'}" onclick="javascript:if(this.value!=''){this.value=''}" />
						<input type="submit" value="Submit" />
						<input type="reset" value="Reset" />
					</form>
					<br />
						<li class="page_nav_link"><a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display=log_in_history&calllog_username={my_user_name}" title="View {user_name_label}'s Log In History."><span style="text-transform: capitalize;">{user_name_label}'s </span> Log In History</a></li>
						<li class="page_nav_link"><a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display=logged_calls&calllog_username={my_user_name}" title="View Calls That {user_name_label}'s Logged."><span style="text-transform: capitalize;">{user_name_label}'s </span> Logged Calls</a></li>
						<li class="page_nav_link_last"><!--<a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display=resolved_calls&calllog_username={my_user_name}" title="View Calls {user_name_label}'s Resolved."><span style="text-transform: capitalize;">{user_name_label}'s </span> Resolved Calls</a>-->Resolved Calls</li>
					</p>
				</td>
			</tr>
			<!-- BEGIN: user_report -->
			<tr>
				<td valign="top" class="content_head" colspan="2">
					Report for <em>{calllog_username}</em>
				</td>
			</tr>
			<tr>
				<td width="500" valign="middle" align="center" class="content" nowrap="nowrap">
					<table width="100%" align="center" valign="top" cellpadding="5" cellspacing="0" class="content">	
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							My Total Calls Logged This Semester
						</td>
						<td align="left" valign="middle" class="content_light">
							{number_of_calls_logged_this_semester}
						</td>
					</tr>
					<!-- BEGIN: total_calls_resolved_this_semester -->
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							My Total {call_type_this_semester} Calls Resolved This Semester
						</td>
						<td align="left" valign="middle" class="content_light">
							{number_of_calls_resolved_this_semester}
						</td>
					</tr>
					<!-- END: total_calls_resolved_this_semester -->
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							My Total Lifetime Calls Logged
						</td>
						<td align="left" valign="middle" class="content_light">
							{number_of_calls_logged}
						</td>
					</tr>
					<!-- BEGIN: total_calls_resolved -->
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							My Total Lifetime {call_type} Calls Resolved
						</td>
						<td align="left" valign="middle" class="content_light">
							{number_of_calls_resolved}
						</td>
					</tr>
					<!-- END: total_calls_resolved -->
					</table>
				</td>
			</tr>
			<!-- END: user_report -->
			<!-- BEGIN: call_log_report -->
			<tr>
				<td valign="top" class="content_head" colspan="2">
					Call Log Report
				</td>
			</tr>
			<tr>
				<td width="500" valign="middle" align="center" class="content" nowrap="nowrap">
					<table width="100%" align="center" valign="top" cellpadding="5" cellspacing="0" class="content">	
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							Top CallLogger This Semester
						</td>
						<td align="left" valign="middle" class="content_light">
							{top_calllogger_this_semester}
						</td>
					</tr>
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							Top Remote CallLogger This Semester
						</td>
						<td align="left" valign="middle" class="content_light">
							{top_remote_calllogger_this_semester}
						</td>
					</tr>	
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							Top CallLogger Lifetime
						</td>
						<td align="left" valign="middle" class="content_light">
							{top_calllogger_lifetime}
						</td>
					</tr>
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							Top Remote CallLogger Lifetime
						</td>
						<td align="left" valign="middle" class="content_light">
							{top_remote_calllogger_lifetime}
						</td>
					</tr>						
					<tr>
						<td width="300" align="left" valign="middle" class="content_head_light_dashed" nowrap="nowrap">
							Total Lifetime CallLog Calls
						</td>
						<td align="left" valign="middle" class="content_light">
							{total_call_log_calls}
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<!-- END: call_log_report -->
			<!-- BEGIN: display_user_info -->
			<tr>
				<td valign="top" class="content_head" colspan="2">
					{display_name}
				</td>
			</tr>
			<tr>
				<td width="100%" valign="middle" align="center" class="content" nowrap="nowrap">
					<table width="100%" align="center" valign="top" cellpadding="3" cellspacing="1" class="content">	
					<!-- BEGIN: call_info_head -->
					<tr>
						<td align="center" valign="middle" class="content_head" nowrap="nowrap">
							<a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display={display}&calllog_username={calllog_username}&sort_by=date" title="Sort By Date" class="content_head">Date</a>
						</td>
						<td align="center" valign="middle" class="content_head">
							<a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display={display}&calllog_username={calllog_username}&sort_by=caller_last_name" title="Sort By Caller Last Name." class="content_head">Caller</a>
						</td>
						<td align="center" valign="middle" class="content_head">
							Options
						</td>
					</tr>
					<!-- END: call_info_head -->
					<!-- BEGIN: call_info -->
					<tr>
						<td align="center" valign="middle" class="content">
							{key.call_date}
						</td>
						<td align="left" valign="middle" class="content">
							{key.caller_first_name} {key.caller_last_name} <em>{key.caller_username}</em>
						</td>
						<td align="center" valign="middle" class="content">
							<a href="javascript:viewCallDetails('{call_log_web_home}/view_call_details_popup.html?call_id={key.call_id}')" title="View Call Details." class="action">Call Details</a>
						</td>
					</tr>
					<!-- END: call_info -->
					<!-- BEGIN: log_in_history_head -->
					<tr>
						<td align="center" valign="middle" class="content_head" nowrap="nowrap">
							<a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display={display}&calllog_username={calllog_username}&sort_by=date" title="Sort By Date" class="content_head">Log in Date</a>
						</td>
						<td align="center" valign="middle" class="content_head" nowrap="nowrap">
							Log in Time
						</td>
						<td align="center" valign="middle" class="content_head">
							<a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display={display}&calllog_username={calllog_username}&sort_by=ip_address" title="Sort By IP Address." class="content_head">IP Address</a>
						</td>
						<td align="center" valign="middle" class="content_head">
							<a href="{PHP.BASE_URL}/graphs/stats_and_reports.html?display={display}&calllog_username={calllog_username}&sort_by=host_name" title="Sort By Host Name." class="content_head">Host Name</a>
						</td>
					</tr>
					<!-- END: log_in_history_head -->
					<!-- BEGIN: log_in_history -->
					<tr>
						<td align="center" valign="middle" class="content">
							{key.login_date}
						</td>
						<td align="center" valign="middle" class="content">
							{key.login_time}
						</td>
                        <td align="center" valign="middle" class="content">
							{key.ip_address}
						</td>
						<td align="center" valign="middle" class="content">
							{key.host_name}
						</td>
					</tr>
					<!-- END: log_in_history -->
					</table>
				</td>
			</tr>
			<!-- END: display_user_info -->
			</table>
		</td>
	</tr>
	</table>
</td>
</tr>
</table>
<!-- END: main -->
