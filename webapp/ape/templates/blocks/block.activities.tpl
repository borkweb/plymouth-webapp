<div id="ape_adv_activities" class="ape-section {if $myuser->go_states.ape_adv_activities === '0'}ape-section-hidden{/if}">
	<h3>Activities</h3>
	<ul class="apedata">
		{foreach name=activities from=$person->alumni->activities item=item}
			{if $smarty.foreach.activities.first}
			<li>
				<table class="grid sortable">
					<thead>
					<tr>
						<th>Latest Year</th>
						<th>Total Years</th>
						<th>Activity</th>
						<th>Type</th>
						<th>Category</th>
					</tr>
					</thead>
					<tbody>
			{/if}
				<tr>
					<td>{$item.latest_year}</td>
					<td>{$item.total_years}</td>
					<td>{$item.actc_desc}</td>
					<td>{$item.actp_desc}</td>
					<td>{$item.accg_desc}</td>
				</tr>
			{if $smarty.foreach.activities.last}</tbody></table></li>{/if}

		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>
</div>
