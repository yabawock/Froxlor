	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/display.png" alt="" />&nbsp;
				{t}Change theme{/t}
			</h2>
		</header>

		<section class="tinyform bradiusodd">
			<form method="post" action="{link area='admin' section='index' action='changeTheme'}" enctype="application/x-www-form-urlencoded">
				<fieldset>
				<legend>Froxlor&nbsp;-&nbsp;{t}Change theme{/t}</legend>
				<p>
					<label for="theme">{t}Theme:{/t}</label>&nbsp;
					<select id="theme" name="theme">{$theme_options}</select>
				</p>
				<p class="submit">
					<input type="hidden" name="send" value="send" />
					<input class="bottom" type="submit" value="{t}Change theme{/t}" />
				</p>
				</fieldset>
			</form>
		</section>
	</article>
