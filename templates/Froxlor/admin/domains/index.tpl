	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/domains.png" alt="" />&nbsp;
				{t}Domains{/t}&nbsp;({$domainscount})
			</h2>
		</header>

		<section>

			{if ((Froxlor::getUser()->getData("resources", "domains_used") < Froxlor::getUser()->getData("resources", "domains")
				|| Froxlor::getUser()->getData("resources", "domains") == '-1') && $domainscount > 15)
			}
				<div class="overviewadd">
					<img src="images/Froxlor/icons/domain_add.png" alt="" />&nbsp;
					<a href="{link area=admin section=domains action=add}">{t}Create domain{/t}</a>
				</div>
			{/if}

			<table class="bradiusodd">
				<thead>
					<tr>
						<th>{t}Domain name{/t}</th>
						<th>{t}IP:Port{/t}</th>
						<th>{t}Customer{/t}</th>
						<th>{t}Options{/t}</th>
					</tr>
				</thead>
				<tbody>
					{foreach $domains as $domain}
					<tr>
						<td>{$domain.name|escape:"htmlall"}</td>
						<td>{$domain.ip}:{$domain.port}</td>
						<td>{$domain.customer|escape:"htmlall"}</td>
						<td></td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			</form>

			{if (Froxlor::getUser()->getData("resources", "domains_used") < Froxlor::getUser()->getData("resources", "domains")
				|| Froxlor::getUser()->getData("resources", "domains") == '-1')
			}
				<div class="overviewadd">
					<img src="images/Froxlor/icons/domain_add.png" alt="" />&nbsp;
					<a href="{link area=admin section=domains action=add}">{t}Create domain{/t}</a>
					<br />
					<img src="images/Froxlor/icons/domain_add.png" alt="" />&nbsp;
					<a href="{link area=admin section=domains action=register}">{t}Register domain{/t}</a>
				</div>
			{/if}

		</section>
	</article>