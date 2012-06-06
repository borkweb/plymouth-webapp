{box title=$title size=16}
{if $smarty.get.username}
Password test for <a href="{$PHP.BASE_URL}/user/{$smarty.get.username}">{$smarty.get.username}</a>.
{/if}
<p>Use this form to test if a user's password is set to the expected value (ie. after a password reset).</p>

<form action="password-test.html" method="post">
	<ul>
		<li><label for="username">Username:</label><input type="text" name="username" size="15" value="{$passtest.username|escape}"></li>
		<li><label for="password">Password:</label><input type="password" name="password" size="15"></li>
		<li><label></label><input type="submit" value="Test"></li>
	</ul>
</form>

{if $passtest.ad}
	<h2>Results</h2>
	<ul class="bullets">
	<li>Active Directory: {$passtest.ad}</li>
	</ul>
{/if}

{/box}
