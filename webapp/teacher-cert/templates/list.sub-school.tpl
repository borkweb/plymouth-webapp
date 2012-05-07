<ul>
{foreach from=$collection item=item}
	<li>
		{$item->position()->name} at <a href="{$PHP.BASE_URL}/{$route}/{$item->school()->id}">{$item->school()->name}</a>
	</li>
{foreachelse}
	<li>
		<em>To add this constituent to a school, please head over to the <a href="{$PHP.BASE_URL}/admin/schools">Schools</a> administration area.</em>
	</li>
{/foreach}
</ul>
