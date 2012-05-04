{box size=16 title="Search"}
	<form method="get" action="">
	<ul>
		<li><label>Text:</label> <input type="text" name="q" value="{$q|escape}"></li>
		<li><label>&nbsp;</label> <input type="submit" value="Search"></li>
	</ul>
	</form>
{/box}

{if $tickets}
	{box size=16 title="Results}
		<table class="grid">
			<thead>
				<tr>
					<th>Call ID</th>
					<th>Updated By</th>
					<th>Updated</th>
					<th>Status</th>
					<th>Comments</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$tickets item=ticket}
				<tr>
					<td><a href="{$PHP.BASE_URL}/ticket/{$ticket.call_id}">{$ticket.call_id}</a></td>
					<td>{$ticket.updated_by|escape}</td>
					<td>
						{$ticket.date_assigned|escape}&nbsp;{$ticket.time_assigned|escape}
					</td>
					<td>{$ticket.call_status|escape}</td>
					<td>{$ticket.comments|escape|nl2br}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	{/box}
{/if}
