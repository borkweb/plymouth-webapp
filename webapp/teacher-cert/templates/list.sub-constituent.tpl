<ul>
{foreach from=$collection item=item}
	<li>
		<a href="{$PHP.BASE_URL}/{$route}/{$item->constituent()->id}">
			{$item->constituent()->last_name}, {$item->constituent()->first_name} {$item->constituent()->mi}
		</a>
		&mdash; {$item->position()->name}
	</li>
{/foreach}
</ul>
