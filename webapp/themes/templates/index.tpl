{col size="16"}
{box title="Select a Theme"}
	{if $admin && $specific}<h2>You are making a selection for {$specific} (<a href="{$PHP.BASE_URL}?cancel_specific=1">cancel</a>)</h2>{/if}
	<p class="description">
		You can <strong>preview</strong> a theme below by clicking on its associated image.  To <strong>set</strong> your theme, simply click on the theme's name.
	</p>
	{if $event_theme}
	<div class="message-container">
		<div class="message message-messages">
			<p>
				The <strong>{$event_theme.name}</strong> theme is currently active.
			</p>
			<p>
				Want your selected theme back?  <a href="{$PHP.BASE_URL}/optout.html">Opt-out</a> of the event theme <a href="{$PHP.BASE_URL}/optout.html">here</a>.
			  You may read more about event themes, theme selection, and theme creation in <a href="http://go.plymouth.edu/themepractices">Plymouth State's Theme Practices</a> document.
			</p>
		</div>
	</div>
	{else}
		<p class="note">
			Curious about PSU themes? You can read more about event themes, theme selection, and theme creation in <a href="http://go.plymouth.edu/themepractices">Plymouth State's Theme Practices</a> document.
		</p>
	{/if}
	<ul class="theme_list">
		{foreach from=$themes item=theme}
			{if $theme.days_old <= 14}
				{assign var="new_theme" value=true}
			{else}
				{assign var="new_theme" value=false}
			{/if}
			{if ($smarty.session.AUTHZ.permission.mis && $smarty.session.AUTHZ.permission.theme_admin) || ($theme.mercury_status == 'enabled' || $theme.mercury_status == 'only') }
			<li class="grid_3{if $theme.id == $current_theme} selected{/if}{if $new_theme} new-theme{/if}">
				<a href="{$PHP.BASE_URL}?theme={$theme.code}"><img src="{$PHP.WEBAPP_URL}/themes/my/themes/{$theme.code|regex_replace:"/.+\:/":""}/screenshot_thumb.jpg" class="screenshot"/></a>
				<a href="{$PHP.BASE_URL}?theme={$theme.code}">{$theme.name}</a><br/><small>(<a href="{$PHP.WEBAPP_URL}/themes/my/themes/{$theme.code|regex_replace:"/.+\:/":""}/screenshot.jpg" class="thickbox">Preview</a>)</small>
				<div class="clear"></div>
			</li>
			{/if}
		{/foreach}
	</ul>
	<div class="clear"></div>
	<script src="https://connect.facebook.net/en_US/all.js#xfbml=1"></script>
	<fb:like href="http://www.facebook.com/pages/MyPlymouth-Themes/184142748286719" show_faces="true" width="900"></fb:like>
{/box}
{box title="Popular Themes"}
	<p class="description">
		There are {$num_themes_set|number_format:0} users with non-default themes.
	</p>
	<table class="data grid" style="float: left;">
		<tr>
			<th>Theme</th>
			<th>Users</th>
		</tr>
		{foreach from=$popular item=theme}
		<tr>
			<td>
			{if $theme.mercury_status == 'enabled' || $theme.mercury_status == 'only' }
				<a href="{$PHP.BASE_URL}?theme={$theme.code}">{$theme.name}</a>
			{else}
				<span title="This theme is not available for selection.">{$theme.name}</span>
			{/if}
			</td>
			<td style="text-align: center;">
			{if $admin}
				<a href="{$PHP.BASE_URL}?who={$theme.code}#stats">{$theme.count|number_format:0}</a>
			{else}
				{$theme.count|number_format:0}
			{/if}
			</td>
		</tr>
		{/foreach}
	</table>
	{if $admin && $theme_users}
	<div class="users">
	<h3>Users using {$who.name}</h3>
	<ul>
		{foreach from=$theme_users item=theme_user}
			<li><a href="https://www.plymouth.edu/webapp/ape/user/{$theme_user.username}" target="_blank" id="user_{$theme_user.username}">{$theme_user.name_last}, {$theme_user.name_first}</a> {if $theme_user.set_by <> 'user'}<span class="set-by">({$theme_user.set_by})</span>{/if}</li>
		{/foreach}
	</ul>
		{foreach from=$theme_users item=theme_user}
		<script>
		$(function(){
			$('#user_{$theme_user.username}').popover({
				title: '{$theme_user.name_last}, {$theme_user.name_first}',
				content: '<img src="/webapp/idcard/u/{$theme_user.username}" height="130" width="98"/>'
			});
		});
		</script>
		{/foreach}
	</div>
	{/if}
	<br style="clear:both;"/>
{/box}
{/col}
