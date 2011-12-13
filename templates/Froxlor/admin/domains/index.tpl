	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/domains.png" alt="" />&nbsp;
				{t}Domains{/t}&nbsp;({$domainscount})
			</h2>
		</header>

		<section>

			{if ((Froxlor::getUser()->getData("resources", "domains_used") < Froxlor::getUser()->getData("resources", "domains")
				|| Froxlor::getUser()->getData("resources", "domains") == '-1') && $domainscount > 15 && $countcustomers > 0)
			}
				<div class="overviewadd">
					<img src="images/Froxlor/icons/domain_add.png" alt="" />&nbsp;
					<a href="{link section=domains action=add}">{t}Create domain{/t}</a>
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

			{if $countcustomers == 0}
				<div class="warningcontainer bradius">
					<div class="warningtitle">{t}WARNING - Please note!{/t}</div>
					<div class="warning">
						<a href="{link section=customers action=add}">{t}It's not possible to add a domain currently. You first need to add at least one customer.{/t}</a>
					</div>
				</div>
			{/if}

			{if (Froxlor::getUser()->getData("resources", "domains_used") < Froxlor::getUser()->getData("resources", "domains")
				|| Froxlor::getUser()->getData("resources", "domains") == '-1') && $countcustomers != 0
			}
				<div class="overviewadd">
					<img src="images/Froxlor/icons/domain_add.png" alt="" />&nbsp;
					<a href="{link section=domains action=add}">{t}Create domain{/t}</a>
					<br />
					<img src="images/Froxlor/icons/domain_add.png" alt="" />&nbsp;
					<a href="{link section=domains action=register}">{t}Register domain{/t}</a>
				</div>
			{/if}

		</section>
	</article>