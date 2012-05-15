<div class="grid_8">
	<h1><a href="{$PHP.BASE_URL}/">{$webapp_app_title}</a></h1>
</div>
<div class="grid_8 right">
	{if $permissions->can_search()}
		<form action="{$PHP.BASE_URL}/search" method="post">
			<select name="gatesystem_id">
				{foreach from=$gatesystems item=gatesystem}
					<option value="{$gatesystem->id}" {if $search_default_gs == $gatesystem->id}selected="selected"{/if}>{$gatesystem->name}</option>
				{/foreach}
			</select>
			<input type="search" name="q">
		</form>
	{/if}
</div>
