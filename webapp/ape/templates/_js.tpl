	{PSU_JS src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js'}
	{psu_js src="https://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"}
	{PSU_JS src="/app/core/js/jquery-plugins/jquery.jgrowl.js"}
	{PSU_JS src="/app/core/js/jquery-plugins/jquery.selectbox.js"}
	{PSU_JS src="/app/core/js/jquery-plugins/colorbox/jquery.colorbox-min.js"}
	{PSU_JS src="`$PHP.JS`/behavior.js?v=6"}
	{PSU_JS src="`$PHP.BASE_URL`/js/checklist.js"}
	<!--[if lt IE 7]>
	<script language="javascript" src="{"/app/core/js/correctPNG.js"|cdn}"></script>
	<![endif]-->
	<script language="javascript" src="{$PHP.BASE_URL}/js/authz.js"></script>
	{psu_authz_js}
