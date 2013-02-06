<div id="ape_id_systems" class="ape-section {if $myuser->go_states.ape_id_systems === '0'}ape-section-hidden{/if}">
	<h3>Account Information</h3>
	<ul class="apedata">
	<li>
		<label>Has Account?</label>
		{ape_bool value=$person->system_account_exists} {if $person->system_account_exists}<small>(created: {$person->account_creation_date|date_format:"%B %d, %Y %l:%M:%S %p"})</small>{/if}
	</li>
	<li>
		<label>Has Zimbra Account?</label>
		{ape_bool value=$person->has_zimbra}
	</li>		
	<li>
		<label>Zimbra Email:</label>
		<a
		href="mailto:{$person->zimbra.actual}">{$person->zimbra.actual}</a>
	</li>		
	<li>
		<label class="zimbra-aliases">Zimbra Email Alias:</label>
		<span class="zimbra-aliases">
		<ul class="flush">
		{foreach from=$person->zimbra.alias item=alias}
			<li><a href="mailto:{$alias}">{$alias}</a></li>
		{foreachelse}
			<li><small>No aliases for this user.</small></li>
		{/foreach}
		</ul>
		</span>
	</li>		
	{if $person->oracle_account_exists}
	<li>
		<label>Has Oracle Account?</label>
		{ape_bool value=$person->oracle_account_exists}
		{if $person->oracle_account_exists && $AUTHZ.permission.ape_oracle_lock}
			{if $person->oracle_account_status == 'OPEN'}
				{assign var=oracle_lock_link value="Lock"}
			{elseif substr($person->oracle_account_status, 0, 6) == 'LOCKED'}
				{assign var=oracle_lock_link value="Unlock"}
			{/if}
			({$person->oracle_account_status})

			{if $oracle_lock_link}
				[<a href="{$PHP.BASE_URL}/actions/oracle-lock.php?username={$person->login_name}">{$oracle_lock_link}</a>]
			{/if}
		{/if}
	</li>		
	{/if}
	<li>
		<label>Has Moodle Account?</label>
		{ape_bool value=$person->has_moodle_account}
		{if !$person->has_moodle_account}
			(<a id="create_moodle" href="{$PHP.BASE_URL}/actions/create_moodle.php?pidm={$person->pidm}">Create it</a>)
		{elseif $person->is_moodle_admin}
			<small>(Admin)</small>
		{/if}
	</li>		
	<li>
		<label>Has Moodle 2 Account?</label>
		{ape_bool value=$person->has_moodle2_account}
		{if !$person->has_moodle2_account}
			(<a id="create_moodle2" href="{$PHP.BASE_URL}/actions/create_moodle.php?pidm={$person->pidm}&version=2">Create it</a>)
		{elseif $person->is_moodle2_admin}
			<small>(Admin)</small>
		{/if}
	</li>		
	<li>
		<label>Has Mahara Account?</label>
		{ape_bool value=$person->has_mahara_account}
		{if $person->is_mahara_admin}
			<small>(Admin)</small>
		{/if}
	</li>		
	<li>
		<label>Identified as:</label>
		{foreach from=$person->system_roles item=role}
			{$role}
		{/foreach}
	</li>
	{if $person->system_account_exists && $AUTHZ.permission.ape_profilereset}
		<li>
			<label>Delete Windows profile:</label>
			<a class="ajaxify" id="delete-vista" href="{$PHP.BASE_URL}/actions/profile-reset.php?username={$person->username}&amp;profile=0">Vista roaming</a>, <a class="ajaxify" id="delete-roaming" href="{$PHP.BASE_URL}/actions/profile-reset.php?username={$person->username}&amp;profile=1">terminal services</a>
		</li>
	{/if}
	</ul>
</div>

