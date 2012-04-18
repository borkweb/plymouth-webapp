{box title="Testing" size=16}
	<form action="{$PHP.BASE_URL}/_testing" method="post">
		<ul class="label-left">
			<!--
			<li>
				<label>Award Status:</label>
				<select name="award_status">
					<option>Default</option>
					<option>Accepted</option>
					<option>Accepted, Offered</option>
				</select>
			</li>
			<li>
				<label>Award Terms:</label>
				<select name="award_terms[]" size="4" multiple>
					<option>Summer</option>
					<option>Fall</option>
					<option>Winter</option>
					<option>Spring</option>
				</select>
			</li>
			-->
			<li>
				<label>Data Mocking:</label>
				<input id="data_mock" name="data_mock" type="checkbox" value="1" {if $testing.data_mock}checked{/if}>
				<small>Show a sample of award data, active messages, requirements, and attendance cost. rather than target's real data.</small>
			</li>
			<li>
				<label for="empty_results">Empty Results:</label>
				<input id="empty_results" name="empty_results" type="checkbox" value="1" {if $testing.empty_results}checked{/if}>
				<small>Simulate no awards, active messages, requirements, or attendance cost. Overrides "Data Mocking."</small>
			</li>
			{if $params.admin}
			<li>
				<label>Force admin verify:</label>
				<input id="force_verify" name="force_verify" type="checkbox" value="1" {if $testing.force_verify}checked{/if}>
				<small>Show the identity verification screen, rather than letting you view anyone.</small>
			</li>
			{/if}
			<li>
				<label>&nbsp;</label>
				<input type="submit" value="Save Changes">
			</li>
		</ul>
	</form>
{/box}
