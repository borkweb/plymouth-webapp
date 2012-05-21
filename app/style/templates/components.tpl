{box title="Messages" style=hero size=16 id=messages}{/box}
<div class="message-container grid_16">
<div class="message message-messages">
	<ul>
			<li>This is an example message</li>
			<li>This is a second message.</li>
			<li>Set by: <code>$_SESSION['messages'][] = 'Your message';</code></li>
		</ul>
</div>
</div>
<div class="message-container grid_16">
<div class="message message-successes">
	<ul>
			<li>This is an example success message</li>
			<li>Set by: <code>$_SESSION['successes'][] = 'Your message';</code></li>
		</ul>
</div>
</div>
<div class="message-container grid_16">
<div class="message message-warnings">
	<ul>
			<li>This is an example warning message</li>
			<li>Set by: <code>$_SESSION['warnings'][] = 'Your message';</code></li>
		</ul>
</div>
</div>
<div class="message-container grid_16">
<div class="message message-errors">
	<ul>
			<li>This is an example error message</li>
			<li>Set by: <code>$_SESSION['errors'][] = 'Your message';</code></li>
		</ul>
</div>
</div>
{box title="Lazy Loading" style=hero size=16 id=lazy}{/box}
{box title='Person Data' size=16}
	<div class="grid_11 alpha">
		<h3>Code for lazy loading PSU Person data</h3>
		<pre class="prettyprint">
&lt;ul class="psu-lazyload" data-object=person data-id=200443&gt;
	&lt;li class="lazy-field" data-type="login_name"&gt;&lt;/li&gt;
	&lt;li class="lazy-field" data-type="email"&gt;&lt;/li&gt;
	&lt;li class="lazy-field" data-type="id"&gt;&lt;/li&gt;
	&lt;li class="lazy-field" data-type="wp_id"&gt;&lt;/li&gt;
	&lt;li class="lazy-field" data-type="first_name"&gt;&lt;/li&gt;
	&lt;li class="lazy-field" data-type="last_name"&gt;&lt;/li&gt;
&lt;/ul&gt;
		</pre>
	</div>
	<div class="grid_4 omega">
		<h3>What you get</h3>
		<ul class="psu-lazyload" data-object=person data-id=200443>
			<li class="lazy-field" data-type="login_name"></li>
			<li class="lazy-field" data-type="email"></li>
			<li class="lazy-field" data-type="id"></li>
			<li class="lazy-field" data-type="wp_id"></li>
			<li class="lazy-field" data-type="first_name"></li>
			<li class="lazy-field" data-type="last_name"></li>
		</ul>
		<h3>Inline example</h3>
		<div class="psu-lazyload" data-object=person data-id=50080>
			My username is <span class="lazy-field inline-field" data-type="login_name"></span>!
		</div>
	</div>
{/box}
