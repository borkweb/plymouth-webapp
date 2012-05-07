{if $student_gate_system && ! $student_gate_system->active()}
	{if $student_gate_system->complete_date}
		{message type="success"}
			This gate system was marked as Complete on
			{$student_gate_system->complete_date|date_format}.
		{/message}
	{elseif $student_gate_system->exit_date}
		{message type="warning"}
			This gate system was closed on
			{$student_gate_system->exit_date|date_format}.
		{/message}
	{/if}
{/if}

{if $breadcrumbs && count($breadcrumbs) > 0}
	{box size=16 class=breadcrumbs}{$breadcrumbs}{/box}
{/if}
