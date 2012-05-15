<!-- BEGIN: main -->
	<!-- BEGIN: kb -->
		<table width="100%" height="100" cellpadding="3" cellspacing="1">
		<!-- BEGIN: sent -->
		<tr style="font-size:11px;" class="content_highlight">
			<td id="knowledgebaseTD1_{link.id}" width="75%"><a href="{link.url}" target="_blank" title="{link.title}">{link.title}</a></td>
			<td id="knowledgebaseTD2_{link.id}"><div id="sendKBEmailDiv_{link.id}"><a href="javascript:void(sendKBEmail({link.id},{count_sent},{i}));">Sent ({count_sent})</a></div></td>
		</tr>
		<!-- END: sent -->

		<!-- BEGIN: none -->
		<tr class="content_light" style="font-size:11px;">
			<td id="knowledgebaseTD1_{link.id}" width="75%"><a href="{link.url}" target="_blank" title="{link.title}">{link.title}</a></td>
			<td id="knowledgebaseTD2_{link.id}"><div id="sendKBEmailDiv_{link.id}"><a href="javascript:void(sendKBEmail({link.id},{count_sent},{i}));">Send (0)</a></div></td>
		</tr>
		<!-- END: none -->
		</table>

		<!-- BEGIN: no_results -->
		<div align="center" style="font-size:12px;">No related knowledgebase articles found</div>
		<!-- END: no_results -->
		<!-- BEGIN: view -->
		<div align="center" style="font-size:12px;">Type text into Problem Details to<br/> Search the Knowledgebase</div>
		<!-- END: view -->
	<!-- END: kb -->

	<!-- BEGIN: searchResults -->
	<div id="main-search-results2">
		<h2>&#187; {search_num} Search Results For {search_results_text}: <em> <u>{search_string}</u></em></h2>
		<table align="center" valign="top" cellpadding="5" cellspacing="1" width="90%" id="calllog-search" class="grid sortable">
			<thead>
				<tr>
					<th>{search_field_1}</th>
					<th>{search_field_2}</th>
					<th>{search_field_3}</th>
					<th>{search_field_4}</th>
					<th>{search_field_5}</th>
				</tr>
			</thead>
			<tbody>
				<!-- BEGIN: results -->
				<!-- BEGIN: user_info -->
				<tr>
					<td>
						<a href="{PHP.BASE_URL}/user/{key.email}/?option={search_string}&find_type={search_type}" title="Create New Call For {key.email}." class="nav_link">{key.name_full}</a>
						<a href="index.html?new_call=passed&amp;action=view_open_calls&amp;option=caller&amp;group={key.email}&amp;find_type={key.email}+open" title="Open calls for {key.email}">{key.open_call}</a>
					</td>
					<td>{key.email}</td>
					<td>{key.phone_of} {key.phone_vm}</td>
					<td title="{key.title} {key.major}">{key.title} {key.major}</td>
					<td>{key.dept}</td>
				</tr>
				<!-- END: user_info -->
				<!-- BEGIN: no_username -->
				<tr>
					<td>
						<a href="{PHP.BASE_URL}/user/{key.identifier}/" title="Create New Call." class="nav_link">{key.name_full}</a> 
						(<a href="https://www.plymouth.edu/webapp/ape/user/{key.pidm}/" target="_blank">APE</a>)
					</td>
					<td><span style="color:red;font-weight:bold;">no username</span></td>
					<td>{key.phone_of} {key.phone_vm}</td>
					<td title="{key.title} {key.major}">{key.title} {key.major}</td>
					<td>{key.dept}</td>
				</tr>
				<!-- END: no_username -->
				<!-- END: results -->
				<!-- BEGIN: ticket_info -->
				<tr>
					<td><a href="{call_log_web_home}/ticket/{call_id}/?option={search_string}&find_type={search_type}" title="Edit Call For {caller_username} and Ticket Number {call_id}" class="nav_link">{call_id}</a></td>
					<td>{caller_first_name} {caller_last_name}</td>
					<td>{caller_username}</td>
					<td>{call_date} @ {call_time}</td>
					<td>{calllog_username}</td>
				</tr>
				<!-- END: ticket_info -->
				<!-- BEGIN: no_ticket -->
				<tr>
					<td align="center" colspan="5"><strong>{no_ticket}</strong></td>
				</tr>
				<!-- END: no_ticket -->
				<!-- BEGIN: hardware_info -->
				<tr>
					<td><a href="{call_log_web_home}/new_call.html?caller={HW_Username}&action=searchUser&option={search_string}&group=none&find_type={search_type}&page=index.html" title="Edit Call For {caller_username} and Ticket Number {call_id}" class="nav_link">{HW_IPName}</a></td>
					<td>{MACAddress}</td>
					<td>{IPAddress}</td>
					<td>{location}</td>
				</tr>
				<!-- END: hardware_info -->
				<!-- BEGIN: hardware_info_IP -->
				<tr>
					<td><a href="{call_log_web_home}/new_call.html?caller={HW_Username}&action=searchUser&option={search_string}&group=none&find_type={search_type}&page=index.html" title="Edit Call For {caller_username} and Ticket Number {call_id}" class="nav_link">{IPAddress}</a></td>
					<td>{HW_Name}</td>
					<td>{HW_Username}</td>
					<td>{MACAddress}</td>
					<td>{HW_IPName}</td>
				</tr>
				<!-- END: hardware_info_IP -->
				<!-- BEGIN: hardware_info_MAC -->
				<tr>
					<td><a href="{call_log_web_home}/new_call.html?caller={HW_Username}&action=searchUser&option={search_string}&group=none&find_type={search_type}&page=index.html" title="Edit Call For {caller_username} and Ticket Number {call_id}" class="nav_link">{MACAddress}</a></td>
					<td>{HW_Name}</td>
					<td>{HW_Username}</td>
					<td>{HW_IPName}</td>
					<td>{IPAddress}</td>
				</tr>
				<!-- END: hardware_info_MAC -->

				<!-- BEGIN: call_log_user -->
				<tr>
					<td><a href="{call_log_web_home}/new_call.html?caller={key.caller_username}&call_id={key.call_id}&action=searchUser&option={search_string}&group=none&find_type={search_type}&page=index.html" title="Create New Call For {key.caller_username}." class="nav_link">{key.call_id}</a></td>
					<td>{key.caller_first_name} {key.caller_last_name} ({key.caller_username})</td>
					<td>{key.call_date}</td>
					<td>{key.call_time}</td>
					<td>{key.comments}</td>
				</tr>
				<!-- END: call_log_user -->

				<!-- BEGIN: user_closed_calls -->
				<tr>
					<td><a href="{call_log_web_home}/new_call.html?caller={key.caller_username}&call_id={key.call_id}&action=searchUser&option={search_string}&group=none&find_type={search_type}&page=index.html" title="Re-open Call for {key.caller_username}." class="nav_link">{key.call_id}</a></td>
					<td>{key.caller_first_name} {key.caller_last_name} ({key.caller_username})</td>
					<td>{key.call_date}</td>
					<td>{key.call_time}</td>
					<td>{key.comments}</td>
				</tr>
				<!-- END: user_closed_calls -->

				<!-- BEGIN: no_results_message -->
				<tr>
					<td colspan="5">
						<h2>Your search for <em><u>{search_string}</u></em> returned no results.</h2>
					</td>
				</tr>
				<!-- END: no_results_message -->
				<tr class="top-border">
					<td><a href="{call_log_web_home}/new_call.html?caller=generic" class="nav_link">Generic Caller</a></td>
					<td>generic</td>
					<td>N/A</td>
					<td>N/A</td>
				</tr>
				<tr>
					<td><a href="{call_log_web_home}/new_call.html?caller=kiosk" class="nav_link">Kiosk Caller</a></td>
					<td>kiosk</td>
					<td>N/A</td>
					<td>N/A</td>
				</tr>
				<tr>
					<td><a href="{call_log_web_home}/new_call.html?caller=cluster" class="nav_link">Cluster Caller</a></td>
					<td>kiosk</td>
					<td>N/A</td>
					<td>N/A</td>
				</tr>
			</tbody>
		</table>
		</div>
	</div>
	<!-- END: searchResults -->

	<!-- BEGIN: call_history -->
	<div id="call_history_detail_div">
	<div style="text-align:left; font-size: 10px; float: left;"><a href="{CALL_LOG_WEB_HOME}/ticket/{this_call_id}/">Edit This Call</a></div>
	<div style="text-align:right; font-size: 10px;"><a href="javascript: void(0);" onClick="viewCallHistorySummary('{caller}', false);">&#171; Back</a></div>
		<table width="95%" valign="top" cellpadding="3" cellspacing="1">
			<tr><th align="center" colspan="2">Call Details</th></tr>
				<!-- BEGIN: call_log_details -->
					<tr><td>Call ID</td><td>{call_id}</td></tr>
					<tr><td>Caller Username</td><td>{caller_username}</td></tr>
					<tr><td>Call Name</td><td>{caller_name}</td></tr>
					<tr><td>Call Phone Number</td><td>{caller_phone_number}</td></tr>
					<tr><td>Call Log Username</td><td>{calllog_username}</td></tr>
					<tr><td>Call Date &amp; Time</td><td>{call_date_time}</td></tr>
					<tr><td>Keywords</td><td>{keywords}</td></tr>
					<tr><td>Location</td><td>{location}</td></tr>
					<tr><td>Logged From</td><td>{call_logged_from}</td></tr>
				<!-- END: call_log_details -->
		</table>
		<div id="call_history_assignment_loop" style="height:150px; overflow:auto;">
		<table width="100%" valign="top" cellpadding="3" cellspacing="1">
		<tr><th align="center" colspan="2">Call History</th></tr>
		<!-- BEGIN: call_num_assigned -->
			<tr style="background-color: {priority_color};">
				<td colspan="1">{call_date} at {call_time}</td><td style="text-align: right;"><b>{priority} Priority</b></td>
			</tr>
			<!-- BEGIN: call_history_details -->
				<tr><td>Updated By</td><td>{updated_by}</td></tr>
				<tr><td>TLC Assigned To</td><td>{tlc_assigned_to}</td></tr>
				<tr><td>ITS Assigned Group</td><td>{its_assigned_group}</td></tr>
				<tr><td>Comments</td><td>{comments}</td></tr>
				<tr><td>Call Status</td><td>{call_status}</td></tr>
			<!-- END: call_history_details -->
		 <tr>
		  <td width="100%" colspan="2">
		   <hr />
		  </td>
		 </tr>		
		<!-- END: call_num_assigned -->	
		</table>
		</div>
	</div>
	<!-- END: call_history -->

	<!-- BEGIN: media_history -->
	<div id="media_history_detail_div">
	<div style="text-align:right;"><a href="javascript: void(0);" onClick="viewMediaHistorySummary();">&#171; Back</a></div>
	<table width="95%" cellpadding="3" cellspacing="1">
		<tr><th colspan="2">Media Loan #{media_id}</th></tr>
		<tr><th>Memo</th><td>{memo}</td></tr>
		<tr><th>Requested</th><td>{request_items}</td></tr>
		<tr><th>Location</th><td>{location}</td></tr>
		<tr><th>Start Date</th><td>{start_date}</td></tr>
		<tr><th>End Date</th><td>{end_date}</td></tr>
	</table>
	<table width="95%" cellpadding="3" cellspacing="1">
		<tr><th colspan="2">Equipment Items</th></tr>
			<!-- BEGIN: media_items -->
				<!-- BEGIN: last_media_item -->
				<th width="95%" colspan="2"><hr/></th>
				<!-- END: last_media_item -->
			<tr><td>Item ID</td><td>{item_id}</td></tr>
			<tr><td>Category</td><td>{category}</td></tr>
			<!-- END: media_items -->
	</table>
	</div>
	<!-- END: media_history -->

	<!-- BEGIN: displayOpenCalls -->
		<h2>&#187; Open Calls</h2>
		{displayOpenCalls}
		<!-- BEGIN: group -->
			<a class="nav_link open_calls" href="index.html?new_call=passed&amp;action=view_open_calls&amp;option={type}&amp;group={my_group}&amp;find_type={open_call_type}" title="{title}">{my_group_name} (<span id="open_calls_num_rows">{numberOfRows}</span>)</a>
		<!-- END: group -->
		<div class="margin-botton"></div>
	<!-- END: displayOpenCalls -->

	<!-- BEGIN: open_calls -->
	<div id="main-section no-border" style="text-align: left;">
	<div class="submit_new_call" style="float: right;margin-top:0.5em;"><a href="javascript: void(0);" onClick="submit_new_call();" class="btn">Submit New Call</a></div>
	<h2>Open Calls &#187; <span style="color: #666;">{open_call_type}</span></h2>

		<!-- BEGIN: open_calls_table -->
		<div id="open_calls_main_div">
			<table class="grid">
				<thead>
					<tr>
						<th><a href="javascript: void(0);" onClick="sortField('{GET.option}', {GET.group}, '{GET.open_call_type}', 'call_date');">Opened</a></th>
						<th><a href="javascript: void(0);" onClick="sortField('{GET.option}', {GET.group}, '{GET.open_call_type}', 'call_updated');">Updated</a></th>
						<th><a href="javascript: void(0);" onClick="sortField('{GET.option}', {GET.group}, '{GET.open_call_type}', 'caller_last_name');">Caller</a></th>
						<th><a href="javascript: void(0);" onClick="sortField('{GET.option}', {GET.group}, '{GET.open_call_type}', 'call_priority');">Priority</a></th>
						<th><a href="javascript: void(0);" onClick="sortField('{GET.option}', {GET.group}, '{GET.open_call_type}', 'its_assigned_group ASC, tlc_assigned_to');">Assigned To</a></th>
					</tr>
				</thead>
				<tbody>
					<!-- BEGIN: open_call_details -->
					<tr class="call">
						<td class="call-age-status-{row.call_age_status}" style="text-align: center; white-space:nowrap;">{row.call_date}<br/>{row.call_time}</td>
						<td class="activity-age-status-{row.activity_age_status}" style="text-align: center; white-space:nowrap;">{row.date_assigned}<br/>{row.time_assigned}</td>
						<td>
							<div class="call-title">{row.call_title}</div>
							<div>
								<a href="{CALL_LOG_WEB_HOME}/ticket/{row.call_id}/?action=view_open_calls&option={open_call_option}&group={group_number}&find_type={open_call_type}" class="view">{row.name_full} <em>({row.caller_username})</em></a> 
								<span class="fade">[#{row.call_id}]</span>
							</div>
							<div class="summary">{row.call_summary}</div>
						</td>
						<td class="priority-status status-{row.call_priority}">{row.call_priority}{row.feelings_face}</td>
						<!-- BEGIN: assigned_open_call -->
						<td style="text-align: center">{row.assigned_to}</td>
						<!-- END: assigned_open_call -->
					</tr>
					<!-- END: open_call_details -->
				</tbody>
			</table>
			<small class="average-open-time">Average Open Call Time: {average_open_call_time}</small>
		</div>
		<!-- END: open_calls_table -->
		<!-- BEGIN: no_open_calls -->
		<h3 align="center">There are currently no <em>{open_call_type}</em> open calls.</h3>
		<!-- END: no_open_calls -->
	</div>
	<!-- END: open_calls -->

	<!-- BEGIN: blog_post -->
	<a href="javascript: submit_new_call();" class="submit_new_call">Submit New Call</a>
	<h2>&#187; Help Desk News</h2>
	<div class="summary_text">{blog_title}</div><br/>
	<div>Published: {blog_pubdate}</div>
	<div>Posted Under: {blog_category} by {blog_creator}</div>
	<div align="left">{blog_encoded}</div>
	<!-- END: blog_post -->

	<!-- BEGIN: highlight_call_history -->
	<script>
	new Effect.Highlight('CallHistoryRow1', {startcolor:'#D19275', endcolor:'#FFEFD5', restorecolor:'#FFEFD5'});
	</script>
	<!-- END: highlight_call_history -->

	<!-- BEGIN: restore_request -->
		<!-- BEGIN: restore_request_error -->
			<script>
			restoreRequestError();
			</script>
			<div id="restore_request_error" style="text-align: center; font-weight:bold;">
			Please Complete All Fields
			</div>
		<!-- END: restore_request_error -->

		<!-- BEGIN: restore_request_complete -->
			Thank You For Submitting a Restore Request<br/>
			<a href='javascript: newRestoreRequest();'>File Another Restore Request</a>
		<!-- END: restore_request_complete -->
	<!-- END: restore_request -->


	<!-- BEGIN: queueEmailMessage -->
		Your message has been sent<br/>
		<a href="javascript: sendHelpDeskMail('{caller_user_name}@{caller_class}plymouth.edu',0,'reset');">Back to Caller Information</a>
	<!-- END: queueEmailMessage -->

	<!-- BEGIN: group_names -->
	{group_names}
	<!-- END: group_names -->

	<!-- BEGIN: assign_reorder -->
		<div id="re-order-div" style="padding-bottom:10px;">
		Re-order: <a href="javascript: reorder_assign_history('old', '{caller_user_name}', {call_id});" class="current_link">Old-New</a> | <a href="javascript: reorder_assign_history('new', '{caller_user_name}', {call_id});" class="current_link">New-Old</a></div>
		<div id="assign-order-div" class="new-call-user-table-inside" style="border:0px; display:inline;">
		<!-- BEGIN: call_assignment_history -->
			<div id="call_assignment_history_inner_div" class="new-call-user-table-inside" style="border:0px; display:inline;">
				<fieldset>
				<legend>Assignment History</legend>
					<label class="label"><em>Updated by:</em> {call_assignment_history_updated} on {call_assignment_history_date} @ {call_assignment_history_time}</label><br/><br/>
					<label class="label"><em>Details:</em> {call_assignment_history_comments}</label><br/>
					<hr style="width: 75%;" align="left">
					<label class="label"><em>Call Priority:</em></label> {call_assignment_history_priority}<br/>
					<label class="label"><em>Call Status:</em></label> {call_assignment_history_status}<br/>
					<label class="label"><em>Assigned To:</em></label> {call_assignment_history_assigned_to}<br/>
				</fieldset>
			</div>
		<!-- END: call_assignment_history -->
		</div>
	<!-- END: assign_reorder -->

<!-- END: main -->
