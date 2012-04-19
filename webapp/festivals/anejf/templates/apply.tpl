{if not $readonly}
{box title="Apply for the All New England Jazz Festival" size=16}
	<p>Please note: required fields are marked in <label class="inline required">bold<em>*</em></label>.
{/box}
<div class="clear"></div>
{/if}

<form method="post" action="_submit" id="anejf-application" class="{$form->classes()}">
{box title="Student Information" size=8}
	<ul>
		{$form->first_name->as_li()}
		{$form->last_name->as_li()}
		{$form->instrument->as_li()}
		{$form->address1->as_li()}
		{$form->address2->as_li()}
		{$form->city->as_li()}
		{$form->state->as_li()}
		{$form->zip->as_li()}
		{$form->high_school->as_li()}
		{$form->high_school_grade->as_li()}
		{$form->high_school_enrollment->as_li()}
		{$form->band_size->as_li()}
	</ul>
{/box}

<div class="grid_8">
{box title="Director's Information" size=8 class="alpha omega"}
	<ul>
		{$form->director_name->as_li()}
		{$form->director_email->as_li()}
		<li>
			{$form->director_student_rating->label()}
			<small>Compared to other students in the state.</small><br>
			{$form->director_student_rating}
		</li>
		{$form->student_past_participate->as_li()}
		<li>
			In our school band, this student plays {$form->student_chair} chair<br>
			in a section of {$form->student_section_players} players.
		</li>
	</ul>
{/box}

{box title="District Honor Band (High School)" size=8 class="alpha omega"}
	<ul>
		{$form->honorband_years->as_li()}
		{$form->honorband_recent_chair->as_li()}
	</ul>
{/box}

{box title="Solo at State Festival" size=8 class="alpha omega"}
	<ul>
		{$form->solosf_music_level->as_li()}
		{$form->solosf_rating->as_li()}
	</ul>
{/box}
</div>

{box title=$form->comments->label size=16}
	{$form->comments}
{/box}

<div class="clear"></div>

{if not $readonly}
{box size=16}
	<p class="center" style="font-size: 1.5em;"><input type="submit" value="Submit Application"></p>
{/box}
{/if}

</form>

<div class="clear"></div>
