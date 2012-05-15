<ul class="grid_16">
	<li><a href="{$PHP.BASE_URL}">Home</a></li>
	<li><a href="{$PHP.BASE_URL}/search/">Detail Search</a></li>
	<li><a href="https://go.plymouth.edu/cts">Media Loans</a></li>
	<li><a href="{$PHP.BASE_URL}/tools.html">Tools</a></li>
	<li><a href="{$PHP.BASE_URL}/graphs/statistics.html">Statistics</a></li>
	<li><a href="{$PHP.BASE_URL}/my_options.html">Options</a></li>
	{if $PHP.calllog_admin}
	<li><a href="{$PHP.BASE_URL}/admin/">Admin</a>
		<ul>
			<li><a href="{$PHP.BASE_URL}/admin/manage_users.html">Manage Users</a></li>
			<li><a href="{$PHP.BASE_URL}/admin/call_log_keyword_admin.html">Manage Keywords</a></li>
			<li><a href="{$PHP.BASE_URL}/admin/employee_calls.html">Manage Employee Calls</a></li>
		</ul>
	</li>
	{/if}
</ul>
