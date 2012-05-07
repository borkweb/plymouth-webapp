<ul{if $params.class} class="{$params.class}"{/if}{if $params.id} id="{$params.id}"{/if}>
{foreach from=$params.links item=link}
	<li class="{if $link.icon}link-with-icon{/if}">
		{if $link.url}
			<a href="{$link.url}"{if $link.tooltip} title="{$link.tooltip|escape}"{/if}{if $link.class} class="{$link.class}"{/if}>{if $link.icon}{icon id=$link.icon} {/if}{$link.title|escape}</a>
		{else}
			{$link.title|escape}
		{/if}
		{if $link.children}
			{nav links=$link.children}
		{/if}
	</li>
{/foreach}
</ul>
