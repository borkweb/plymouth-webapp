{box size="16" title="Status"}
{if $step == NULL}
	<a href="{$PHP.BASE_URL}/reserve/"><button >Event Information</button></a>
	<button class="btn danger">Equipment</button>
	<button class="btn danger"> Confirmation</button></a>

{elseif $step == "1"}
	<a href="{$PHP.BASE_URL}/reserve/"><button class="btn success">Event Information</button></a>
	<a href="{$PHP.BASE_URL}/reserve/equipment"><button>Equipment</button></a>
	<button class="btn danger"> Confirmation</button></a>

{elseif $step == "2"}
<a href="{$PHP.BASE_URL}/reserve/"><button class="btn success">Event Information</button></a>
<a href="{$PHP.BASE_URL}/reserve/equipment"><button class="btn success">Equipment</button></a>
<a href="{$PHP.BASE_URL}/reserve/confirm"><button> Confirmation</button></a>

{/if}
{/box}
