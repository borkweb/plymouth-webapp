{col size=8}
	{include file="blocks/student.info.tpl"}
{/col}

{col size=8}

{if $student_school}
	{assign var=boxtitle value="Edit School in Gate System"}
	{assign var=formaction value="Save"}
{else}
	{assign var=boxtitle value="Add School to Gate System"}
	{assign var=formaction value="Add"}
{/if}

{box title=$boxtitle class="stu-gate"}
	{include file=form.tpl edit=1 action=$formaction what=School model=$school_model cancel_url=$cancel_url}
{/box}

{if $student_school && count($student_school->parent()->cooperating_teachers())}
	{box title="Cooperating Teachers"}
		{if count($student_school->cooperating_teachers())}
		<p>The following cooperating teachers are already part of this student's gate system:</p>
		<table class="grid" style="width:100%">
			<thead>
				<tr>
					<th>Name</th>
					<th>Vouchers</th>
					<th>Actions</th>
				</tr>
			</thead>
			{foreach from=$student_school->cooperating_teachers() item=teacher}
				<tr>
					<td>
						{$teacher->constituent()->last_name}, {$teacher->constituent()->first_name}
						{if $teacher->association_attribute}
							<br>{$teacher->association_attribute}
						{/if}
					</td>
					<td>
						{$teacher->voucher|default:0} {if $teacher->voucher}({$teacher->voucher_date|date_format}){/if}
					</td>
					<td>
						<form method="post">
							<input type="hidden" name="target" value="teacher">
							<input type="hidden" name="id" value="{$teacher->id}">
							<select name="action">
								{if $teacher->voucher}
									<option>Remove Voucher</option>
								{else}
									<option>Add Voucher</option>
								{/if}
								<option>Remove Teacher</option>
							</select>
							<input type="submit" value="Go">
						</form>
					</td>
				</tr>
			{/foreach}
		</table>
		{else}
			<p>No teachers have been attached to this student's school.</p>
		{/if}

		<h3>Add Teacher</h3>
		<form method="post">
			<input type="hidden" name="target" value="teacher">
			<input type="hidden" name="student_school_id" value="{$student_school->id}">
			<ul>
				{$teacher_model->constituent_school_id->as_li()}
				{$teacher_model->association_attribute->as_li()}
				<li class="well">
					<button name="action" value="add-teacher" type="submit">Add</button>
				</li>
			</ul>
		</form>
	{/box}
{elseif $student_school}
	<br class="clear">
	{message type="warning"}
		You cannot set cooperating teachers for this student, because no
		cooperating teachers are attached to {$student_school->parent()->name}.
	{/message}
{/if}

{/col}
