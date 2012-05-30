{include file="admin-sidebar.tpl"}

{col size=10}
{box}
<a href="../../admin/"><h4>View All Channels and Tabs</h4></a>
<form id="add_channel" class="label-left" action="/webapp/my/admin/channel/{$channel->id->value()}" method="post">
	<ul>
		{$channel->id->as_hidden()}
		{$channel->name->as_li()}
		{$channel->content_text->as_li()}
		{$channel->description->as_li()}
		{$channel->content_url->as_li()}
		{if $custom_authz}
			<li><strong style="color:red">This channel has custom authorizations</strong></li>
		{/if}
		{$channel->targets->as_li()}
		<li><input type="submit" name="submit" value="Add/Edit Channel"></li>  
		<li><input type="submit" name="newchannel" value="New Channel"></li>
</ul>
</form>
{/box}
{/col}
