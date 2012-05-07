<form id="add-{$what}" action="{$PHP.BASE_URL}/{$route}/{$object->id}/add-{$what}" method="POST">
	<ul>
		<li>
			<select name="{$what}_id">
				<option></option>
				{foreach from=$collection item=item}
					<option value="{$item->id}">{$item->last_name}, {$item->first_name} {$item->mi}</option>
				{/foreach}
			</select>
			<select name="position_id">
				<option></option>
				{foreach from=$app->positions item=item}
					<option value="{$item->id}">{$item->name}</option>
				{/foreach}
			</select>
		</li>
		<li class="well">
			<button type="submit">Add</button>	
		</li>
	</ul>
</form>
