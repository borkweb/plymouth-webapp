{box title="Pending Loans"}
<strong>These are loans that are currently pending.</strong>
<p>Click the index number in the left column to view the loan, or click the copy loan link in the right column to copy the loan into a new reservation.</p>

{assign var="fixed_start_date" value=$start_date|date_format:$date_format}
{assign var="fixed_end_date" value=$end_date|date_format:$date_format}
<table class="grid">
	<thead>
		<th class="header">View</th>	
		<th class="header">Last Name</th>	
		<th class="header">First Name</th>	
		<th class="header">Start Date</th>	
		<th class="header">Start Time</th>	
		<th class="header">End Date</th>	
		<th class="header">End Time</th>	
		<th class="header">Building</th>	
		<th class="header">Title</th>	
		<th class="header">Status</th>
		<th class="header">Copy Loan</th>
	</thead>
	<tbody>
	{foreach from=$reservations item=reserve key=id}
		<tr>
			<td><a class="btn" href="{$PHP.BASE_URL}/history/search/id/{$id}">View Loan</a></td>
			<td>{$reserve.lname}</td>		
			<td>{$reserve.fname}</td>
			<td>{$reserve.start_date|date_format:$date_format}</td>		
			<td>{$reserve.start_time|date_format:$time_format}</td>		
			<td>{$reserve.end_date|date_format:$date_format}</td>		
			<td>{$reserve.end_time|date_format:$time_format}</td>		
			<td>{$locations[$reserve.building_idx]}</td>
			<td>{$reserve.title}</td>		
			<td>{$reserve.status}</td>
			<td><a class="btn" href="{$PHP.BASE_URL}/history/copy/{$id}">Copy Loan</a>
		</tr>
	{/foreach}
	</tbody>
</table>

{/box}
