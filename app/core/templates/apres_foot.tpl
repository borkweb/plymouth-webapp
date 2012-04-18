<section class="grid_4" id="foot-logo-column">
	<a class="logo" href="http://go.plymouth.edu/home" title="Plymouth State University">Plymouth State University</a>
</section>
<nav class="grid_6" id="foot-quick-links-column">
	{if $webapp_apres_center}
		{$webapp_apres_center}
	{else}
	<h3>Quick Links</h3>
	<ul class="grid_3 alpha"> 
		<li><a title="A-Z Site Index" href="http://go.plymouth.edu/siteindex">A-Z Site Index</a></li> 
		<li><a title="Admissions" href="http://go.plymouth.edu/admissions" target="_blank">Admissions</a></li> 
		<li><a title="Athletics" href="http://go.plymouth.edu/athletics">Athletics</a></li> 
		<li><a title="Employment Opportunities at PSU" href="http://go.plymouth.edu/hr" target="_blank">Employment</a></li> 
		<li><a title="Giving Online" href="http://go.plymouth.edu/giving">Giving  to PSU</a></li> 
	</ul>
	<ul class="grid_3 omega"> 
		<li><a title="myPlymouth" href="http://go.plymouth.edu/my" target="_blank">myPlymouth</a></li> 
		<li><a title="Lamson Library &amp; Learning Commons" href="http://go.plymouth.edu/library" target="_blank">Lamson Library</a></li> 
		<li><a title="Campus Directory" href="http://go.plymouth.edu/phonebook" target="_blank">Campus Directory</a></li> 
		<li><a title="Campus Life" href="http://go.plymouth.edu/campuslife" target="_blank">Campus Life</a></li> 
		<li><a title="iGrad" href="http://go.plymouth.edu/igrad">iGrad</a></li> 
	</ul>
	{/if}
</nav>
<nav class="grid_6" id="foot-hot-links">
	<h3>{if $smarty.session.username}My {/if}Hot Links</h3>
	<ul>
	{foreach from=$webapp_hot_links item=link name=hot_links}
		{if $smarty.foreach.hot_links.index < 5}
			<li><a href="{$link.url}">{$link.title}</a></li>
		{/if}
	{/foreach}
	</ul>
</nav>
