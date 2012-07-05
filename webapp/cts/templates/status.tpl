{box size="16" title="Status"}
{if $step == NULL}
	<a class="btn" href="{$PHP.BASE_URL}/reserve/">Event Information</a>
	<button class="btn danger">Equipment</button>
	<button class="btn danger"> Confirmation</button>

{elseif $step == "1"}
	<a class="btn success" href="{$PHP.BASE_URL}/reserve/">Event Information</a>
	<a class="btn" href="{$PHP.BASE_URL}/reserve/equipment">Equipment</a>
	<button class="btn danger"> Confirmation</button>

{elseif $step == "2"}
<a class="btn success" href="{$PHP.BASE_URL}/reserve/">Event Information</a>
<a class="btn success" href="{$PHP.BASE_URL}/reserve/equipment">Equipment</a>
<a class="btn" href="{$PHP.BASE_URL}/reserve/confirm">Confirmation</a>

{/if}
{/box}
