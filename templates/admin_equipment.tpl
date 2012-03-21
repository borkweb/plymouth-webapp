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
					<label id="price-label" for="price">Price:</label>
					<input name="price" type="text" id="price" size="10" readonly="readonly" />
					<div id="price-slider"></div>
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
