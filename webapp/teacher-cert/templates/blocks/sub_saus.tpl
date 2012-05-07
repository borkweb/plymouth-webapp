{math assign=title_size equation="x-3" x=$size}

{capture assign=secondary}
{/capture}
{box title="<a href='`$PHP.BASE_URL`/admin/saus'>SAUs</a>" secondary_title=$secondary title_size=$title_size size=$size}
	{include file="list.sub-sau.tpl" collection=$include route="admin/saus"}
{/box}
