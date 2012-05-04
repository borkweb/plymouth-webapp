<!-- BEGIN: main -->
<!-- BEGIN: queue -->
<h2>{queue.name}</h2>

<table class="calllog_queue secondary_table_block" cellspacing="0" cellpadding="2" width="100%">
	<thead><tr>
		<td class="secondary_table_header">Date</td>
		<td class="secondary_table_header">Caller</td>
		<td class="secondary_table_header">Priority</td>
		<td class="secondary_table_header">Assigned To</td>
	</tr></thead>
	<tbody>
	<!-- BEGIN: call -->
	<tr>
		<td class="secondary_table_cell_1"><a href="{PHP.BASE_URL}/ticket/{call.call_id}/" target="_blank">{call.call_date}</a></td>
		<td class="secondary_table_cell_1"><a href="{PHP.BASE_URL}/ticket/{call.call_id}/" target="_blank">{call.caller_last_name} {call.caller_first_name} <em>{call.caller_username}</em></a></td>
		<td class="secondary_table_cell_1"><a href="{PHP.BASE_URL}/ticket/{call.call_id}/" target="_blank">{call.call_priority}</a></td>
		<td class="secondary_table_cell_1"><a href="{PHP.BASE_URL}/ticket/{call.call_id}/" target="_blank">{call.tlc_assigned_to}</a></td>
	</tr>
	<!-- END: call -->
	</tbody>
</table>
<!-- END: queue -->

<p>
	<span class="updated">Updated {channel_updated}</span>
	<a href="#" class="refresh">Refresh</a>
</p>

<!-- END: main -->
