{box size=16 title="Announcements"}
<form class="label-left" action="{$PHP.BASE_URL}/admin/admincp/announcements/{$announcement.announceID}/edit/save" method="POST">
<ul>
	<li><label>Message:</label>
	<textarea cols="100" rows="10" name="message">{$announcement.message}</textarea></li>
	<li class="form-actions"><input type="Submit" value="Save"> </li>
</ul>
</form>
{/box}


