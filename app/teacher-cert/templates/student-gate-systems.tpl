{assign var=student_name value=$student->person()->formatName('f l')}

{box title="Gate Systems for $student_name (`$student->person()->id`)" size=16}
	<ul>
		{foreach from=$student->gate_systems() item=student_gate_system}
			{if $permissions->has_gatesystem($student_gate_system->gate_system()->level_code)}
				{assign var=url value=$resolver->resolve($student_gate_system)}
			{else}
				{assign var=url value="`$PHP.BASE_URL`/student/`$student->person()->id`/`$student_gate_system->id`}
			{/if}
			<li><a href="{$url|escape}">{$student_gate_system->gate_system()->name}</a></li>
		{/foreach}
	</ul>
{/box}
