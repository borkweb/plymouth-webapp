{math assign=title_size equation="x-3" x=$size}

{box title="<a href='`$PHP.BASE_URL`/admin/constituents'>Constituents</a>" title_size=$title_size size=$size}
	{include file="list.simple.tpl" collection=$include route="admin/constituents"}
	{include file="form.attach-simple.tpl" collection=$exclude what="constituent" object=$object route=$route}
{/box}
