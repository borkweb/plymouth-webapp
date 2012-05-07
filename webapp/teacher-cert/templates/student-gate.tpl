{col size=8}

{include file="blocks/student.info.tpl"}

{/col}
{col size=8}
{box title=$student_gate->name class="stu-gate"}
	<form method="post" action="">
		<ul>
			{foreach from=$student_gate->checklist_items() item=item}
				<li class="checklist-item">
					<label>{$item->name()}:</label>
					{* global checklist item id, not student answer id *}
					{if $item->is_date_field()}
						<input type="text" class="ckl-date" name="checklist_item[{$item->checklist_item_id()}]" value="{$item|escape:'html'}">
					{elseif $item->is_text_field()}
						<input type="text" name="checklist_item[{$item->checklist_item_id()}]" value="{$item|escape:'html'}">
					{else}
						<select name="checklist_item[{$item->checklist_item_id()}]">
							{foreach from=$item->answers() item=answer}
								<option value="{$answer->id}" {if $answer == $item->answer()}selected{/if}>{$answer->answer}</option>
							{/foreach}
						</select>
					{/if}
				</li>
			{/foreach}
			<li class="well">
				<a href="{$PHP.BASE_URL}/gate-system/{$student_gate_system->gate_system()->slug}/{$student_gate_system->id}" class="btn">Cancel</a>
				<input type="submit" name="save" class="btn primary right" value="Save Changes">
			</li>
		</ul>
	</form>
{/box}

{/col}
