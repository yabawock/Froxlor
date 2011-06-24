	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/encrypted.png" alt="" />&nbsp;
				{t}Change password{/t}
			</h2>
		</header>

		<section class="tinyform bradiusodd">
			<form method="post" action="{link area='admin' section='index' action='changePassword'}" enctype="application/x-www-form-urlencoded">
				<fieldset>
				<legend>Froxlor&nbsp;-&nbsp;{t}Change password{/t}</legend>
				<p>
					<label for="old_password">{t}Old password:{/t}</label>&nbsp;
					<input type="password" id="old_password" name="old_password" />
				</p>
				<p>
					<label for="new_password">{t}New password:{/t}</label>&nbsp;
					<input type="password" id="new_password" name="new_password" />
				</p>
				<p>
					<label for="new_password_confirm">{t}New password (confirm):{/t}</label>&nbsp;
					<input type="password" id="new_password_confirm" name="new_password_confirm" />
				</p>
				<p class="submit">
					<input type="hidden" name="send" value="send" />
					<input type="submit" value="{t}Change password{/t}" />
				</p>
				</fieldset>
			</form>
		</section>
	</article>
