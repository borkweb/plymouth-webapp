<i class="psu-icon icon {$psuiconsize|default:'small'} {if $psuiconbox}boxed{/if} {if $psuiconflat}flat{/if} {if $psuiconclass}{$psuiconclass}{/if}">
	<span class="icon-{$psuiconcode}">
		{if $psusubvalue}
		<sub class="sub-value {$psusubvaluetype}">
			{if is_numeric( $psusubvalue )}
				{if $psusubvalue > 99 }
					99+
				{else}
					{$psusubvalue}
				{/if}
			{else}
				{$psusubvalue}
			{/if}
		</sub>
	{/if}
	{if $psuiconcodesub}
		<sub class="sub-icon icon-{$psuiconcodesub}"></sub>
	{/if}
	</span>
</i>
