
<nav id="webapp-nav">
  <div class="container">
  </div>
</nav>
{include file="status.tpl"}
{box size="4" title="Available Equipment"}
<form class="label-left" name="equipment" method="POST" action="{$PHP.BASE_URL}/reserve/equipment/add">
	{foreach from=$categories key=k item=device}
			<a href="{$PHP.BASE_URL}/reserve/equipment?equipment_id={$k}">{$device}</a><br>
	{/foreach}

{/box}

{box size="8" title="Current Item"}
	<p>{$description}</p>
	<input type="hidden" name="equipment_id" value="{$equipment_id}" >
	<input type="Submit" name="Submit" value="Add to loan">
{/box}
</form>
<form name="equipment-full" method="POST" action="{$PHP.BASE_URL}/reserve/confirm">
{box size="4" title="Shopping Cart"}
	{foreach from=$equipment item=item key=k}
		{$categories[$item]}-<em><a href="{$PHP.BASE_URL}/reserve/equipment/{$k}/remove">Remove</a></em> <br>
	{/foreach}
	<input type="Submit" name="Final Submit" value="Done">
{/box}
</form>
