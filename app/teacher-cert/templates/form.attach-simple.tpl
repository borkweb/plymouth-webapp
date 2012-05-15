<form id="add-{$what}" action="{$PHP.BASE_URL}/{$route}/{$object->id}/add-{$what}" method="POST">
	{if $sau_id}<input type="hidden" name="sau_id" value="{$sau_id}"/>{/if}
	{if $school_id}<input type="hidden" name="school_id" value="{$school_id}"/>{/if}
	{if $constituent_id}<input type="hidden" name="constituent_id" value="{$constituent_id}"/>{/if}
	<ul>
		<li>
			<select name="{$what}_id">
				<option></option>
				{foreach from=$collection item=item}
					<option value="{$item->id}">{$item->name}</option>
				{/foreach}
			</select>
		</li>
		<li class="well">
			<button type="submit">Add</button>	
		</li>
	</ul>
</form>
