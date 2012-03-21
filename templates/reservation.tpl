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
{box size="16" title="Reservation Details from $start_date to $end_date"}
<table class="sortable">
	<thead>
		<th class="header">Reservation Index</th>	
		<th class="header">First Name</th>	
		<th class="header">Last Name</th>	
		<th class="header">Phone</th>	
		<th class="header">Email</th>	
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
			<td>{$id}</td>
			<td>{$reserve.fname}</td>		
			<td>{$reserve.fname}</td>
			<td>{$reserve.phone}</td>
			<td>{$reserve.email}</td>		
			<td>{$reserve.start_date}</td>		
			<td>{$reserve.start_time}</td>		
			<td>{$reserve.end_date}</td>		
			<td>{$reserve.end_time}</td>		
			<td>{$locations[$reserve.building_idx]}</td>
			<td>{$reserve.title}</td>		
			<td>{$reserve.status}</td>		
		</tr>
	{/foreach}
	</tbody>
</table>
{/box}
