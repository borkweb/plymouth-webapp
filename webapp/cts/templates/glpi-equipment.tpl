{col size="4"}
{box size=4 title="Quick Tools"}
<ul class="clean">
	<li><a href="{$PHP.BASE_URL}/admin/equipment/by-week">View Reservations by week</a></li>
	<form action="{$PHP.BASE_URL}/admin/equipment/add-id">
		<li><label>Add Index to Session: </label><input name="reservation_idx" type="text"><input type="Submit" value="Add"></li>
	</form>
</ul>
{/box}
	{box title="Refine" subheader="Filter Your Search"}
		<form id="filter-search" action="{$PHP.BASE_URL}/admin/equipment{if $reservation_idx}/{$reservation_idx}{/if}/filter/">
			<ul>
				<li>
					<label>Machine Type:</label>
					{html_checkboxes class="filter-check" name="type" values=$types output=$types selected=$type seperator='<br />'}
				</li>
				<li>
					<label>Models:</label>
					{html_checkboxes class="filter-check" name="model" values=$models output=$models selected=$model seperator='<br />'}
				</li>
			
				<li>
					<input type="hidden" name="search_term" value="{$search_term}" />
					<input type="submit" value="Search" />
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
			<h4>Nothing found with that criteria.</h4>
		{/box}
	{/foreach}
{/col}
