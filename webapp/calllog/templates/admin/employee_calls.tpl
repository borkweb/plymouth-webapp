<!-- BEGIN: main -->
<script type="text/javascript" language="javascript" charset="utf-8" src="{JS_WEB_DIR}/admin_functions.js"></script>
Employee Username: <input type="text" name="employee_calls_username" id="employee_calls_username"/>
<input type="button" name="employee_calls_fetch" id="employee_calls_fetch" value="Get Calls" onClick="getEmployeeCalls('{option}')" />
<div id="display_calls_loading" style="display: none;"><img src="{call_log_web_home}/images/loading-anim.gif" alt="Loading..."/>Loading Content Please Wait...</div>
<div id="display_calls"></div>
<!-- BEGIN: js_action -->
<script type="text/javascript">
	var action = {action};
	var option = '{option}';
	action(option);
</script>
<!-- END: js_action -->
<!-- END: main -->