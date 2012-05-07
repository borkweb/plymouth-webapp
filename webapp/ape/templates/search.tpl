{box title="Search Results" class=$window.class}
{if $search_results}
		<table class="grid">
			<tr>
				<th>PSU ID</th>
				<th>First Name</th>
				<th>Middle Name</th>
				<th>Last Name</th>
				<th>Username</th>
				{if $search_type eq 'email'}
					<th>Email</th>
				{/if}
				{if $search_type == 'device'}
					<th>Device Name</th>
					<th>IP Address</th>
					<th>MAC Address</th>
				{/if}
				<th>&nbsp;</th>
			</tr>
			{foreach from=$search_results item=name}
			<tr>
				<td>{$name.r_id}</td>
				<td>{$name.r_first_name}</td>
				<td>{$name.r_mi}</td>
				<td>
					{$name.r_last_name}
					{if $name.last_names}
					{foreach from=$name.last_names item=lname}
					<div class="fade">Formerly: {$lname}</div>
					{/foreach}
					{/if}
				</td>
				<td>{if $name.r_ldap_user}{$name.r_ldap_user}{else}{$name.r_wp_id}{/if}</td>
				{if $search_type eq 'email'}
					<td>{$name.r_email_address} ({$name.r_emal_code})</td>
				{/if}
				{if $search_type == 'device'}
					<td><a href="{$PHP.BASE_URL}/hardware/u/{$name.r_ldap_user}">{$name.computer_name}</a></td>
					<td><a href="{$PHP.BASE_URL}/hardware/u/{$name.r_ldap_user}">{$name.ip_address}</a></td>
					<td><a href="{$PHP.BASE_URL}/hardware/u/{$name.r_ldap_user}">{$name.mac_address}</a></td>
				{/if}
				<td>
					<a href="{$PHP.BASE_URL}/user/{if $name.r_ldap_user}{$name.r_ldap_user}{elseif $name.r_id}{$name.r_id}{else}{$name.r_wp_id}{/if}" title="View Identity/Access Data">{icon id="ape-person" boxed=true}</a>
					{if $student_link}<a href="{$PHP.BASE_URL}/user/student/{$name.r_ldap_user}" title="View Student Data">{icon id="ape-student" boxed=true}</a>{/if}
					{if $advancement_link}<a href="{$PHP.BASE_URL}/user/advancement/{$name.r_ldap_user}" title="View Advancment Data">{icon id="ape-advancement" boxed=true}</a>{/if}
					{if $family_link}<a href="{$PHP.BASE_URL}/user/family/{$name.r_ldap_user}" title="View Family Data">{icon id="ape-family" boxed=true}</a>{/if}
				</td>
			</tr>
			{/foreach}
		</table>
{else}
	<p>No users matched your search.</p>
{/if}
{/box}
