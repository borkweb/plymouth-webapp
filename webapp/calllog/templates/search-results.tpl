<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th>{$search.fields.1}</th>
			<th>{$search.fields.2}</th>
			<th>{$search.fields.3}</th>
			<th>{$search.fields.4}</th>
			{if $search.fields.5}<th>{$search.fields.5}</th>{/if}
		</tr>
	</thead>
	<tbody>
	{foreach from=$search.results item=row}
		{if 'ticket' == $search.search_type}
			<tr>
				<td>
					<a href="{$PHP.BASE_URL}/ticket/{$row.call_id}/?option={$search.search_string}&find_type={$search.search_type}" title="Edit Call For {$row.caller_username} and Ticket Number {$row.call_id}" class="nav_link">{$row.call_id}</a>
				</td>
				<td>{$row.caller_first_name} {$row.caller_last_name}</td>
				<td>{$row.caller_username}</td>
				<td>{$row.call_date} @ {$row.call_time}</td>
				<td>{$row.calllog_username}</td>
			</tr>
		{elseif 'computer' == $search.search_type}
			<tr>
				<td><a href="{$PHP.BASE_URL}/new_call.html?caller={$row.HW_Username}&action=searchUser&option={$search.search_string}&group=none&find_type={$search.search_type}&page=index.html" title="Edit Call For {$row.caller_username} and Ticket Number {$row.call_id}" class="nav_link">{$row.HW_IPName}</a></td>
				<td>{$row.MACAddress}</td>
				<td>{$row.IPAddress}</td>
				<td>{$row.location}</td>
			</tr>
		{elseif 'ip' == $search.search_type}
			<tr>
				<td><a href="{$PHP.BASE_URL}/new_call.html?caller={$row.HW_Username}&action=searchUser&option={$search.search_string}&group=none&find_type={$search.search_type}&page=index.html" title="Edit Call For {$row.caller_username} and Ticket Number {$row.call_id}" class="nav_link">{$row.IPAddress}</a></td>
				<td>{$row.HW_Name}</td>
				<td>{$row.HW_Username}</td>
				<td>{$row.MACAddress}</td>
				<td>{$row.HW_IPName}</td>
			</tr>
		{elseif 'mac' == $search.search_type}
			<tr>
				<td><a href="{$PHP.BASE_URL}/new_call.html?caller={$row.HW_Username}&action=searchUser&option={$search.search_string}&group=none&find_type={$search.search_type}&page=index.html" title="Edit Call For {$row.caller_username} and Ticket Number {$row.call_id}" class="nav_link">{$row.MACAddress}</a></td>
				<td>{$row.HW_Name}</td>
				<td>{$row.HW_Username}</td>
				<td>{$row.HW_IPName}</td>
				<td>{$row.IPAddress}</td>
			</tr>
		{elseif 'closed' == $search.search_type || 'user' == $search.search_type}
			<tr>
				<td>
					<a href="{$PHP.BASE_URL}/new_call.html?caller={$row.key.caller_username}&call_id={$row.key.call_id}&action=searchUser&option={$search.search_string}&group=none&find_type={$search.search_type}&page=index.html" title="{if 'closed' == $search.search_type}Re-open{else}Create New{/if} Call For {$row.key.caller_username}." class="nav_link">{$row.key.call_id}</a></td>
				<td>{$row.key.caller_first_name} {$row.key.caller_last_name} ({$row.key.caller_username})</td>
				<td>{$row.key.call_date}</td>
				<td>{$row.key.call_time}</td>
				<td>{$row.key.comments}</td>
			</tr>
		{else}
			<tr>
				{if $row.key.email}
				<td>
					<a href="{$PHP.BASE_URL}/user/{$row.key.username}/?option={$search.search_string}&find_type={$search.search_type}" title="Create New Call For {$row.key.email}." class="nav_link">{$row.key.name_full}</a>
					<a href="{$PHP.BASE_URL}/calls/for/{$row.key.username}/" title="Open calls for {$row.key.username}">{$row.key.open_call}</a>
				</td>
				<td>{$row.key.email}</td>
				{else}
				<td>
					<a href="{$PHP.BASE_URL}/user/{$row.key.identifier}/" title="Create New Call." class="nav_link">{$row.key.name_full}</a> 
					(<a href="https://www.plymouth.edu/webapp/ape/user/{$row.key.pidm}/" target="_blank">APE</a>)
				</td>
				<td><span style="color:red;font-weight:bold;">no username</span></td>
				{/if}
				<td>{$row.key.phone_of} {$row.key.phone_vm}</td>
				<td title="{$row.key.title} {$row.key.major}">{$row.key.title} {$row.key.major}</td>
				<td>{$row.key.dept}</td>
			</tr>
		{/if}
		{/foreach}
	</tbody>
</table>
