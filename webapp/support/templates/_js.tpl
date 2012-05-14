{psu_js src="`$PHP.BASE_URL`/js/behavior.js"}
{psu_js src="`$PHP.BASE_URL`/js/calllog.js"}
{psu_js src="/webapp/calllog/js/main.js"}
{if isset( $smarty.get.go )}
<script type="text/javascript">
$(document).ready(function() {
	$('#checklists select').val('{$smarty.get.go}'); 
	$('#checklists select').triggerHandler('change');
});
</script>
{/if}
