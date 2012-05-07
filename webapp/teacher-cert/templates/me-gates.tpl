{box title="My Gates" size=16}
	<ul>
		{foreach from=$student->gate_systems() item=student_gate_system}
			<li><a href="{$PHP.BASE_URL}/me/{$student_gate_system->id}">{$student_gate_system->gate_system()->name}</a></li>
		{/foreach}
	</ul>
{/box}
