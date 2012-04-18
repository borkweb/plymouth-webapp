{box title="History"}
<h2>These are loans that you have previously completed.</h2>
<h5>&nbsp;&nbsp;&nbsp;Click the index number in the left column to view the loan.</h5>
{assign var="fixed_start_date" value=$start_date|date_format:$date_format}
{assign var="fixed_end_date" value=$end_date|date_format:$date_format}
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
	{foreach from=$reservations item=reserve key=id}
		<tr>
			<td><a href="{$PHP.BASE_URL}/history/search/id/{$id}">{$id}</a></td>
			<td>{$reserve.lname}</td>		
			<td>{$reserve.fname}</td>
			<td>{$reserve.start_date|date_format:$date_format}</td>		
			<td>{$reserve.start_time|date_format:$time_format}</td>		
			<td>{$reserve.end_date|date_format:$date_format}</td>		
			<td>{$reserve.end_time|date_format:$time_format}</td>		
			<td>{$locations[$reserve.building_idx]}</td>
			<td>{$reserve.title}</td>		
			<td>{$reserve.status}</td>		
		</tr>
	{/foreach}
	</tbody>
</table>

{/box}
