{include file='sidebar.tpl'}
{col size="12" id="single_channel" class="channel-column"}
	{capture name=channel_id}channel-{$channel->id}{/capture}
	{capture name=channel_slug}channel-{$channel->slug}{/capture}
	{capture name=channel_class}channel {$smarty.capture.channel_slug}{/capture}
	{box title=$channel->name id=$smarty.capture.channel_id class=$smarty.capture.channel_class no_grid=true no_title_size=true}
		{if $channel->content_url}
			{if $channel->meta()->gadget}
			<script type="text/javascript" src="https://www.gmodules.com/ig/ifr?url={if $channel->meta()->gadget_url}{$channel->meta()->gadget_url}{else}{$channel->content_url}{/if}&amp;output=js"></script>
			{else}
			<div class="channel-container">
				<a href="{$channel->content_url}{if $channel->meta()->gadget}?gadget={$channel->meta()->gadget}{/if}" class="remote-channel">
					<img src="/images/1x1trns.gif" class="throbber" alt="Loading"/>
				</a>
			</div>
			{/if}
		{else}
			{$channel->content_text}
		{/if}
	{/box}
{/col}
