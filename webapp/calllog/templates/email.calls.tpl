<html>
<head>

</head>
<body>
{if ! $calls}
	You currently have no open calls.
{else}
	<table>
		<thead>
			<tr>
				<th>Last Update</th>
				<th>Ticket</th>
				<th>Caller</th>
				<th>Priority</th>
				<th>Assigned To</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$calls item=row}
			<tr>
				<td>{$row.date_assigned} @ {$row.time_assigned}</td>
				<td><a href="{$PHP.BASE_URL}/ticket/{$row.call_id}/">#{$row.call_id}: {if $row.call_title}{$row.call_title}{else}[No Title]{/if}</a></td>
				<td>{$row.name_full} <em>({$row.caller_username})</em></td>
				<td>{$row.call_priority}{$row.feelings_face}</td>
				<td>
					<ul class="unstyled">
					{foreach name=assignees from=$row.assigned_to item=assignee}
						<li>{$assignee}</li>
					{/foreach}
					</ul>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
{/if}
</body>
</html>