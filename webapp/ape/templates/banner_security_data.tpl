<ul>
	<li class="filter">
		<span>Filter: </span><input type="text"/>
	</li>
	{foreach from=$items item=item}
	<li>
		<a href="{$PHP.BASE_URL}/banner_security_data.html?key={$item.name}&key_type={$smarty.get.load}&item_type={$item.type}" {if $item.date}title="Added: {$item.date|date_format:"%b %e, %Y %I:%M %p"}"{/if} class="drill">{if $item.type == "locked"}<font color="aaa">{/if}{$item.name}{if $item.type} <small>({$item.type})</small>{/if}{if $item.type == "locked"}</font>{/if}</a>
		<ul style="display:none;">
			{if $smarty.get.load == 'class' ||  $smarty.get.load == 'role' ||  $smarty.get.load == 'object' ||  $smarty.get.load == 'form'}
				<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=user&key={$item.name}&key_type={$smarty.get.load}&item_type={$item.type}" class="drill">Users</a></li>
			{/if}
			{if $smarty.get.load == 'user' ||  $smarty.get.load == 'object'}
				<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=role&key={$item.name}&key_type={$smarty.get.load}&item_type={$item.type}" class="drill">Roles</a></li>
			{/if}
			{if $smarty.get.load == 'form' ||  $smarty.get.load == 'user'}
				<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=class&key={$item.name}&key_type={$smarty.get.load}&item_type={$item.type}" class="drill">Classes</a></li>
			{/if}
			{if $smarty.get.load == 'class' || $smarty.get.load == 'user'}
				<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=form&key={$item.name}&key_type={$smarty.get.load}&item_type={$item.type}" class="drill">INB Forms</a></li>
			{/if}
			{if $smarty.get.load == 'user' ||  $smarty.get.load == 'role'}
				<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=object&key={$item.name}&key_type={$smarty.get.load}&item_type={$item.type}" class="drill">Tables, Views, etc.</a></li>
			{/if}
		</ul>
	</li>
	{foreachelse}
		<li class="no-data">No data found</li>
	{/foreach}
</ul>
