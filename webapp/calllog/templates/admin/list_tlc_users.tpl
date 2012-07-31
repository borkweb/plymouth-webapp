<table class="sortable grid">
<thead>
	<tr>
		<th>Name</th>
		<th>Phone</th>
		<th>Options</th>
	</tr>
</thead>
<tbody>
	{foreach from=$users item=row}
	<tr>
		<td><font color="red">{$row.last_name}, {$row.first_name}</font> (<a href="tlc_user_profile.html?user_name={$row.user_name}" title="View {$row.first_name} {$row.last_name}'s Profile In A New Window." target="_blank">{$row.user_name}</a>)</td>
		<td align="center">{$row.phone}</td>
		<td align="center"><a href="tlc_user_profile.html?user_name={$row.user_name}" title="View {$row.first_name} {$row.last_name}'s Profile." class="action"> &#187;&#187; View Profile &#187;&#187; </a></td>
	</tr>
	{/foreach}
</tbody>
</table>
