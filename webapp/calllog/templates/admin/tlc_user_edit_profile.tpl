<!-- BEGIN: main -->
<table width="100%" align="center" valign="top" cellpadding="0" cellspacing="0">
<tr>
	<td width="100%" align="center">
		<table width="100%" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td width="100%" align="left">
				<h2>&#187; TLC Users</h2>
				<span class="page_content_summary">Showing TLC User Information.</span>
			</td>
		</tr>
		<tr>
			<td width="100%" align="left" valign="middle">
				<li class="page_nav_link">
					<a href="tlc_user_profile.html?user_name={user_name}" title="View Your TLC Profile.">View My Profile</a>
				</li>
				<li class="page_nav_link">
					<a href="tlc_user_edit_profile.html" title="Edit Your TLC Profile.">Edit My Profile</a>
				</li>
				<li class="page_nav_link_last">
					<a href="tlc_users.html" title="View TLC Profiles.">TLC Profiles</a>
				</li>
				<hr />
			</td>
		</tr>
		<tr>
		<!-- 
		BLOCKS 
		------------------------------
		photo_updated_successfully
		error_updating_photo
		comments_updated_successfully
		error_updating_comments
		phone_updated_successfully
		error_updating_phone
		------------------------------
		-->
			<td width="100%" align="center" valign="top" class="content">
				<table width="100%" align="center" cellpadding="3" cellspacing="1" class="content">
				<tr>		
					<td width="100%" align="left" valign="top" class="content">
						<table width="100%" align="center" valign="top" cellpadding="0" cellspacing="0">
						<tr>
							<td width="100%" align="center">
								<table width="100%" align="center" cellpadding="3" cellspacing="0">
								<!-- BEGIN: status_message -->
								<tr>
									<td width="100%" align="left" class="content_head">
									   Profile Update Status:	
									</td>
								</tr>
								<tr>
									<td width="100%" align="left">
										<table width="100%" align="center" valign="top" cellpadding="5" cellspacing="1" class="content">
										<tr>
											<td class="content" align="left" valign="middle" nowrap="nowrap">
											<!-- BEGIN: message -->
												<p>{message}</p>
											<!-- END: message -->
											</td>
										</tr>
										</table>
									</td>
								</tr>
								<!-- END: status_message -->
								<!-- BEGIN: edit_profile_form -->
								<tr>
									<td width="100%" align="left" valign="middle">
									   To change your profile, edit the fields below.	
									</td>
								</tr>
								<tr>
									<td width="100%" align="left" valign="middle">
									<form name="edit_profile" enctype="multipart/form-data" method="post" action="{form_action}">
									<input type="hidden" name="user_name" value="{user_name}" />
										<table width="100%" align="center" valign="top" cellpadding="2" cellspacing="1" class="content">
										<tr>
											<td width="120" class="content_head" align="left">
												Phone
											</td>
											<td class="content" align="left" valign="middle">
												<input type="text" value="{phone}" name="phone" size="40" />
											</td>
										</tr>
										<tr>
											<td class="content_head" align="left" nowrap="nowrap">
												Photo
											</td>
											<td class="content" align="left" valign="middle">	
												<!--<input type="file" name="photo" value="{photo}" size="40" />-->
												<input type="file" name="photo" value="" size="40" />
											</td>
										</tr>
										<tr>
											<td class="content_head" align="left" nowrap="nowrap">
												Comments
											</td>
											<td class="content" align="left" valign="middle">
												<textarea name="comments" rows="9" cols="40">{comments}</textarea>	
											</td>
										</tr>
										<!--
										<tr>
											<td class="content_head" align="center" nowrap="nowrap">
												Phone
											</td>
											<td class="content" align="left" valign="middle">
												{phone}	
											</td>
										</tr>
										-->
										<tr>
											<td class="content_head" align="center" colspan="2" nowrap="nowrap">
												<a href="javascript:document.edit_profile.submit()" title="Update Your Profile." class="action">Update</a>
												&nbsp; | &nbsp;
												<a href="tlc_users.html" title="Cancel." class="action">Cancel</a>
											</td>
										</tr>
										</table>
									</form>
									</td>
								</tr>
								<!-- END: edit_profile_form -->
								</table>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="100%" align="left" valign="middle">
				<hr />
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
<!-- END: main -->
