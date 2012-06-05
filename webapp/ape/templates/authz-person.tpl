<div style="padding: 1em;">

<img height="130" width="98" src="{$user->idcard()}" class="pull-left">
<div style="margin-left: 110px;">
	<h3>{$user->first_name} {$user->last_name}</h3>
	<ul class="bullets">
		<li>Username: <a href="{$PHP.BASE_URL}/user/{$user->username}">{$user->username}</a></li>
		<li>Pidm: {$user->pidm}</li>
		<li>WP ID: {$user->wp_id}</li>
	</ul>
</div>

<br class="clear">

<hr>

<h3>{$type|ucfirst} Details</h3>
<ul class="bullets">
{foreach from=$logs key=id item=log}
	<li>
		ID: {$log.id}
		<ul class="bullets">
			<li>Granted by: <a href="{$PHP.BASE_URL}/user/{$log.granted_by|escape}">{$log.granted_by|escape}</a></li>
			{if $log.reason}
				<li>Reason: {$log.reason|escape}</li>
			{/if}
			{if $log.source eq $PHP.IDM_SOURCE}
				<li><a class="ajaxify" href="{$PHP.BASE_URL}/actions/idm.php?pidm={$user->pidm}&amp;action=remove&amp;id={$log.id}">Remove {$type}</a></li>
			{/if}
		</ul>
	</li>
{/foreach}
</ul>

</div>
