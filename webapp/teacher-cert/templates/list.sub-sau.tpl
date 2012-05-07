<ul>
{foreach from=$collection item=item}
	<li>
		{$item->position()->name} at <a href="{$PHP.BASE_URL}/{$route}/{$item->sau()->id}">{$item->sau()->name}</a>
	</li>
{foreachelse}
	<li>
		<em>To add this constituent to an SAU, please head over to the <a href="{$PHP.BASE_URL}/admin/saus">SAUs</a> administration area.</em>
	</li>
{/foreach}
</ul>
