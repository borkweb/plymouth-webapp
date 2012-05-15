{math assign=title_size equation="x-3" x=$size}

{box title="<a href='`$PHP.BASE_URL`/'>Students</a>" title_size=$title_size size=$size}
	{include file="list.simple.tpl" collection=$include route="admin/students"}
{/box}
