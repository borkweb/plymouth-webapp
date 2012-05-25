<div class="outer"><div class="middle"><div class="inner">

<div id="listing">
    <table>
    {foreach from=$valid_directories item=dir}
    <tr class="dir">
        <td class="name"><a href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:browse{$dir.path}"><span class="badge"></span>{$dir.title}</a></td>
        <td>{$dir.path}</td>
    </tr>
    {foreachelse}
    <tr class="dir">
        <td class="name error"><span class="badge"></span>Sorry, you do not have access to any directories.</li>
    </tr>
    {/foreach}
    </table>
</div>

</div></div></div>

{strip}
<div id="server-list">
<h2>Servers</h2>
<ul>
{foreach from=$servers item=server}
<li><a href="{$PHP.BASE_URL}/{$server}:">{$server}</a></li>
{/foreach}
</ul>
</div>
{/strip}

<script type="text/javascript">

jQuery(function(){
	rfjs.serverList = jQuery('#server-list');
	rfjs.serverSpan = jQuery('h1 span');

	jQuery('h1 span, #server-list').mouseover(rfjs.showSL).mouseout(rfjs.hideSL);

	rfjs.serverList.css({
		left: rfjs.serverSpan.offset().left,
		top: rfjs.serverSpan.offset().top + rfjs.serverSpan.height(),
		width: rfjs.serverSpan.width() + 15
	});
});
</script>
