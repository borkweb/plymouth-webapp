<!-- BEGIN: main -->
<script type="text/javascript" language="javascript" charset="utf-8" src="{JS_WEB_DIR}/new_call.js"></script>

<div id="recovered_information">
{displayRecoveryData}
<!-- BEGIN: recovered -->
	You have a call that was recovered, click link to load that call or delete all recoveries: <br/><br/>
	<div id="recovered_info_inside" style="max-height:100px; overflow-y:scroll;">
	<!-- BEGIN: listRecoveredCalls -->
		<a href='{PHP.BASE_URL}/new_call.html?caller={recovered_data.caller_user_name}&call_details={recovered_data.call_details}'>Caller: {recovered_data.caller_user_name} Saved At: {recovered_data.call_date} & {recovered_data.call_time}</a><br/><br/>
	<!-- END: listRecoveredCalls -->
	</div>
<a href="javascript: delete_saved_data('{call_log_username}')">Delete Saved Data</a>
<br/><br/>
<!-- END: recovered -->
</div>

<div id="new-call-table">
	<div id="left-sidebar">
	<div id="main-open-calls">
		<h2>&#187; Open Calls</h2>
			{displayOpenCalls}
			<!-- BEGIN: group -->
				<a class="nav_link open_calls" href="index.html?new_call=passed&amp;action=view_open_calls&amp;option={type}&amp;group={my_group}&amp;find_type={open_call_type}" title="{title}">{my_group_name} (<span id="open_calls_num_rows">{numberOfRows}</span>)</a>
			<!-- END: group -->
		<div class="margin-botton"></div>
	</div> <!-- end main-open-calls -->
	<div id="main-helpdesk-news">
		<h2>&#187; Help Desk News</h2>
		<div class="helpdesk_news">
			{displayNewsFeed}
			<!-- BEGIN: BlogNews -->
			<div><a href="{BlogNewsLink}" target="_blank">{BlogNewsTitle}</a></div>
			<div>{BlogNewsPubDate}
			<br/>by {BlogNewsCreator}<br/>
			Category: {BlogNewsCategory}<br/><br/>
			</div>
			<!-- END: BlogNews -->
			<div class="helpdesk_news_footer"><a href="http://helpdesk.blogs.plymouth.edu/category/its-helpdesk-news/" target="_BLANK">More Help Desk News</a></div>
		</div>
	</div>
	</div>
	<div id="main-section">

		<div id="open_calls_loading" style="display: none;" align="center"><img src="{call_log_web_home}/images/loading-anim.gif" alt="Loading..."/>Loading Content Please Wait...</div>
		<div id="helpdesk_news_loading" style="display: none;" align="center"><img src="{call_log_web_home}/images/loading-anim.gif" alt="Loading..."/>Loading Content Please Wait...</div>

		<div id="main-new-call">
			<h2>&#187; New Call</h2>
			<div class="main-table-properties">
				<div class="summary_text bold_text">
				Search By:
				</div>
				<hr noshade="noshade" class="content" width="17%" />
				<select name="search_type" id="search_type" onchange="document.getElementById('search_string').focus();"> 
				{search_options}
				</select>
				<br /><br />
				<input type="text" size="19" id="search_string" name="search_string" onKeyDown="javascript: KeyCheck(event);" value="{PHP._GET.search_string}{option}"/>
				<br />
				<br/>
				<a href="javascript: void(0);" class="action" onclick="searchUser()">Search >></a><br/><br/>	
			</div>
			<div id="search_results_loading" style="display: none;" align="center"><img src="{call_log_web_home}/images/loading-anim.gif" alt="Loading..."/>Loading Content Please Wait...</div>

			<div id="main-search-results">
				<h2>&#187; Search Results For <em><u>Generic</u></em></h2>
				<table align='center' valign='top' cellpadding='5' cellspacing='1' width='100%' id="calllog-search" class="sortable">
					<thead>
						<tr>
							<th>Name</th>
							<th>Username</th>
							<th>Phone</th>
							<th>Major/Title</th>
							<th>Dept/Class</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><a href='{PHP.BASE_URL}/user/generic/' class='nav_link'>Generic Caller</a></td>
							<td>generic</td>
							<td>N/A</td>
							<td>N/A</td>
							<td>N/A</td>
						</tr>
						<tr>
							<td><a href='{PHP.BASE_URL}/user/kiosk/' class='nav_link'>Kiosk Call</a></td>
							<td>kiosk</td>
							<td>N/A</td>
							<td>N/A</td>
							<td>N/A</td>
						</tr>
						<tr>
							<td><a href='{PHP.BASE_URL}/user/clusteradm/' class='nav_link'>Cluster Call</a></td>
							<td>clusteradm</td>
							<td>N/A</td>
							<td>N/A</td>
							<td>N/A</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div id="graphs">
		<!-- BEGIN: graphs -->
		
		<!-- END: graphs -->
		</div>

	</div>


</div>

<!-- BEGIN: not_js_action -->
	<body onLoad="document.getElementById('search_string').focus(); updateOpenCalls();">
<!-- END: not_js_action -->

<!-- BEGIN: js_action -->
	<script type="text/javascript">
		var action = {action};
		var option = '{option}';
		var group = '{group}';
		var find_type = '{find_type}';
		if(option == 'mygroup' || option == 'unassigned' || option == 'all' || option == 'my'){
			$('search_string').value = "";
		}
		action(option, group, find_type);
	</script>
	<body onLoad="updateOpenCalls();">
<!-- END: js_action -->

<!-- END: main -->
