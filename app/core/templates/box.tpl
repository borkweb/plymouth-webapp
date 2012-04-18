{if $webapp_max_box_size}
	{assign var='webapp_box_ao' value='alpha omega'}
{else}
	{assign var='webapp_box_ao' value=''}
{/if}

{if $box.style}
	{assign var='webapp_box_style' value=`$box.style`}
{else}
	{assign var='webapp_box_style' value='border-box'}
{/if}

{if $box.size}
	{assign var='webapp_box_size' value=`$box.size`}
{elseif $webapp_max_box_size}
	{assign var='webapp_box_size' value=`$webapp_max_box_size`}
{else}
	{assign var='webapp_box_size' value=`$webapp_max_size`}
{/if}

{if $box.title_size && !$box.no_title_size}
	{assign var='webapp_title_size' value=`$box.title_size`}
	{assign var='webapp_stitle_size' value=`$webapp_box_size-$box.title_size`}
{elseif !$box.no_title_size}
	{assign var='webapp_title_size' value=`$webapp_box_size`}
	{assign var='webapp_stitle_size' value=`$webapp_box_size`}
{/if}

<div {if $box.id}id="{$box.id}"{/if} class="{if $webapp_box_size && !$box.no_grid}grid_{$webapp_box_size} {$webapp_box_ao} {/if}box {$webapp_box_style} {$box.class}{if ! $box.title && ! $box.secondary_title} box-notitle{/if}">
	{if $box.title || $box.secondary_title}
		<div class="title"><span class="obj1"></span><span class="obj2"></span>
			<div class="box-inner">
				<h2 class="{if $webapp_title_size}grid_{$webapp_title_size} {/if}primary">{$box.title}</h2>
				{if $box.secondary_title}
					<div class="{if $webapp_stitle_size}grid_{$webapp_stitle_size} {/if}secondary">
						{$box.secondary_title}
					</div>
				{/if}
				<div class="clear"></div>
			</div>
		</div>
	{/if}
	{if $box.subheader}
	<div class="subheader">
		<div class="box-inner">
			{$box.subheader}
		</div>
	</div>
	{/if}
	<div class="body">
		<div class="box-inner" {if $box.body_id}id="{$box.body_id}"{/if}>
			{$box.content}
		</div>
	</div>
	<div class="foot">
		<div class="box-inner">
			{$box.foot}
		</div>
	</div>
</div>
