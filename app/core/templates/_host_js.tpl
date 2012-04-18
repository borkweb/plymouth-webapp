	<script>
	var HOST = 'http{if $smarty.server.HTTPS == 'on'}s{/if}://{$smarty.server.HTTP_HOST}';
	var CDN = {if $smarty.const.PSU_CDN}'{$cdn}'{else}HOST{/if};
	var BASE_URL = '{$PHP.BASE_URL}';
	var my_js_loaded = false;
	</script>
