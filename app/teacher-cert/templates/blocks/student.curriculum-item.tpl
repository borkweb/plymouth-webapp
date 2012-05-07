{if $student_data->curriculum.$which}
<li>
	<strong>{$which|replace:'concentration':'option'|ucwords}{if count($student_data->curriculum.$which) != 1}s{/if}:</strong>
	<ul>
		{foreach from=$student_data->curriculum.$which item=curriculum_item}
		<li>
			{$curriculum_item.0.description}
		</li>
		{/foreach}
	</ul>
</li>
{/if}
