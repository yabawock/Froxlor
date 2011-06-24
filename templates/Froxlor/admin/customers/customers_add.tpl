	<article>
		<header>
			<h2>
				<img src="images/Froxlor/{$image}" alt="{$title}" />&nbsp;
				{$title}
			</h2>
		</header>

		<section class="fullform bradiusodd">

			<form action="{link area='admin' section='customers' action='add'}" method="post" enctype="application/x-www-form-urlencoded">
				<fieldset>
					<legend>Froxlor&nbsp;-&nbsp;{$title}</legend>

					<table class="formtable">
						{$customer_add_form}
					</table>

					<p style="display: none;">
						<input type="hidden" name="send" value="send" />
					</p>
				</fieldset>
			</form>

		</section>

	</article>
	<br />
	<article>
		<section class="fullform bradiusodd">
			<p style="margin-left:15px;">
				<span style="color:#ff0000;">*</span>: {t}This value is mandatory{/t}<br />
				<span style="color:#ff0000;">**</span>: {t}Either &quot;name&quot; and &quot;firstname&quot; or &quot;company&quot; must be filled{/t}
			</p>
		</section>
	</article>