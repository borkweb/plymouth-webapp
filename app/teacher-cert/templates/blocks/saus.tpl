{math assign=title_size equation="x-3" x=$size}

{capture assign=secondary}
	{if ! $edit}
	<a href="{$PHP.BASE_URL}/{$route}/{$object->id}/add-sau" class="button sau-add">Assign SAU</a>
	{/if}
{/capture}
{box title="<a href='`$PHP.BASE_URL`/admin/saus'>SAUs</a>" secondary_title=$secondary title_size=$title_size size=$size}
	{include file="list.simple.tpl" collection=$include route="admin/saus"}
	{include file="form.attach-simple.tpl" collection=$exclude what="sau" object=$object route=$route}
{/box}
