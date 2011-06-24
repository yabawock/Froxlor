	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/flag.png" alt="" />&nbsp;
				{t}Change language{/t}
			</h2>
		</header>

		<section class="tinyform bradiusodd">
			<form method="post" action="{link area='admin' section='index' action='changeLanguage'}" enctype="application/x-www-form-urlencoded">
				<fieldset>
				<legend>Froxlor&nbsp;-&nbsp;{t}Change language{/t}</legend>
				<p>
					<label for="def_language">{t}Language:{/t}</label>&nbsp;
					<select id="def_language" name="def_language">{$language_options}</select>
				</p>
				<p class="submit">
					<input type="hidden" name="send" value="send" />
					<input type="submit" value="{t}Change language{/t}" />
				</p>
				</fieldset>
			</form>
		</section>
	</article>
