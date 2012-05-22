{include file="sidebar.tpl"}
{col size=12}
	{box title="New Call"}
		<div class="grid_6 alpha">
			<h3>Search</h3>
			{include file="search-form.tpl" button_class="btn-primary" search_body=true}
		</div>
		<div class="grid_4 omega">
			<h3>Quick Create</h3>
			<ul id="generic-call-list">
				<li><a href="{$PHP.BASE_URL}/user/generic/" class="btn info">Generic Caller</a></li>
				<li><a href="{$PHP.BASE_URL}/user/kiosk/" class="btn">Kiosk Call</a></li>
				<li><a href="{$PHP.BASE_URL}/user/clusteradm/" class="btn">Cluster Call</a></li>
			</ul>
		</div>
		<div class="clear"></div>
	{/box}

	{if $search.search_string}
		{box title="Search Results for \"`$search.search_string`\""}
			{if $search.results}
				{include file="search-results.tpl"}
			{else}
				Your search returned no results.
			{/if}
		{/box}
	{/if}
{/col}
