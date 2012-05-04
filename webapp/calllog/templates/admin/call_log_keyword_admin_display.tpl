<!-- BEGIN: main -->
<table class="content" width="100%" align="center" valign="top" cellpadding="4" cellspacing="1">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Keyword</th>
		<th>Status</th>
		<th colspan="2">Options</th>
	</tr>
	<!-- BEGIN: display_keyword -->
	<tr>
		<td>{key.keyword_id}</td>
		<td>{key.name}</td>
		<td>{key.keyword}</td>
		<td align="center">{status_name}</td>
		<td align="center"><a href="call_log_keyword_admin.html?action=editkeyword&keyword_id={key.keyword_id}" title="Edit {key.name} Keyword." class="action">Edit Keyword</a></td>
		<td align="center"><a href="call_log_keyword_admin.html?action=set_keyword_status&keyword_id={key.keyword_id}&status={other_status}" title="Edit {key.name} Keyword." class="action" onClick="return confirm('Set Keyword {other_status_name}, Are You Sure?')">Set {other_status_name}</a></td>
	</tr>
	<!-- END: display_keyword --> 
</table> 
<!-- END: main -->