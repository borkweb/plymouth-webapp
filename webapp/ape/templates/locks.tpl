{box title=$title}

<table class="grid">
<tr>
	<th>Full Name</th>
	<th>Username</th>
	<th>Locked</th>
	<th>Locked By</th>
	<th>Status</th>
	<th>Lock Reason</th>
</tr>
{foreach from=$locks item=lock}
	<tr>
		<td><a href="{$PHP.BASE_URL}/user/{$lock.login_name}">{$lock.fullname}</a></td>
		<td>{$lock.login_name}</td>
		<td>{$lock.added}</td>
		<td><a href="{$PHP.BASE_URL}/user/{$lock.locker_pidm}">{$lock.locker}</a></td>
		<td>{$lock.status}</td>
		<td>{$lock.reason}</td>
	</tr>
{/foreach}
</table>
{/box}
