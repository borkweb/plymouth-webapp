{if $title}
	<h2>{$rss->title}</h2>
{/if}

{if $desc}
	<em>{$rss->description}</em>
{/if}

<div id="rss-{$id}">
<ul class="rss" id="rss-{$id}{$smarty.now}">
{if is_object($rss->image) && $rss->image->url() && ! $mycomics}
	{assign var=imagelink value=$rss->image->deatomized_link}
	<li class="image">
		{if $imagelink}<a href="{$rss->image->deatomized_link}" target="_blank">{/if}
		<img src="{$rss->image->url()}" alt="{$rss->image->title()|escape}"/>
		{if $imagelink}</a>{/if}
	</li>
{/if}

{foreach from=$rss item=item name=rss_items}
	{if $smarty.foreach.rss_items.index < $rss->max}
	{capture name="state"}{if $rss->open || ($rss->expand && $smarty.foreach.rss_items.index < $rss->expand)}expanded{else}contracted{/if}{/capture}
	<li class="{$smarty.capture.state}">
		<h3>
			<a href="#" class="toggle"><img src="/psu/images/spacer.gif" style="vertical-align:middle;"/></a>
			<a href="{$item->deatomized_link}" target="_blank">{$item->title}</a>
		</h3>
		<div class="rss-body">
			{$item->out_text}
			<div class="clear"></div>
			<a href="{$item->deatomized_link}" target="_blank">{$link_text}</a>
		</div>
	</li>
	{/if}
{foreachelse}
	<li>There are no articles in this News Feed.</li>
{/foreach}
</ul>
</div>
<div class="clear"></div>

<a href="{$rss->url()}" target="_blank">Read more</a>
