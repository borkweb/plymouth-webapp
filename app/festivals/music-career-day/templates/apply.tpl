{if not $readonly}
{box title="Apply for Music Tech and Ed Career Day" size=16}
	<p>Please note: required fields are marked in <label class="inline required">bold<em>*</em></label>.
{/box}
<div class="clear"></div>
{/if}

<form method="post" action="_submit" id="mtecd-application" class="{$form->classes()}">
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

{col size=8}
	{box title="Contact Information" size=8 class="alpha omega"}
		<ul>
			{$form->student_email->as_li()}
		</ul>
	{/box}

	{box title=$form->comments->label}
		{$form->comments}
	{/box}
{/col}

<div class="clear"></div>

{if not $readonly}
{box size=16}
	<p class="center" style="font-size: 1.5em;"><input type="submit" value="Submit Application"></p>
{/box}
{/if}

</form>

<div class="clear"></div>
