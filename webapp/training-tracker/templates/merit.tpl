{PSU_CSS src='../css/merit.css'}
<script>
	var merits = {$merits};
</script>
{PSU_JS scr='../js/merit.js'}

{box title="Gold stars and dog houses" size="16"}
	{foreach from=$staff item=person name=ct}
		<div id='{$person->wpid}' class = 'person bordered-top{if $smarty.foreach.ct.iteration is odd} striped{/if}'>
			<span class='name' data-wpid='{$person->wpid}'>{$person->name}</span>
			<button data-type='add' data-wpid="{$person->wpid}" class='add btn btn-success'>Add new merit</button>
			<button data-type='remove' data-wpid="{$person->wpid}" class='remove btn btn-danger'>Remove a merit</button>
			<div class='hidden new'>
				<span class='new-title'>Add a new merit</span>
				<span class='new-type'>Type: 
					<input type='radio' name='type' value='star' data-text='Star'>Star
					<input type='radio' name='type' value='dog-house' data-text='Dog House'>Dog House
				</span>
				<span class='comment-title'>Add additional information about this merit</span>
				<textarea rows='3' cols='30'></textarea>
				<button class='confirm'>Add</button>
			</div>
			<div class='hidden old'>
				<span id='stars'>Stars</span>
				<ul class='current-merit'>
				</ul>
				<span id='dog-houses'>Dog Houses</span>
				<ul class='current-demerit'>
				</ul>
				<button class='remove-old'>Remove Selected</button>
			</div>
		</div>
	{/foreach}
{/box}
