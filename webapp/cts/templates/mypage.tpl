{box title="My Page"}
<h1> Welcome {$user->first_name}&nbsp;{$user->last_name} </h1>
{if $count}
	<span>There are still {$count} <a target="blank" href="{$PHP.BASE_URL}/admin/reservation/search/pending">pending reservations</a>.</span>
{/if}
{if $reservation == NULL}
<h2>You have no assignments for today.</h2>
{else}
<h3>Here are your assignments for the day.</h3>
<table class="grid">
	<thead>
		<th class="header">Index</th>	
		<th class="header">Last Name</th>	
		<th class="header">First Name</th>	
		<th class="header">Start Date</th>	
		<th class="header">Start Time</th>	
		<th class="header">End Date</th>	
		<th class="header">End Time</th>	
		<th class="header">Building</th>	
		<th class="header">Title</th>	
		<th class="header">Status</th>	
	</thead>
	<tbody>
	{foreach from=$reservation item=reserve key=id}
		<tr>
		{if $reserve.priority=='1' || $reserve.delivery_type=='1'}
			<td><a class= "btn" href="{$PHP.BASE_URL}/admin/reservation/search/id/{$id}">{$id}</a></td>
			<td><strong>{$reserve.lname}</strong></td>		
			<td><strong>{$reserve.fname}</strong></td>
			<td><strong>{$reserve.start_date|date_format:$date_format}</strong></td>		
			<td><strong>{$reserve.start_time|date_format:$time_format}</strong></td>		
			<td><strong>{$reserve.end_date|date_format:$date_format}</strong></td>		
			<td><strong>{$reserve.end_time|date_format:$time_format}</strong></td>		
			<td><strong>{$locations[$reserve.building_idx]}</strong></td>
			<td><strong>{$reserve.title}</strong></td>		
			<td><strong>{$reserve.status}</strong></td>
		{else}
		<td><a class="btn" href="{$PHP.BASE_URL}/admin/reservation/search/id/{$id}">{$id}</a></td>
			<td>{$reserve.lname}</td>		
			<td>{$reserve.fname}</td>
			<td>{$reserve.start_date|date_format:$date_format}</td>		
			<td>{$reserve.start_time|date_format:$time_format}</td>		
			<td>{$reserve.end_date|date_format:$date_format}</td>		
			<td>{$reserve.end_time|date_format:$time_format}</td>		
			<td>{$locations[$reserve.building_idx]}</td>
			<td>{$reserve.title}</td>		
			<td>{$reserve.status}</td>
		{/if}

		</tr>
	{/foreach}
	</tbody>
</table>
{/if}

{/box}

