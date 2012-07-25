{PSU_CSS src='../css/merit.css'}
<link rel="stylesheet" type="text/css" href="../templates/style.css" media="screen" />
<link rel='stylesheet' type='text/css' src='../css/merit.css'>
<script>
	//var merits = {$merits};
</script>
{PSU_JS scr='../js/merit.js'}
<script type="text/javascript" src="../js/merit.js">// here for testing</script>

{box title="Gold stars and dog houses" size="16"}
	{foreach from=$staff item=person name=ct key=key}
		{assign var=wpid value=$person->wpid} 
		{col size = 16 class='person bordered-top striped' id=$person->wpid}
				{col size=4}
					{foreach from=$merits.$wpid.demerits item=demerit}
						<img title = "{$demerit.notes}" data-merit-id='{$demerit.id}' class='merit demerit left' src='https://s0.plymouth.edu/images/icons/22x22/status/dialog-warning.png'>			
					{/foreach}

					{foreach from=$merits.$wpid.merits item=merit}
						<img title = "{$merit.notes}" data-merit-id='{$merit.id}' class='merit left' src='../images/star.png'>
					{/foreach}

					<span class='name' data-wpid='{$person->wpid}'>{$person->name}</span>
				{/col}

				{col size = 6}
					<button data-type='add' data-wpid="{$person->wpid}" class='add btn btn-success'>Add new merit</button>
				{/col}

				{col size = 3}
					<button data-type='remove' data-wpid="{$person->wpid}" class='remove btn btn-danger'>Remove a merit</button>
				{/col}

				{col size=16}
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
				{/col}
				{col size=16}
				<div class='hidden old'>
					<span id='stars'>Stars</span>
					<ul class='current-merit'>
						{foreach from=$merits.$wpid.merits item=merit}
							<li data-merit-id='{$merit.id}'><input type="checkbox"> {$merit.notes}</li>
						{/foreach}
					</ul>
					<span id='dog-houses'>Dog Houses</span>
					<ul class='current-demerit'>
						{foreach from=$merits.$wpid.demerits item=demerit}
							<li data-merit-id='{$demerit.id}'><input type="checkbox"> {$demerit.notes}</li>
						{/foreach}
					</ul>
					<button class='remove-old'>Remove Selected</button>
				</div>
				{/col}
		{/col}
	{/foreach}
{/box}

