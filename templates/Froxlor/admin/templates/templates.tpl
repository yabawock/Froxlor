$header
	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/templates.png" alt="" />&nbsp;
				{$lng['admin']['templates']['templates']}
			</h2>
		</header>

		<section>
			<table class="bradiusodd">
			<thead>
				<tr>
					<th>{$lng['login']['language']}</th>
					<th>{$lng['admin']['templates']['action']}</th>
					<th>{$lng['panel']['options']}</th>
				</tr>
			</thead>
			<tbody>
				{$templates}
			</tbody>
			</table>

			<if $add>
				<div class="overviewadd">
					<img src="images/Froxlor/icons/templates_add.png" alt="" />&nbsp;
					<a href="$filename?page=$page&amp;action=add&amp;s=$s">{$lng['admin']['templates']['template_add']}</a>
				</div>
			</if>

		</section>

	</article>
	<br />
	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/templates.png" alt="" />&nbsp;
				{$lng['admin']['templates']['filetemplates']}
			</h2>
		</header>

		<section>
			<table class="bradiusodd">
			<thead>
				<tr>
					<th>{$lng['admin']['templates']['action']}</th>
					<th>{$lng['panel']['options']}</th>
				</tr>
			</thead>
			<tbody>
				{$filetemplates}
			</tbody>
			</table>

			<if $filetemplateadd>
				<div class="overviewadd">
					<img src="images/Froxlor/icons/templates_add.png" alt="" />&nbsp;
					<a href="$filename?page=$page&amp;action=add&amp;s=$s&amp;files=files">{$lng['admin']['templates']['template_add']}</a>
				</div>
			</if>
		</section>

	</article>
$footer
