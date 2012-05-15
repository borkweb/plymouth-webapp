{capture name="edit"}
	<a href="{$PHP.BASE_URL}/gate-system/{$student_gate_system->gate_system()->slug}/{$student_gate_system->id}/praxis" class="btn stu-gate-edit">Update</a>
{/capture}

{box title="Praxis Scores" title_size=6 secondary_title=$smarty.capture.edit}
	<div class="praxis-scores">
		<ul>
			{assign var=s value=$student->person()->student}

			<li>
				<strong>Reading:</strong>
				{assign var=test value=$s->tests->max('PRXR')}
				<span class="answer">{if $test}{$test->score}{else}N/A{/if}</span>
			</li>

			<li>
				<strong>Writing:</strong>
				{assign var=test value=$s->tests->max('PRXW')}
				<span class="answer">{if $test}{$test->score}{else}N/A{/if}</span>
			</li>

			<li>
				<strong>Math:</strong>
				{assign var=test value=$s->tests->max('PRXM')}
				<span class="answer">{if $test}{$test->score}{else}N/A{/if}</span>
			</li>

			<li>
				<strong>Composite:</strong>
				{assign var=test value=$s->tests->max_praxis()->composite()}
				<span class="answer">{if $test}{$test}{else}N/A{/if}</span>
			</li>
		</ul>
	</div>
{/box}
