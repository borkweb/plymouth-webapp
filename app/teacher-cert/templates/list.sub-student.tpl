<ul>
{foreach from=$collection item=item}
	<li>
		<a href="{$PHP.BASE_URL}/gate-system/{$item.gate_system_slug}/{$item.student_gate_system_id}">{$item.last_name}, {$item.first_name} {$item.mi}</a> (Voucher: {$item.voucher|default:'0'})
	</li>
{/foreach}
</ul>
