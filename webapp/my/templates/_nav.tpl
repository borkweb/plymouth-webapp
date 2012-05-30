<ul id="tabs" class="grid_16">
	{foreach from=$portal->tabs() item=tab}
	<li id="tab-{$tab->id}" class="{if $tab->slug == $current_tab->slug}selected{/if}"><a href="{$PHP.BASE_URL}/tab/{$tab->slug}/">{$tab->base->name}</a></li>
	{/foreach}
	<li id="tab-library"><a href="http://go.plymouth.edu/library" target="_blank">Library</a></li>
	<li style="float: right;margin-right:0;">
		<a href="http://go.plymouth.edu/logout"><img src="/psu/images/spacer.gif" class="icon icon-logout"/>Logout</a>
	</li>
	<!--li class="ui-state-default ui-corner-all new-tab" title="Add a tab">
		<a href="{$PHP.BASE_URL}/tab/add" class="ui-icon ui-icon-plus"></a>
	</li-->
</ul>
