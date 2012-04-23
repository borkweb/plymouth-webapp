{col size="4"}
	{box title="Logs"}
	<p>Logs for <a href="{$PHP.BASE_URL}/user/{$person->login_name|default:$person->wp_id}">{$person->formatName('f l')}</a>.</p>
	<ul>
		<li><a href="?">Combined Logs</a></li>
		<li><a href="?log=audit">Audit Log</a></li>
		<li><a href="?log=expiration">Password Expiration Log</a></li>
		<li><a href="?log=change">Password Change Log</a></li>
		<li><a href="?log=logins">Recent (WordPress) Portal Logins</a></li>
		<li><a href="?log=lum_logins">Recent (Luminis) Portal Logins</a></li>
	</ul>
	{/box}
{/col}
{col size="12"}
{capture name="title"}{$log_name} Logs for <a href="{$PHP.BASE_URL}/user/{$person->login_name|default:$person->wp_id}">{$person->formatName('f l')}</a>.{/capture}
{box title=$smarty.capture.title}
	{if $smarty.get.log == 'audit'}
		{if $tables.audit_log}
		<table class="grid">
			<thead><tr><th>Timestamp</th><th>Application</th><th>Username</th><th>Log</th></tr></thead>
			<tbody>
			{foreach from=$tables.audit_log item=row}
				<tr>
					<td>{$row.activity_date|date_format:"%b %e, %Y %l:%M %P"}</td>
					<td>{$row.name}</td>
					<td>{$row.username}</td>
					<td>{$row.log}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{else}
			<p>No records in the audit log.</p>
		{/if}
	{elseif $smarty.get.log == 'expiration'}
	{if $tables.password_expiration}
		<table class="grid">
			<thead><tr><th>Added</th><th>Refreshed</th><th>Reason</th><th>Seen</th><th>Changed</th></tr></thead>
			<tbody>
			{foreach from=$tables.password_expiration item=row}
				<tr>
					<td>{$row.added|date_format:"%b %e, %Y %l:%M %P"}</td>
					<td>{$row.refreshed|date_format:"%b %e, %Y %l:%M %P"}</td>
					<td>{$row.reason}</td>
					<td>{$row.seen|date_format:"%b %e, %Y %l:%M %P"}</td>
					<td>{$row.changed|date_format:"%b %e, %Y %l:%M %P"}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{else}
			<p>No records in the password expiration log.</p>
		{/if}
	{elseif $smarty.get.log == 'change'}
		{if $tables.password_log}
		<table class="grid">
			<thead><tr><th>Timestamp</th><th>Message</th></tr></thead>
			<tbody>
			{foreach from=$tables.password_log item=row}
				<tr>
					<td>{$row.stamp|date_format:"%b %e, %Y %l:%M %P"}</td>
					<td>{$row.message}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{else}
			<p>No records in the password change log.</p>
		{/if}
	{elseif $smarty.get.log == 'logins'}
		<p>Most recent 25 logins. Hostnames updated every five minutes.</p>
		{if $tables.portal_logins}
		<table class="grid">
			<thead>
				<tr>
					<th>Login Time</th>
					<th>IP</th>
					<th>Hostname</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$tables.portal_logins item=row}
				<tr>
					<td>{$row.activity_date|date_format:"%b %e, %Y %l:%M %P"}</td>
					<td>{$row.ip}</td>
					<td>{$row.hostname}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{else}
			<p>No records in the login log.</p>
		{/if}
	{elseif $smarty.get.log == 'lum_logins'}
		<p>Most recent 25 logins. Hostnames updated every five minutes.</p>
		{if $tables.luminis_logins}
		<table class="grid">
			<thead>
				<tr>
					<th>Login Time</th>
					<th>Username</th>
					<th>Role String</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$tables.luminis_logins item=row}
				<tr>
					<td>{$row.activity_date|date_format:"%b %e, %Y %l:%M %P"}</td>
					<td>{$row.username}</td>
					<td>{$row.role_string}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{else}
			<p>No records in the login log.</p>
		{/if}
	{else}
		{if $combined}
		<table class="grid">
			<thead>
				<tr>
					<th>Timestamp</th>
					<th>Section</th>
					<th colspan="2">Message</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$combined item=row}
				<tr>
					<td>{$row.combined_key|date_format:"%b %e, %Y %l:%M %P %Z"}</td>
					<td>{$row.combined_desc}</td>
					<td{if $row.application != 20} colspan="2"{/if}>{$row.combined_longdesc}</td>
					{if $row.application == 20}
					<td>by: {$row.username}</td>
					{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
		{else}
			<p>No records in the combined log.</p>
		{/if}
	{/if}
{/box}
{/col}
