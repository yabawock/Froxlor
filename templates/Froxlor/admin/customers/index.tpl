	<article>
		<header>
			<h2>
				<img src="images/Froxlor/icons/group_edit.png" alt="" />&nbsp;
				{t}Customers{/t}&nbsp;({$customercount})
			</h2>
		</header>

		<section>

			<form action="{link area='admin' section='customers' action='index'}" method="post" enctype="application/x-www-form-urlencoded">

			{if ($user->getData('resources', customers_used) < $user->getData('resources', 'customers') || $user->getData('resources', 'customers') == '-1') && $user->getData('resources', 'customers_used') > 15}
				<div class="overviewadd">
					<img src="images/Froxlor/icons/user_add.png" alt="" />&nbsp;
					<a href="{link area='admin' section=customers action=add}">{t}Create customer{/t}</a>
				</div>
			{/if}

			<table class="bradiusodd">
			<thead>
				<tr>
					<th>
						{t}Name{/t}&nbsp;&nbsp;
						{t}Firstname{/t}&nbsp;&nbsp;
						{t}Username{/t}&nbsp;
						{t}Admin{/t}
					</th>
					<th>{t}Options{/t}</th>
				</tr>
			</thead>
			<tbody>
				{foreach $customers as $customer}<tr>
					<td>
						<strong>
							{if $customer.handle.name != '' && $customer.handle.firstname != ''}
								{$customer.handle.firstname}&nbsp;{$customer.handle.name}
							{/if}
							{if ($customer.handle.name == '' || $customer.handle.firstname == '') &&  $customer.handle.organization != ''}
								{$customer.handle.organization}
							{/if}&nbsp;(<a href="{link area='admin' section='customers' action='su' id=$customer.row.id}" rel="external">{$customer.row.loginname}</a> | {$customer.admin.loginname})
						</strong>
						<div>
							<span class="overviewcustomerextras">
								{t}Webspace:{/t}&nbsp;
								{if $customer.row.diskspace != -1}
									<span class="progressBar" title="{$customer.row.diskspace_used} / {$customer.row.diskspace} MB">
										{if (($customer.row.diskspace / 100) * $maxdisk) < $customer.row.diskspace_used}
											<span class="redbar">
										{else}
											<span>
										{/if}
										<em style="left: {$customer.doublepercent}px;">{$customer.percent}%</em></span>
									</span>
								{else}
									<span class="progressBar" title="{t}Unlimited{/t}">
										<span class="greybar"><em style="left: 200px;">100%</em></span>
									</span>
								{/if}
							</span>
							<span class="overviewcustomerextras">
								{t}Traffic:{/t}&nbsp;
								{if $customer.row.traffic != -1}
									<span class="progressBar" title="{$customer.row.traffic_used} / {$customer.row.traffic} GB">
										{if (($customer.row.traffic / 100) * $trafficmax) < $customer.row.traffic_used}
											<span class="redbar">
										{else}
											<span>
										{/if}
										<em style="left: {$customer.doublepercent}px;">{$customer.percent}%</em></span>
									</span>
								{else}
									<span class="progressBar" title="{t}Unlimited{/t}">
										<span class="greybar"><em style="left: 200px;">100%</em></span>
									</span>
								{/if}
							</span>
							<span style="clear: both !important;">
								{$customer.last_login}{$customer.unlock_link}
							</span>
						</div>
					</td>
					<td>
						<a href="{link area='admin' section='customers' action='edit' id=$customer.row.id}" style="text-decoration:none;">
							<img src="images/Froxlor/icons/edit.png" alt="{t}Edit{/t}" />
						</a>&nbsp;
						<a href="{link area='admin' section='customers' action='delete' id=$customer.row.id}" style="text-decoration:none;">
							<img src="images/Froxlor/icons/delete.png" alt="{t}Delete{/t}" />
						</a>
					</td>
				</tr>{/foreach}
			</tbody>
			</table>

			<p style="display:none;">
				<input type="hidden" name="s" value="$s" />
				<input type="hidden" name="page" value="$page" />
			</p>

			</form>

			{if $user->getData('resources', customers_used) < $user->getData('resources', 'customers') || $user->getData('resources', 'customers') == '-1'}
			<div class="overviewadd">
				<img src="images/Froxlor/icons/user_add.png" alt="" />&nbsp;
				<a href="{link area='admin' section=customers action=add}">{t}Create customer{/t}</a>
			</div>
			{/if}

		</section>

	</article>
