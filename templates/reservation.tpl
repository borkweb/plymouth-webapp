<nav id="webapp-nav">
  <div class="container">
  </div>
</nav>
{box title="Quick Filter"}

	<a href="{$PHP.BASE_URL}/admin/reservation/search/lastweek">Last Week</a>|
	<a href="{$PHP.BASE_URL}/admin/reservation/search/nextweek">Next Week</a>|
	<a href="{$PHP.BASE_URL}/admin/reservation/search/thisweek">This Week</a>|
	<a href="{$PHP.BASE_URL}/admin/reservation/search/today">Today</a>|
	<a href="{$PHP.BASE_URL}/admin/reservation/search/yesterday">Yesterday</a>|
	<a href="{$PHP.BASE_URL}/admin/reservation/search/tommorrow">Tommorrow</a>|	
	<a href="{$PHP.BASE_URL}/admin/reservation/search/pending">Pending</a>|	
	<a href="{$PHP.BASE_URL}/admin/reservation/search/loaned">Loaned</a>|
	<a href="{$PHP.BASE_URL}/admin/reservation/search/outstanding">Outstanding</a>|
	<a href="{$PHP.BASE_URL}/admin/reservation/search/missing">Missing</a>
{/box}
{assign var="fixed_start_date" value=$start_date|date_format:$date_format}
{assign var="fixed_end_date" value=$end_date|date_format:$date_format}
{box size="16" title="Reservations from $fixed_start_date to $fixed_end_date"}
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
			<td><a href="{$PHP.BASE_URL}/admin/reservation/search/id/{$id}">{$id}</a></td>
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
