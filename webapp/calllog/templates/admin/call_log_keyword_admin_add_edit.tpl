<!-- BEGIN: main -->
<form name="add_edit_keyword" method="post" action="{form_action}">
<input type="hidden" name="keyword_id" value="{key.keyword_id}" />
<table width="75%" align="center" cellpadding="5" cellspacing="1" class="content">
<tr>
	<td width="200" align="left" valign="middle" class="content_head" nowrap="nowrap">
		Keyword Name
	</td>
	<td width="350" align="left" valign="middle" class="content" nowrap="nowrap">
		<input type="text" name="name" value="{key.name}{name}" size="40" />
	</td>
</tr>
<tr>
	<td align="left" valign="middle" class="content_head">
		Keyword ID (No spaces)
	</td>
	<td align="left" valign="middle" class="content">
		<input type="text" name="keyword" value="{key.keyword}{keyword}" size="40" />
	</td>
</tr>
<tr>
	<td align="left" valign="middle" class="content_head">
		Keyword Status
	</td>
	<td width="100%" align="left" valign="middle" class="content">
		<select name="active">
			<option value="1"{selected_active}>Active</option>
			<option value="0"{selected_inactive}>Inactive</option>
		</select>
	</td>
</tr>
<tr>
	<td width="100%" align="center" valign="middle" colspan="2">
		<!-- BEGIN: add_keyword -->
		<a href="javascript:document.add_edit_keyword.submit()" title="Add Keyword." class="action">Add Item</a>
		<!-- END: add_keyword -->
		<!-- BEGIN: update_keyword -->
		<a href="javascript:document.add_edit_keyword.submit()" title="Update Keyword." class="action">Update Keyword</a>
		<!-- END: update_keyword -->		
		&nbsp;&nbsp;
		<a href="{father_page}" title="Cancel Changes." class="action">Cancel</a>	
	</td>
</tr>
</table>
</form>
<!-- END: main -->