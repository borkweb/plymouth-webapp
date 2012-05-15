<form id="add-{$what}" action="{$PHP.BASE_URL}/{$route}/{$object->id}/add-{$what}" method="POST">
	<ul>
		<li>
			<select name="{$what}_id">
				<option></option>
				{foreach from=$collection item=item}
					<option value="{$item->school()->id}">{$item->school()->name}</option>
				{/foreach}
			</select>
		</li>
		<li class="well">
			<button type="submit">Add</button>	
		</li>
	</ul>
</form>
