{foreach from=$person->student->levels item=level name=level_loop}
	{assign var=student value=`$person->student->$level`}
	{foreach from=$student->reslife item=item name=reslife_loop}
		{if $smarty.foreach.reslife_loop.first && !$reslife_output}
			<div id="ape_reslife" class="ape-section {if $myuser->go_states.reslife === '0'}ape-section-hidden{/if}">
				<h3>Mealplan and Rooms</h3>	
				<table class="grid">
					<thead>
						<tr>
							<th>Term Code</th>
							<th>Application</th>
							<th>Meal Plan</th>
							<th>Building</th>
							<th>Room</th>
							<th>Room Type</th>
							<th>Begin Date</th>
							<th>End Date</th>
						</tr>
					</thead>
					<tbody>
					{assign var=reslife_output value=true}
		{/if}
		<tr>
			<td>{$item.term_code}</td>
			<td>{$item.app_type}</td>
			<td>{$item.meal_plan}</td>
			<td>{$item.building}</td>
			<td>{$item.room}</td>
			<td>{$item.room_type}</td>
			<td>{$item.begin_date|date_format:"%b %e, %Y"}</td>
			<td>{$item.end_date|date_format:"%b %e, %Y"}</td>
		</tr>
	{/foreach}
{/foreach}

{if $reslife_output}
		</tbody>
	</table>
</div>	
{/if}
