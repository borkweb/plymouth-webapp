{include file="admin-sidebar.tpl"}

{col size=10}
{box}
<a href="../../admin/"><h4>View All Channels and Tabs</h4></a>
<form id="add_tab" class="label-left" action="/webapp/my/admin/tab/" method="post">
	<ul>
		{$tabform->id->as_hidden()}
		{$tabform->name->as_li()}
		{$tabform->slug->as_hidden()} 
		{$tabform->lock_state->as_li()}
		{$tabform->targets->as_li()}
		<li><input type="submit" name="submit" value="Add/Edit Tab"></li>  
		<li><input type="submit" name="newtab" value="New Tab"></li>
</ul>
</form>
{/box}
{/col}

