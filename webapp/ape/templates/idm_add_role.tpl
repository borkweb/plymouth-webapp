<form action="{$PHP.BASE_URL}/actions/idm.php" method="post" id="idm_add_role">
<input type="hidden" name="action" value="add">
<input type="hidden" name="type" value="role">
<input type="hidden" name="pidm" value="{$person->pidm}">
<h4>Add Role</h4>
<ul>
<li>
	<label for="role">Role:</label>
	{html_options name=attribute[] options=$ape->nonApeRoles($person->pidm)}
</li>
<li>
	<label>Active Dates:</label>
	<input type="text" size="10" name="start_date" value="{$idm_add.start_date}">
	through
	<input type="text" size="10" name="end_date">
</li>
<li>
	<label>Grantor:</label>
	<input type="text" value="{$username}" disabled="disabled">
</li>
<li>
	<label>Source:</label>
	<input type="text" value="ape" disabled="disabled">
</li>
<li>
	<label>Reason:</label>
	<textarea cols="40" rows="5" name="reason" id="idm_add_reason"></textarea>
</li>
<li>
	<label>&nbsp;</label>
	Remaining: <span id="reason_left"></span>
<li>
	<label for="idm_add_submit">&nbsp;</label>
	<input type="submit" id="idm_add_submit" name="submit" value="Add">
</li>
</ul>
</form>
{literal}
<script type="text/javascript">
$(document).ready(function(){
	$('#idm_add_reason').keyup(function(){
		var len = $(this).val().length; 
		$('#reason_left').text(1000 - len);
	}).keyup();
});
</script>
{/literal}
