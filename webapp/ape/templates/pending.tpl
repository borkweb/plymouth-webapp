{box title="Accounts Pending Creation" size=16}
{if $pending}
<table class="grid">
	<thead>
		<tr>
			<th>Last</th>
			<th>First</th>
			<th>Middle</th>
			<th>Username</th>
			<th>Pidm</th>
		</tr>
	</thead>
	{foreach from=$pending item=user}
	<tr>
		<td>{$user.user_last}</td>
		<td>{$user.user_first}</td>
		<td>{$user.user_middle}</td>
		<td>{$user.user_uname}</td>
		<td><a href="{$PHP.BASE_URL}/user/{$user.pidm}">{$user.pidm}</a></td>
	</tr>
	{/foreach}
</table>
{else}
	<p>There are no accounts pending creation.</p>
{/if}
{/box}
