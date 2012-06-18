{box title="Plymouth APE" size=16 style=hero}
	<p>
		APE &mdash; Analysis and Provisioning Engine
	</p>
	{include file="user_search.tpl"}
{/box}

{box title="Connect.ply Login Errors" size=16}
	<p>Connect.plymouth.edu logs the following events during the login process, adding them
	together to return a code during login errors:</p>
	<table class="table table-striped table-bordered table-condensed" id="sl-error-codes">
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
