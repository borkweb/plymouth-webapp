<ul class="grid_16">
	<li><a href="{$PHP.BASE_URL}/user/">Home</a></li>
	<li><a href="{$PHP.BASE_URL}/reserve/new">New Reservation</a></li>
	<li><a href="{$PHP.BASE_URL}/history/">History</a></li>
	{if $user_level <=2}
		<li><a href="{$PHP.BASE_URL}/admin/equipment">Equipment</a></li>
		<li><a href="{$PHP.BASE_URL}/admin/reservation">Search Reservations</a></li>
	{/if}
	{if $user_level <=1}
		<li><a href="{$PHP.BASE_URL}/admin/mypage">My Page</a></li>
		<li><a href="http://puppis.plymouth.edu/inventory" target="_blank" >GLPI</a></li>

	{/if}
	{if $user_level == 0}
		<li><a href="{$PHP.BASE_URL}/admin/admincp">Admin</a></li>
		<li><a href="{$PHP.BASE_URL}/admin/statistics">Statistics</a></li>
	{/if}
		<li><a href="{$PHP.BASE_URL}/user/help">Help</a></li>

	
</ul>
