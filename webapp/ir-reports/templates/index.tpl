{box size='16' title='Links Reporting In'}
	<ul class='clean'>
		{foreach from=$files item=file}
			<li><a href='{$base_url}/reports/{$file.file_name}'>{$file.display_name}</a></li>
		{/foreach}
	</ul>
{/box}
