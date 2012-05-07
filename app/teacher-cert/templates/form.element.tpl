<li {if $required}class="required" title="Required Field"{/if}>
<label for="{$name}">{$title}:</label>
{if $edit}
	{if 'select' == $type}
		<select name="{$name}">
			<option></option>
			{foreach from=$collection item=item}
				<option value="{$item->id}" {if $object && $object->$name == $item->id}selected="selected"{/if}>{$item->name}</option>
			{/foreach}
		</select>
	{else}
		<input type="{$type|default:'text'}" name="{$name}" value="{$object->$name}"/>
	{/if}
{else}
	<div class="uneditable-input">
		{assign var=element value=$object->$name}

		{if $collection}
			<a href="{$url}/{$element}">{$collection[$element]->name}</a>
		{else}
			{$object->$name}
		{/if}
	</div>
{/if}
</li>
