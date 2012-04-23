{if $svninfo}
<div class="grid_8">
	<a href="{$PHP.BASE_URL}/changelog.html">APE r{$svninfo.revision}{if $svninfo.date} ({$svninfo.date|date_format:"%e %b %Y"}){/if}</a>
</div>
{/if}
<div class="grid_8" style="text-align: right;">
	<a href="{$PHP.BASE_URL}/actions/cache-flush.php" title="Flush the IDM cache">flush cache</a>
</div>
<script language="JavaScript" type="text/javascript" src="/includes/js/wz_tooltip.js"></script>
