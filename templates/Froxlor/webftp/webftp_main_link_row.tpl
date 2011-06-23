<tr {$checked_color}>
	<td><input type="checkbox" name="file[]" value="{$myDir.name}" {$checked} /></td>
	<td><a href="{link action="cd" file=$myDir.name currentDir=$currentDir}"><img src="{$image_folder}/icons/link.gif" align="top" border="0" /></a></td>
	<td><a href="{link action="cd" file=$myDir.name currentDir=$currentDir}">{$myDir.name}</a></td>
	<td align=right>{$myDir.size}</td>
	<td>{$myDir.date}</td>
	<td>{$myDir.perms}</td>
	<td>{$myDir.user}</td>
	<td>{$myDir.group}</td>
	<td><a href="{link action="deldir" file=$myDir.name currentDir=$currentDir}"><img src="{$image_folder}/icons/delete.png" border="0" alt="{t}Delete{/t}"></a></td>
	<td><a href="{link action="rename" op="show" file=$myDir.name currentDir=$currentDir}"><img src="{$image_folder}/icons/rename.gif" border="0" alt="{t}Rename{/t}"></a></td>
	<td colspan="2">{t}Symbolic link to{/t}: {$myDir.target}&nbsp;</td>
</tr>