{foreach from=$tables item=data key=table}
{box size="4" title="<small>`$table`</small>"}
	<ul style="font-size: 1.5em;{if $data.new == $data.old || $data.notes }opacity: 0.2;{/if}">
		<li><strong>Old:</strong> {$data.old|number_format}</li>
		<li ><strong>New:</strong> <span style="font-weight: bold;color:{if $data.new < $data.old && ! $data.notes}red{else}inherit{/if}">{$data.new|number_format}</span></li>
	</ul>
	
	<div style="opacity: 0.3;">
		<small>{$data.notes|default:"&nbsp;"}</small>
	</div>

	{if $data.differences}
		<a href="" class="differences-link">View Differences</a>
		<div style="display:none;" class="differences">
			{psu_dbug var=$data.differences}
		</div>
	{else}
		<span style="opacity: 0.2;">View Differences</span>
	{/if}
{/box}
{/foreach}

<script>
{literal}
$(function(){
	
	$('.differences-link').on('click', function(e) {
		e.preventDefault();
		var $el = $(this);
		var $box = $el.closest('.box');
		var $diff = $box.find('.differences');

		if( $box.hasClass('grid_16') ) {
			$box.removeClass('grid_16');
			$diff.hide();
		} else {
			$box.addClass('grid_16');
			$diff.show();
		}//end else
	});
});
{/literal}
</script>
