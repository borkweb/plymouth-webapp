<div class="grid_16">
	<select id="tab-box">
		{foreach from=$portal->tabs() item=tab}
		<option value="{$PHP.BASE_URL}/tab/{$tab->slug}/" {if $tab->slug == $current_tab->slug}selected="selected"{/if}>{$tab->base->name}</option>
		{/foreach}
	</select>
	<h1 class="myplymouth_logo"><a href="{$PHP.BASE_URL}/tab/welcome/">myPlymouth</a></h1>
	<a href="http://www.plymouth.edu" class="psu_logo" target="_blank">PSU</a>
	{if $smarty.session.portal.session_stashed}
	<div class="logged-in-as">
	Logged in as <strong>{$smarty.session.username}</strong>
	(Simulated by {$smarty.session.portal.session_stashed.username}. <a href="{$PHP.BASE_URL}/restore-layout">Revert</a>?)
	</div>
	{/if}
</div>
