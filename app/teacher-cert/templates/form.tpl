{if $edit}
<form method="POST" {if $url}action="{$url}"{/if}>
	<ul>
{else}
<div class="field-wrapper">
	<ul class="clean label-left">
{/if}
	{if $form}
		{include file="form.`$form`.tpl" edit=$edit}
	{else}
		{foreach from=$model->elements() item=element}
			{$element->as_li()}
		{/foreach}
	{/if}
	{if $edit}
	<li class="well">
		{if $delete_url}
			<a href="{$delete_url|escape}" class="btn danger js-confirm-delete">Delete {$what}</a>
		{/if}
		<button type="submit" class="btn primary right">{$action} {$what}</button>
	</li>
	{/if}
{if $edit}
	</ul>
</form>
{else}
	</ul>
</div>
{/if}
