<ul>
{foreach from=$collection item=item}
	<li>
		<a href="{$PHP.BASE_URL}/{$route}/{$item->id}">{$item->name}</a>
	</li>
{/foreach}
</ul>
