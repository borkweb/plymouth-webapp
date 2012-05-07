{col size=8}
	{include file="blocks/student.info.tpl"}
	{include file="blocks/student.praxis.tpl"}
	{include file="blocks/student.teaching.tpl"}
	{include file="blocks/student.constituents.tpl"}
{/col}

{col size=8}
	{foreach from=$student_gate_system->gates() item=gate}
		{capture name="edit"}
			<a href="{$PHP.BASE_URL}/student-gate/{if $gate->student_gate_id}{$gate->student_gate_id}{else}add/{$gate->student_gate_system_id}/{$gate->gate_id}{/if}" class="btn stu-gate-view">
				{if $gate->is_complete()}
				View
				{else}
				Hide
				{/if}
			</a>
			<a href="{$PHP.BASE_URL}/student-gate/{if $gate->student_gate_id}{$gate->student_gate_id}{else}add/{$gate->student_gate_system_id}/{$gate->gate_id}{/if}" class="btn stu-gate-edit">Edit</a>
		{/capture}
		{capture assign=gate_title}{$gate->name} ({if $gate->is_complete()}Complete{else}Incomplete{/if}){/capture}
		{capture assign=gate_class}{if $gate->is_complete()}collapse{else}{/if}{/capture}
		{box title=$gate_title id="stu-gate-`$gate->id`" title_size=3 secondary_title=$smarty.capture.edit class="stu-gate `$gate_class`"}
			<div class="stu-gate-wrapper">
			<ul data-student-gate-id="{$gate->id}" data-gate-id="{$gate->gate_id}">
				{foreach from=$gate->checklist_items() item=checklist_item}
					<li data-checklist-id="{$checklist_item->checklist_item_id}" class="checklist-item {if ! $checklist_item->complete()}in{/if}complete">
						<strong>{$checklist_item->name()}:</strong>

						{if $checklist_item->is_default()}
							{assign var=ckldefault value="ckl-default"}
						{else}
							{assign var=ckldefault value=""}
						{/if}

						<span class="answer {$ckldefault}" data-student-answer-id="{$checklist_item->id}" data-answer-id="{$checklist_item->answer_id}">
							{if $checklist_item->is_date_field()}
								{if $checklist_item->has_answer()}
									{$checklist_item|date_format}
								{else}
									N/A
								{/if}
							{elseif $checklist_item->is_text_field()}
								{if $checklist_item->has_answer()}
									{$checklist_item}
								{else}
									N/A
								{/if}
							{else}
								{$checklist_item|default:'N/A'}
							{/if}
						</span>
					</li>
				{/foreach}
			</ul>
			</div>
		{/box}
	{/foreach}

	{box title="Manage Gate System" class="stu-manage"}
		<div class="well">
			{if ! $student_gate_system->active()}
				{assign var=formclass value=collapse}
				<div class="show-siblings center">
					{if $student_gate_system->complete_date}
						This gate system was marked as <strong>Complete</strong> on
						<strong>{$student_gate_system->complete_date|date_format}</strong>.
					{elseif $student_gate_system->exit_date}
						This gate system was <strong>closed</strong> on
						<strong>{$student_gate_system->exit_date|date_format}</strong>.
					{/if}

					Click for more options.
				</div>
			{/if}
			<form method="post" class="{$formclass}">
				{if $student_gate_system->complete_date}
					<button class="btn danger" name="action" value="incomplete" type="submit">Mark Incomplete</button>
				{else}
					<button class="btn success" name="action" value="complete" type="submit">Mark Complete</button>
				{/if}

				{if $student_gate_system->exit_date}
					<button class="btn right" name="action" value="reopen" type="submit">Reopen (Revert Early Exit)</button>
				{else}
					<button class="btn danger right stu-delete-gs" name="action" value="exit" type="submit">Disable (Early Exit)</button>
				{/if}
			</form>
		</div>
	{/box}
{/col}

{literal}
<script type="text/x-jquery-tmpl">
	<ul>
		<li>Reading: {{=reading}}</li>
		<li>Writing: {{=writing}}</li>
		<li>Math: {{=math}}</li>
		<li>Composite: {{=composite}}</li>
	</ul>
</script>

<script id="" type="text/x-jquery-tmpl">
	<form class="label-left">
		<ul>
			<li>
				<label>Reading:</label>
				<input type="number" value="{{=reading}}">
			</li>
			<li>
				<label>Writing:</label>
				<input type="number" value="{{=reading}}">
			</li>
			<li>
				<label>Math:</label>
				<input type="number" value="{{=reading}}">
			</li>
		</ul>
	</form>
</script>

<script type="text/x-jquery-tmpl">
	<form class="label-left">
		<ul>
			<li>
				<label>School:</label>
				{{schools}}
			</li>
		</ul>
	</form>
</script>

<script id="stu-gate-ro" type="text/x-jquery-tmpl">
	<ul>
		{{#each checklist}}
			<li>{{=name}}: {{=answer}}</li>
		{{/each}}
	</ul>

	<button class="stu-gate-edit">Edit Gate</button>
</script>

<script id="stu-gate" type="text/x-jquery-tmpl">
	<form>
	<ul>
		{{#each checklist}}
			<li>
				<label>{{=name}}</label>
				<select name="item[{{=id}}]">
					<option>Foo</option>
				</select>
			</li>
		{{/each}}
	</ul>

	<input type="submit" name="action" value="Save Changes" class="stu-gate-save">
	<input type="submit" name="action" value="Cancel" class="stu-gate-cancel">
	</form>
</script>
{/literal}

<script>
$(function(){
	var d = teacher_cert.data;

	d.gate_system = {$gate_system|@json_encode};
	d.student_gate_system = {$student_gate_system|@json_encode};
	d.student = {$student|@json_encode};

	teacher_cert.student_gate.ready();
});
</script>
