{include file="admin-sidebar.tpl"}
{col size="12"}
	{box title="Welcome"}
		<p>This is the MyPlymouth administration screen.</p>
		<ul>
			<li><a href="{$PHP.BASE_URL}/admin/channel/">Add a new channel</a></li>
			<li><a href="{$PHP.BASE_URL}/admin/set-layout/0">Enable default layout</a></li>
			<li>Enable layout for: <form class="inline" method="get" action="{$PHP.BASE_URL}/admin/set-layout/"><input type="text" name="wp_id" size="10" maxlength="9"> <input type="submit" value="Go"></form></li>
			<li>Enable generic user layout: <form class="inline" method="get" action="{$PHP.BASE_URL}/admin/set-type/"><select name="generic_role"><option value="public">public</option><option value="portalord">PortaLord</option></select> <input type="submit" value="Go >>"></form></li>
			<li><a href="{$PHP.BASE_URL}/admin/push/">Push default channels to users</a></li>
		</ul>
	{/box}

	<div class="clear"></div>

	{box size="6" title="Channels" id="myp-content" class="channel grid_6 alpha" no_grid=true}
		<ul class="channel-list">
		{foreach from=$channels item=channel}
			<li class="channel-info">
				<div class="grid_5 channel-info-body">
					<a href="{$PHP.BASE_URL}/admin/channel/{$channel->id}"><h4>{$channel->name}</h4></a>
					{$channel->description}
					<p class="fade small"><em>{$channel->target_names}</em></p>
				</div>
				<div class="clear"></div>
			</li>
		{/foreach}
		</ul>
	{/box}

	{box size="6" title="Tabs" class="tab myp-content grid_6 omega" no_grid=true}
		<ul class="tab-list">
		{foreach from=$tabs item=tab}
			<li class="tab-info">
				<div class="grid_5 tab-info-body">
					<a href="{$PHP.BASE_URL}/admin/tab/{$tab->id}"><h4>{$tab->name}</h4></a>
					{$tab->description}
				</div>
				<div class="clear"></div>
			</li>
		{/foreach}
		</ul>
	{/box}
{/col}
