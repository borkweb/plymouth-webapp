<script type="text/javascript">
{literal}
$(function(){
	$('#students').focus(function(){
		this.select();
	});
});
{/literal}
</script>
{box title="Email Addresses for Seniors That Haven't Signed Up"}
<h3>Number of Students: {$emails|@count}</h3>
<textarea id="students" style="width: 800px;height: 400px;">{foreach from=$emails item=email}{$email}; {/foreach}</textarea>
{/box}
