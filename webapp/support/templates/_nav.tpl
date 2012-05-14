<ul class="grid_16">
{if $smarty.session.username}
	<li><a href="{$PHP.BASE_URL}" class="nav-home">My Tickets</a></li>
	<li><a href="{$PHP.BASE_URL}/submit" class="nav-submit">Create A Ticket</a></li>
	<li><a href="{$PHP.BASE_URL}/ip" class="nav-browser">My Browsing Info</a></li>
{else}
	<li><a href="{$PHP.BASE_URL}">Please Log In</a></li>
{/if}
</ul>
