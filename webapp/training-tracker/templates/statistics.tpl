{PSU_CSS href="$base_url/css/stats.css"} 

{* setting javascript variables *}
<script> 	
	var current_user_wpid = "{$current_user->wpid}";
	var active_user_wpid = "{$app->user->wpid}";
	var current_user_level = "{$current_user_level}";
</script>

{PSU_JS src="$base_url/js/statistics.js"}

{box title=$title size="16"}
	{* This is the outer accordian that shows the level ie trainee *}
	{foreach from=$checklist_item_cat item=category}
		<span class="permission-level"><h4>{$category.name}</h4></span>
		<div class = "overall-progress">Overall progress: <span id='total-progress'>{$progress}</span>%
			<div id="overall" data-progress="{$progress}" class="progressbar"></div>
		</div>

		<div id="goals"> 
			{foreach from=$checklist_item_sub_cat item=sub_category}
				<h3><a href="#">{$sub_category.name} progress: <span class = "progress">{$sub_category.stat}</span>%<div id="{$sub_category.id}" data-progress="{$sub_category.stat}" class="progressbar"></div></a></h3>
				{*  foreach category look at each sub category and add every item per sub category *}
				<div class="inner-goals" {if $sub_category.slug == "mpc-skills"}data-divisor="2"{elseif $sub_category.slug == "at-skill"}data-divisor="1"{/if}> 
				{foreach from=$checklist_items item=item}
					{if $item.category_id eq $sub_category.id}
						<label class="chkbox-container" for="{$item.id}" {if isset($item.updated_by)}title="Last modified by - {$item.updated_by} on {$item.updated_time}."{else}title="This item hasn't been updated yet."{/if}><input class="chkbox" type="checkbox" {if $item.checked}checked="true" {/if} id="{$item.id}" {if $disabled}disabled="disabled"{/if}>{$item.description}</label>
					{/if}
				{/foreach}
				</div>
			{/foreach}
		</div>
	{/foreach}

	<form action="/webapp/training-tracker/staff/checklist/comments/{$current_user->person()->wpid}" method="post">
		<textarea class="txtarea" rows="10" cols="40" name="comments">{$comments} {* comment section *}
		</textarea>
		<button name='name' type="submit" value="save" class="btn">Save</button>
		{if $progress eq 100}
			{if ($active_user_level eq 'supervisor' || $active_user_level eq 'shift_leader' || $active_user_level eq 'manager' || $active_user_level eq 'webguru')}
				<span class="confirm-text">Pressing the confirm button will send an email to your boss saying {$current_user->person()->formatname("f l")} has completed the tasks above</span>
				<button name='name' value='confirm' class="btn btn-warning confirm">Confirm</button>
			{/if}
		{/if}

	</form>

{/box}

