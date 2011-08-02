$header
	<article>
		<header>
			<h2>
				<img src="images/Froxlor/{$image}" alt="{$title}" />&nbsp;
				{$title}
			</h2>
		</header>

		<section class="fullform bradiusodd">

			<form action="{$linker->getLink(array('section' => 'domains', 'page' => 'register', 'step' => 'search'))}" method="post" enctype="application/x-www-form-urlencoded">
				<fieldset>
					<legend>Froxlor&nbsp;-&nbsp;{$title}</legend>

					<table class="formtable">
						<fieldset>
						<legend>Froxlor&nbsp;-&nbsp;Domain search</legend>
						<p>
							<label for="def_language">Domain:</label>&nbsp;
							<input type="text" id="def_language" name="domain" />
						</p>
						<p class="submit">
							<input type="hidden" name="s" value="$s" />
							<input type="hidden" name="page" value="$page" />
							<input type="hidden" name="send" value="send" />
							<input type="submit" value="Search" />
						</p>
						</fieldset>
					</table>

					<p style="display: none;">
						<input type="hidden" name="s" value="$s" />
						<input type="hidden" name="page" value="$page" />
						<input type="hidden" name="action" value="$action" />
						<input type="hidden" name="send" value="send" />
					</p>
				</fieldset>
			</form>

		</section>

	</article>
$footer
