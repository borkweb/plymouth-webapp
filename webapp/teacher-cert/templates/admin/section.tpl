{col size=8}
	{capture assign=title}
		<a href="{$PHP.BASE_URL}/admin/{$route}/">{$object->get_static('_name')}</a> &raquo; {if $title}{$title}{else}{$object->name}{/if}
	{/capture}
	{capture assign=secondary}
		{if ! $edit}
		<a href="{$PHP.BASE_URL}/admin/{$route}/{$object->id}/edit" class="button {$route_single}-edit">Edit</a>
		{/if}
	{/capture}
	{box size="8" title=$title id="`$route_single`-`$object->id`" title_size="7" secondary_title=$secondary}
		{include file="form.tpl" action="Edit" what=$object->get_static('_name') object=$object edit=$edit model=$model}
	{/box}
{/col}

{col size=8}
	{if $block.saus}
		{include file="blocks/saus.tpl" size=8 route="admin/`$route`" object=$object include=$include_saus exclude=$exclude_saus}
	{/if}
	{if $block.sub_saus}
		{include file="blocks/sub_saus.tpl" size=8 route="admin/`$route`" object=$object include=$include_saus exclude=$exclude_saus}
	{/if}
	{if $block.schools}
		{include file="blocks/schools.tpl" size=8 route="admin/`$route`" object=$object include=$include_schools exclude=$exclude_schools}
	{/if}
	{if $block.sub_schools}
		{include file="blocks/sub_schools.tpl" size=8 route="admin/`$route`" object=$object include=$include_schools exclude=$exclude_schools}
	{/if}
	{if $block.constituents}
		{include file="blocks/constituents.tpl" size=8 route="admin/`$route`" object=$object include=$include_constituents exclude=$exclude_constituents}
	{/if}
	{if $block.sub_constituents}
		{include file="blocks/sub_constituents.tpl" size=8 route="admin/`$route`" object=$object include=$include_constituents exclude=$exclude_constituents}
	{/if}
	{if $block.sub_students}
		{include file="blocks/sub_students.tpl" size=8 route="admin/`$route`" object=$object include=$include_students exclude=$exclude_students}
	{/if}
{/col}
