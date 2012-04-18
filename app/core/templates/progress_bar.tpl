{strip}
<ul {if $progress_id}id="{$progress_id}"{/if} class="psuprogress">
	{foreach from=$progress_bars item=bar key=bar_id}
		
		{* format the bar text *}
		{capture assign=bar_text}
			{if $bar.selected}
				<span class="indicator">
				{if $selected_indicator}
					{$selected_indicator}
				{else}
					&raquo;
				{/if}
				</span>
			{/if}
			{$bar.text|default:Progress}
		{/capture}
		
		{* if there is a url specified, open an a tag.  Otherwise, open a span *}
		{capture assign=pre_tag}
			{if $bar.url}
				<a href="{$bar.url}" class="bar-inner {$bar.class}">
			{else}
				<span class="bar-inner {$bar.class}">
			{/if}
		{/capture}

		{* if there is a url specified, close an a tag.  Otherwise, close a span *}
		{capture assign=post_tag}
			{if $bar.url}
				</a>
			{else}
				</span>
			{/if}
		{/capture}
		
	<li id="bar-{$bar_id}" class="{if $bar.selected}selected{/if}">
		<span class="progress" style="width:{$bar.percent|default:0}%;">
			{$pre_tag}
				{$bar_text} <span>({$bar.percent|default:0}%)</span>
			{$post_tag}
		</span>
		<span>
			{$pre_tag}
				{$bar_text} <span>({$bar.percent|default:0}%)</span>
			{$post_tag}
		</span>
	</li>
	{/foreach}
</ul>
{/strip}
