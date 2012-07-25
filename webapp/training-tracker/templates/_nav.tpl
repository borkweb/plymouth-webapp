<ul class ="grid_16">
		<li><a href="/webapp/training-tracker/">Person select</a></li>
		<li><a href="/webapp/training-tracker/team/list">View teams</a></li>
		{if $is_mentor && $has_team}
			<li><a href="/webapp/training-tracker/team/list/{$user->wpid}">View my team</a></li>
		{/if}
		{if $is_admin}
			<li><a href="/webapp/training-tracker/team/builder">Team builder</a></li>
			<li><a href="/webapp/training-tracker/staff/fate">Admin</a></li>
			<li><a href="/webapp/training-tracker/staff/merit">Merits</a></li>
		{/if}
</ul>

