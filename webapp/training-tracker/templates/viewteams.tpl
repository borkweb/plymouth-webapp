{box title="Teams" class="teams" size="16"}
	{foreach from=$teams_array item=team}
		{if $team.mentor.mentor_name != $teams_array.unassigned.mentor.mentor_name}
			<h4>{$team.mentor.mentor_name}</h4>
			<ul class="clean">
			{foreach from=$team item=mentee}
				{if $mentee.name != $team.mentor.mentor_name}
					<li>{$mentee.name|indent:3:"&nbsp"}</li>
				{/if}
			{/foreach}
			</ul>
		{/if}
	{/foreach}
{/box}
