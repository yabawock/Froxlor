<tr class="{if $error}formerror{/if}">
	<td{$style} class="formlabeltd{if $error} formerror{/if}">
		<label for="{$fieldname}">{$label}{$mandatory}:
		{if $desc != ''}
			<br /><span style="font-size:85%;">{$desc}</span>
		{/if}
		</label>
	</td>
	<td class="{if $error}formerror{/if}">
		{$data_field}
	</td>
</tr>
