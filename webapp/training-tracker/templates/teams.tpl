{PSU_JS src="/webapp/training-tracker/js/teams.js"}
{PSU_CSS src="/webapp/training-tracker/css/teams.css"}

<script>
	var teams = {$teams};
</script>

{box title="Team builder" class="team_builder" size="16"}
	<div class="grid_8 grid-internal table table-bordered light">
		<div class="dropdown-box">
			<select data-placeholder="Choose a team leader..." class="chzn-select list1" tabindex="2">
				<option value=""></option>
				<option value="unassigned">Unassigned</option> 
				{foreach from=$mentors item=mentor}
					<option value="{$mentor->wpid}">{$mentor->name}</option> 
				{/foreach}
			</select>
		</div>
		<ul id="team1" class="team connectedSortable ui-sortable table table-bordered">
		</ul>
	</div>

	<div class="grid_8 grid-internal table table-bordered light">
		<div class="dropdown-box">
			<select data-placeholder="Choose a team leader..." class="chzn-select list2" tabindex="2">
				<option value=""></option>
				<option value="unassigned">Unassigned</option> 
				{foreach from=$mentors item=mentor}
					<option value="{$mentor->wpid}">{$mentor->name}</option> 
				{/foreach}
			</select>
		</div>
		<ul id="team2" class="team connectedSortable ui-sortable table table-bordered">
		</ul>
	</div>
{/box}

