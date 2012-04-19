{psu_dbug var=$types}
{psu_dbug var=$manufacturers}
{psu_dbug var=$models}
{col size="4"}
	{box title="Refine" subheader="Filter Your Search"}
		<form id="filter-search" action="{$PHP.BASE_URL}">
			<ul>
				<li>
					<label>Machine Type:</label>
					{html_checkboxes class="filter-check" name="type" values=$types output=$types selected=$type seperator='<br />'}
				</li>
				<li>
					<label>Manufacturer:</label>
					{html_checkboxes class="filter-check" name="manufacturer" values=$manufacturers output=$manufacturers selected=$manufacturer seperator='<br />'}
				</li>
				<li>
					<label>Models:</label>
					{html_checkboxes class="filter-check" name="model" values=$models output=$models selected=$model seperator='<br />'}
				</li>
				
				<li>
					<input type="hidden" name="search_term" value="{$search_term}" />
					<input type="submit" value="Search" />
					<button id="filter-reset" type="button">Clear</button>
				</li>
			</ul>
		</form>
	{/box}
{/col}
{col size="12"}
	{foreach from=$by_model key=model item=model_info}
		{include file='model.tpl' model_info=$model_info model=$model box_size="12"}
	{foreachelse}
		{box size="12" title="No Items Found"}
			<h4>We don't seem to have anything like that right now, but feel free to try searching for other stuff :)</h4>
		{/box}
	{/foreach}
{/col}
