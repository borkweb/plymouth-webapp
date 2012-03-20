
<nav id="webapp-nav">
  <div class="container">
  </div>
</nav>
{include file="status.tpl"}
{box size="4" title="Available Equipment"}
<form class="label-left" name="equipment" method="POST" action="{$PHP.BASE_URL}/reserve/equipment">
	{foreach from=$categories key=k item=device}
			<a href="{$PHP.BASE_URL}/reserve/equipment/{$k}">{$device}</a><br>
	{/foreach}

{/box}

{box size="8" title="Current Item"}
	{$equipment_id}
	<input type="hidden" name="equipment_id" value="{$equipment_id}" >
	<input type="Submit" name="Submit" value="Add to loan">
{/box}

{box size="4" title="Shopping Cart"}
	{foreach from=$equipment item=item}
		{$categories[$item]}<br>
	{/foreach}
{/box}
</form>
