<!--Had to take the loop for UG and GR out as I believe these things really only pertain to UG and causing dups-->
<div id="ape_holds" class="ape-section {if $myuser->go_states.holds === '0'}ape-section-hidden{/if}">
	<h3>Student Holds</h3>	
	<ul class="apedata">
		{foreach name=holds from=$person->student->ug->holds item=item}
			{if $smarty.foreach.holds.first}
			<li>
				<table class="grid sortable">
					<thead>
					<tr>
						<th>From Date</th>
						<th>To Date</th>
						<th>Description</th>
						<th>Reason</th>
						<th>Amount</th>
					</tr>
					</thead>
					<tbody>
			{/if}
				<tr>
					<td>{$item.from_date|date_format:"%b %e, %Y"}</td>
					<td>{$item.to_date|date_format:"%b %e, %Y"}</td>
					<td>{$item.description}</td>
					<td>{$item.reason}</td>
					<td>{$item.amount|money_format}</td>
				</tr>
			{if $smarty.foreach.holds.last}</tbody></table></li>{/if}

		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>
</div>
