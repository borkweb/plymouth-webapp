<script type="text/javascript">
	var psu_slider = {
		min: {$price_range.min|default:0},
		max: {$price_range.max|default:100},
		selected_min: {$selected_price_range.min|default:$price_range.min},
		selected_max: {$selected_price_range.max|default:$price_range.max}
	};
</script>
{PSU_JS src="/js/jquery-plugins/jquery-ui.js"}    
{PSU_JS src="/js/jquery-plugins/colorbox/jquery.colorbox-min.js"}    
{PSU_JS src="`$PHP.BASE_URL`/js/behavior.js"}    
