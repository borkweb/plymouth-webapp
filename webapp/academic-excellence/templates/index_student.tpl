{* Called by index.tpl if user_type is 'aestudent' *}

{box title="Welcome!"}
<form action="{$PHP.BASE_URL}/student_update.php" method="post" class="label-left">
	<p>Congratulations for your outstanding academic performance in attaining a 3.50 or higher cumulative GPA as a full-time student.

	{if not $editing}
		<div class="response">
		<p>Thank you for your response. You have indicated that you:</p>
		<ul>
			{if not $PHP.IS_SUMMER}
				<li><strong>Would {if $student.confirmed == 0}not{/if}</strong> like to attend ceremony.</li>
			{/if}
			<li><strong>Would {if $student.confirmed_cert == 0}not{/if}</strong> like to receive a certificate.</li>
		</ul>
		<p>You may <a href="{$PHP.BASE_URL}/edit.php">change your reply</a> if you wish.</p>
		</div>
	{/if}

	{* Hide input if they said they don't want a certificate. *}
	{if ! $PHP.IS_SUMMER}
		<h3>Academic Excellence Ceremony</h3>

		<p>You are invited to attend a special ceremony in your honor, to be held on {$PHP.DINNER_DATE|date_format:'%B %e, %Y'}.</p>

		<ul>
			<li>
				<label>Attending:</label>
				{if $editing}
					<select name="confirmed">
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				{else}
					{if $student.confirmed == 1}
						Yes
					{else}
						No
					{/if}
				{/if}
			</li>
			{if $student.confirmed != 0}
				<li>
					<label>Number of Guests:</label>
					{if $editing}
						<input type="text" size="5" maxlength="1" value="{$student.guest_count}" id="guest_count" name="guest_count"/>
					{else}
						{$student.guest_count}
					{/if}
				</li>
				<li>
					<label>Special Needs:</label>
					{if $editing}
						<textarea id="ceremony_needs" name="ceremony_needs" cols="90" rows="10">{$student.ceremony_needs|escape}</textarea><br>
					{else}
						{$student.ceremony_needs|escape|nl2br}
					{/if}
			{/if}
		</ul>
		<br class="clear"/>
	{/if}

	<h3>Academic Excellence Certificate</h3>

	<p>Would you like to receive a certificate from the University honoring your
	achievement?  If so, please enter a mailing address for this certificate.</p>

	<ul class="form_fields">
		<li>
			<label>Receive Certificate?</label>
			{if $editing}
				<select name="confirmed_cert">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			{else}
				{if $student.confirmed_cert == 1}
					Yes
				{else}
					No
				{/if}
			{/if}
		</li>
		{if $student.confirmed_cert != 0}
			<li>
				<label>Name:</label>
				{$student.full_name|escape}<br class="clear"/>
			</li>
			<li>
				<label for="addr1">Address Line 1:</label>
				{if $editing}
					<input type="text" size="30" maxlength="30" value="{$student.addr1|escape}" id="addr1" name="addr1"/>
				{else}
					{$student.addr1|escape}<br class="clear"/>
				{/if}
			</li>
			<li>
				<label for="addr2">Address Line 2:</label>
				{if $editing}
					<input type="text" size="30" maxlength="30" value="{$student.addr2|escape}" id="addr2" name="addr2"/>
				{else}
					{$student.addr2|escape|default:'<em>none</em>'}<br class="clear"/>
				{/if}
			</li>
			<li>
				<label for="city">City:</label>
				{if $editing}
					<input type="text" size="30" maxlength="30" value="{$student.city|escape}" id="city" name="city"/>
				{else}
					{$student.city|escape}<br class="clear"/>
				{/if}
			</li>
			<li>
				<label for="state">State:</label>
				{if $editing}
					<input type="text" size="2" maxlength="2" value="{$student.state|escape}" id="state" name="state"/>
				{else}
					{$student.state|escape}<br class="clear"/>
				{/if}
			</li>
			<li>
				<label for="">Zip:</label>
				{if $editing}
					<input type="text" size="10" maxlength="10" value="{$student.zip|escape}" id="zip" name="zip"/>
				{else}
					{$student.zip|escape}<br class="clear"/>
				{/if}
			</li>
			{if $editing}
				<li>
					<label>&nbsp;</label>
					<input type="submit" name="submit" value="Save" style="background-color: #97e088;" {if $preview} disabled="disabled" {/if}/>
				</li>
			{/if}
		{/if}
	</ul>
</form>
{/box}
