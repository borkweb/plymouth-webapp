{foreach from=$person->student->levels item=level}
	{assign var=student value=`$person->student->$level`}
	{assign var=termcode value=`$student->term_code`}
	{if $termcode|substr:4:2 =='40'}{assign var="termcode" value=$person->student->ug->term_code+70}
	{elseif $termcode|substr:4:2 =='20'}{assign var="termcode" value=$person->student->ug->term_code+10}
	{elseif $termcode|substr:4:2 =='92'}{assign var="termcode" value=$person->student->gr->term_code+1}
	{elseif $termcode|substr:4:2 =='94'}{assign var="termcode" value=$person->student->gr->term_code+97}
	{elseif $termcode|substr:4:2 =='80'}{assign var="termcode" value=$person->student->gr->term_code+11}{/if}

	{foreach from=$person->student->web_registration($termcode) item=item key=field name=webreg_loop}
		{if $smarty.foreach.webreg_loop.first}
<div id="ape_webreg" class="ape-section {if $myuser->go_states.ape_webreg === '0'}ape-section-hidden{/if}">
	<h3>Web Registration</h3>	
	<ul class="apedata">
			{/if}
			<li><label>{$field|replace:'_':' '|capitalize}: </label>{$item}</li>
			{if $smarty.foreach.webreg_loop.last}
	</ul>		
</div>
		{/if}
	{/foreach}
{/foreach}
