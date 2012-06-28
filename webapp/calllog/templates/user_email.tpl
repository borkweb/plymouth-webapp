<!-- BEGIN: main -->
<label for="user_name">User Name: </label>
<input type="text" name="email_user_name" id="email_user_name" size="27" value="{caller.identifier}"/><br/>
<label class="label" for="last_name">Full Name: </label>
<input type="text" name="email_full_name" id="email_full_name" size="30" value="{caller.name_full}"/>
<label class="label" for="message">Message: </label><br/>
<textarea id="email_message" name="email_message" cols="40" rows="4" ></textarea><br/><br/>

<input type="hidden" name="email_caller_class" value="{caller.class}"/>
<input type="hidden" name="email_first_name" id="email_first_name" value="{caller.name_first}"/>
<input type="hidden" name="email_last_name" id="email_last_name" value="{caller.name_last}"/><br/>
<input type="button" class="btn primary" onClick="sendHelpDeskMail('{email_call_id}', '0', 'send')" value="Send Mail"/>
<input type="button" class="btn danger" onClick="sendHelpDeskMail(0, 0, '')" value="Cancel"/>
<!-- END: main -->
