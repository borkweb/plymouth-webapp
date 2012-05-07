{col size=9}
	{include file="blocks/student.info.tpl"}
{/col}

{col size=7}

{box title="Update Praxis Scores"}
	<p>Scores left blank will be not be updated in Banner.</p>
	{include file=form.tpl edit=1 model=$test_model cancel_url=$cancel_url action=$action what=Score}
{/box}

{/col}
