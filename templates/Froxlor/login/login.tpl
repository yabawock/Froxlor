	<article class="login bradius">
		<header class="dark">
			<img src="{$header_logo}" alt="Froxlor Server Management Panel" />
		</header>

		{if isset($update_in_progress)}
			<div class="warningcontainer bradius">
				<div class="warning">{$update_in_progress}</div>
			</div>
		{/if}

		{if isset($successmessage)}
			<div class="successcontainer bradius">
				<div class="successtitle">{t}Information{/t}</div>
				<div class="success">{$successmessage}</div>
			</div>
		{/if}

		{if isset($message)}
			<div class="errorcontainer bradius">
				<div class="errortitle">{t}Error{/t}</div>
				<div class="error">{$message}</div>
			</div>
		{/if}

		<section class="loginsec">
			<form method="post" action="{link}" enctype="application/x-www-form-urlencoded">
				<fieldset>
				<legend>{t}Froxlor - Login{/t}</legend>
				<p>
					<label for="loginname">{t}Username{/t}:</label>&nbsp;
					<input type="text" name="loginname" id="loginname" value="" required/>
				</p>
				<p>
					<label for="password">{t}Password{/t}:</label>&nbsp;
					<input type="password" name="password" id="password" required/>
				</p>
				<p>
					<label for="language">{t}Language{/t}:</label>&nbsp;
					<select name="language" id="language">$language_options</select>
				</p>
				<p class="submit">
					<input type="hidden" name="send" value="send" />
					<input type="submit" value="{t}Login{/t}" />
				</p>
				</fieldset>
			</form>

			<aside>
				{if $settings.panel.allow_preset == '1'}
					<a href="{link area="login" section="login" action="forgotpwd"}">{t}Forgot password{/t}</a>
				{else}
					&nbsp;
				{/if}
			</aside>

		</section>

	</article>
