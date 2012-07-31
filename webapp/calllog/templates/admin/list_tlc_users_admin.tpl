<table class="grid">
<thead>
	<tr>
		<th>Name</th>
		<th>User name</th>
		<th>Work Phone</th>
		<th>Cell Phone</th>
		<th>Home Phone</th>
	</tr>
</thead>
<tbody>
	{foreach from=$users item=row}
	<tr>
		<td><a href="?action=edittlcuser&user_name={$row.user_name}">{$row.last_name}, {$row.first_name}</a></td>
		<td>{$row.user_name}</td>
		<td>{$row.work_phone}</td>
		<td>{$row.cell_phone}</td>
		<td>{$row.home_phone}</td>
	</tr>
	{/foreach}
</tbody>
</table>
