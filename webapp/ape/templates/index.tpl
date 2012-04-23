<style>
.front-nav li{
	margin-bottom: 1em;
}
</style>
{box title="Welcome to the PSU Analysis and Provisioning Engine!"}
	<p>
		If you would like to analyze a specific user, search for one here:
	</p>
	{include file="user_search.tpl"}
	<p style="margin-top: 2em;">
		Here are some other services in APE that you may find useful:
	</p>
	<ul class="front-nav">
		<li><a href="{$PHP.BASE_URL}/authz.html"><img src="{$PHP.ICONS}/32x32/categories/applications-internet.png"> Access Management</a></li>
		{if $AUTHZ.oracle.reporting_security}
			<li class="{$banner_current}"><a href="{$PHP.BASE_URL}/banner/"><img src="{$PHP.ICONS}/32x32/emblems/emblem-readonly.png"> Banner Security</a></li>
		{/if}
		{if $ape->canResetPassword()}
			<li><a href="{$PHP.BASE_URL}/password-test.html"><img src="{$PHP.ICONS}/32x32/actions/edit-find-replace.png"> Password Test</a></li>
			<li><a href="{$PHP.BASE_URL}/locks.html"><img src="{$PHP.ICONS}/32x32/actions/system-lock-screen.png"> Locked Accounts ({$ape->locks_count()})</a></li>
		{/if}
	</ul>	
{/box}

{box title="Connect.ply Login Errors"}
	<p>Connect.plymouth.edu logs the following events during the login process, adding them
	together to return a code during login errors:</p>
	<table class="grid" id="sl-error-codes">
		<tr>
			<th>Value</th>
			<th>Description</th>
		</tr>
		<tr>
			<td>1</td>
			<td>Username field did not match against any username or email address</td>
		</tr>
		<tr>
			<td>2</td>
			<td>Username matched an existing PSU username in Banner</td>
		</tr>
		<tr>
			<td>4</td>
			<td>Associating PSU username with existing WordPress account</td>
		</tr>
		<tr>
			<td>8</td>
			<td>Associating PSU username with new WordPress account</td>
		</tr>
		<tr>
			<td>16</td>
			<td>An external authenticate filter failed</td>
		</tr>
		<tr>
			<td>32</td>
			<td>Password did not match in WordPress</td>
		</tr>
		<tr>
			<td>64</td>
			<td>Password did not match in Active Directory</td>
		</tr>
	</table>
	<p>Enter a code here to highlight the events triggered above:</p>
	<ul class="label-left">
		<li>
			<label for="sl-error-code">Error code:</label>
			<input id="sl-error-code" type="text">
		</li>
	</ul>
{/box}
