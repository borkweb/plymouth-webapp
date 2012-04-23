<!--Had to take the loop for UG and GR out as I believe these things really only pertain to UG and causing dups-->
<div id="ape_wps" class="ape-section {if $myuser->go_states.wps === '0'}ape-section-hidden{/if}">
	<h3>Academic Standing History</h3>	
	<ul class="apedata">
		{foreach name=wps from=$person->student->ug->wps item=item}
			{if $smarty.foreach.wps.first}
			<li>
				<table class="grid sortable">
					<thead>
					<tr>
						<th>Term Code</th>
						<th>Description</th>
						<th>Restricted Hours</th>
						<th>Dean's/President's List</th>
					</tr>
					</thead>
					<tbody>
			{/if}
				<tr>
					<td>{$item.term_code}</td>
					<td>{$item.standing}</td>
					<td>{$item.restricted_hours|number_format:2}</td>
					<td>{$item.deans_list}</td>
				</tr>
			{if $smarty.foreach.wps.last}</tbody></table></li>{/if}

		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>
</div>
