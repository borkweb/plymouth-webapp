{math assign=title_size equation="x-3" x=$size}

{box title="<a href='`$PHP.BASE_URL`/admin/schools'>Schools</a>" title_size=$title_size size=$size}
	{include file="list.simple.tpl" collection=$include route="admin/schools"}
	{include file="form.attach-simple.tpl" collection=$exclude what="school" object=$object route=$route}
{/box}
