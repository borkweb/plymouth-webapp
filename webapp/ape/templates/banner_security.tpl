<script type="text/javascript" charset="utf-8" src="/includes/js/jquery-plugins/jquery.scrollto.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/includes/js/jquery-plugins/jquery.supercolumns.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$.fn.supercolumns.defaults.throbber = '{$PHP.BASE_URL}/images/supercolumn_throbber.gif';
		$('#security').supercolumns({ });
	});
</script>

<style>
	/***
	 * The "*" position: static is being set to fix a positioning bug caused by the global
	 * PSUTemplate stylesheet setting * position: relative.
	 */
	* { position: static;}
	.supercolumns{
		background: #fff;
		border: 1px solid #ccc;
		font-family: arial, helvetica, sans-serif;
		position:relative;
	}
	.supercolumns ul{
		border-right: 1px solid #ccc;
		height: 100%;
		list-style-type: none;
		margin: 0;
		padding: 0;
		position: absolute;
		width: 250px;
	}
	
	.supercolumns ul div{
		height: 100%;
		overflow-x: hidden;
		overflow-y: auto;
	}
	
	.supercolumns li{
		padding: 0;
		margin: 0;
	}
	
	.supercolumns li a{
		display: block;
		padding: 0.25em;
	}
	
	.supercolumns ul ul{
		left: 250px;
		top: 0;
	}
	
	.supercolumns a{
		
		text-decoration: none;
	}
	
	.supercolumns a.selected{
		background: #418ff8;
		color: #fff;
	}
	
	.supercolumns .throbber{
		float:right;
	}
	
	.supercolumns .filter{
		background: #eee;
		border-bottom: 1px solid #ccc;
		padding: 0.25em;
		height: 24px;
	}
	
	.supercolumns .filter span{
		display: inline-block;
		width: 35px;
	}
	
	.supercolumns .filter input{
		width: 185px;
	}
	
	.supercolumns .no-data{
		font-style: italic;
		padding: 0.25em;
	}
</style>
{box title="Banner Security"}
	<ul id="security">
		<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=user" class="drill">Users</a></li>
		<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=role" class="drill">Roles</a></li>
		<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=class" class="drill">Classes</a></li>
		<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=form" class="drill">INB Forms</a></li>
		<li><a href="{$PHP.BASE_URL}/banner_security_data.html?load=object" class="drill">Tables, Views, etc.</a></li>
	</ul>
{/box}
