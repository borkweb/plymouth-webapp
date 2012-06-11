{col size="4"}
	{box title="Browse Content" no_title_size=true no_grid=true}
		<h3>Sort By</h3>
		<ul>
			<li><a href="{$PHP.BASE_URL}/channels/name/">Name</a></li>
			<li><a href="{$PHP.BASE_URL}/channels/popular/">Most Users</a></li>
			<li><a href="{$PHP.BASE_URL}/channels/newest/">Newest</a></li>
		</ul>
<!--
		<h3>Narrow By Category</h3>
		<ul>
			<li><a href="">All Categories</a></li>
			<li><a href="">Entertainment</a></li>
			<li><a href="">News</a></li>
			<li><a href="">Tools</a></li>
		</ul>
-->
	{/box}
{/col}
{col size="12"}
	{include file="paging.tpl"}
	{box title="myPlymouth Content" class="channel" id="myp-content" no_grid=true no_title_size=true}
		<ul class="channel-list">
			{foreach from=$channels item=channel}
				<li class="channel-info" id="channel-{$channel->id}">
					<div class="grid_2 alpha channel-info-options">
						{$channel->users} users<br/>
						<a href="" class="add-channel">Add it now</a>
					</div>
					<div class="grid_9 channel-info-body">
					<h4>{$channel->name}</h4>
					{$channel->description}
					</div>
					<div class="clear"></div>
				</li>
			{/foreach}
		</ul>
	{/box}

	{box style="clear" no_grid=true}
	 	<small><em><a id="reset-portal-layout" href="{$PHP.BASE_URL}/admin/reset">Reset Portal to Default for {$portal->person->wp_email}</a></em></small>
	{/box}
{/col}
