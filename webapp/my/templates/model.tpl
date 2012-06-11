{col size=10}
{box}
<form id="add_channel" class="label-left" action="/webapp/my/model" method="post">
	<ul>
	{$channel->name->as_li()}
	{$channel->slug->as_li()}
	{$channel->content_text->as_li()}
	{$channel->content_url->as_li()}
	{$channel->targets->as_li()}
	{$channel->locked->as_li()}
	<li><input type="submit" name="submit" value="Add Channel"></li>  
	</ul>
</form>
{/box}
{/col}
