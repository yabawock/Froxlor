	<header>
		<h2>{t}Update{/t}</h2>
	</header>
	<article>
		<form action="{link section="updates"}" method="post">
			{$update_information}
			<p class="submit">
				<input type="hidden" name="s" value="{$s}"/>
				<input type="hidden" name="page" value="{$page}"/>
				<input type="hidden" name="send" value="send" />
				<input type="submit" value="{t}Proceed{/t}" />
			</p>
		</form>
	</article>