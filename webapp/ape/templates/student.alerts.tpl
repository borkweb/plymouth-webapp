	{if $person->deceased || $person->confidential || empty($person->student->levels)}
	<div class="alerts">
		{if $person->confidential}<strong style="display:block;">This account is marked as CONFIDENTIAL.</strong>{/if}
		<ol>
			{if $person->confidential}
				<li>Confidential accounts must be kept private.  No information may be given to third parties regarding this user OR even the existence of this user's records in our system.</li>
			{/if}
			{if $person->deceased}
				<li>This person is deceased.</li>
			{/if}
			{if empty($person->student->levels)}
				<li>This person is not an active student.</li>
			{/if}
		</ol>
	</div>
	{/if}
