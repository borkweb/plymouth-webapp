{include file='sidebar.tpl'}
{assign var='max_width' value=12}
{if $current_tab->num_cols}
	{assign var='num_cols' value=`$current_tab->num_cols`}
{else}
	{assign var='num_cols' value=1}
{/if}
{assign var='col_size' value=`$max_width/$num_cols`}
{section name=column start=1 loop=3 step=1}
	{assign var=column value=`$smarty.section.column.index`}
	{col size=`$col_size` id="column_$column" class="column"}
		{foreach from=$current_tab->channels() item=channel name=channel_loop}
			{if $channel->col_num == $column}
				{capture name="icons"}
				<ul class="options" style="margin-bottom:0;">{* margin-bottom:0 is used to fix an IE7 bug *}
					{if ($channel->lock_state & 2) != 2}{* lock-remove *}
					<li class="ui-state-default ui-corner-all icon-delete" title="Remove channel from tab">
						<span class="ui-icon ui-icon-close"></span>
					</li>
					{/if}
					<li class="clear"></li>
				</ul>
				{/capture}
				{capture name=channel_id}channel-{$channel->id}{/capture}
				{capture name=channel_slug}channel-{$channel->slug}{/capture}
				{capture name=channel_class}channel {$smarty.capture.channel_slug}{if ($channel->lock_state & 1) == 1} channel-lock-move{/if}{/capture}
				{box title=$channel->name secondary_title=$smarty.capture.icons id=$smarty.capture.channel_id class=$smarty.capture.channel_class no_grid=true no_title_size=true}
					{if $channel->content_url}
						{if $channel->meta()->gadget}
						<div class="gadget-container">
						<script type="text/javascript" src="https://www.gmodules.com/ig/ifr?url={if $channel->meta()->gadget_url}{$channel->meta()->gadget_url}{else}{$channel->content_url}{/if}&output=js"></script>
						</div>
						{else}
						<div class="channel-container">
							<a href="{$channel->content_url|replace:'[base]':$PHP.HOST_URL|replace:'&':'&amp;'}{if $channel->meta()->gadget}?gadget={$channel->meta()->gadget}{/if}" class="remote-channel">
								<img src="/images/1x1trns.gif" class="throbber" alt="Loading"/>
							</a>
						</div>
						{/if}
					{else}
						{$channel->content_text}
					{/if}
				{/box}
			{/if}
		{/foreach}
	{/col}
{/section}
