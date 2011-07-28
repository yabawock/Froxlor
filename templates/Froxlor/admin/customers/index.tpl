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

			<table class="bradiusodd tablesorter">
				<thead>
					<tr>
						<th>{t}Username{/t}</th>
						<th>{t}Name{/t}</th>
						<th>{t}Admin{/t}</th>
						<th>{t}Webspace{/t}</th>
						<th>{t}Traffic{/t}</th>
						<th>{t}Last login{/t}</th>
						<th>{t}Locked{/t}</th>
						<th>{t}Options{/t}</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td id="pager" class="pager" colspan="8">
							<form>
								{if $user->getData('resources', customers_used) < $user->getData('resources', 'customers') || $user->getData('resources', 'customers') == '-1'}<img src="images/Froxlor/icons/user_add.png" alt="" />&nbsp;<a href="{link area='admin' section=customers action=add}">{t}Create customer{/t}</a>{/if} |
								<img src="images/Froxlor/icons/first.png" class="first" />
								<img src="images/Froxlor/icons/prev.png" class="prev" />
								<input type="text" class="pagedisplay" />
								<img src="images/Froxlor/icons/next.png" class="next" />
								<img src="images/Froxlor/icons/last.png" class="last" />
								<select class="pagesize">
									<option selected="selected"  value="10">10</option>
									<option value="20">20</option>
									<option value="30">30</option>
									<option value="40">40</option>
								</select> |
								<input name="filter" id="filter-box" value="" maxlength="30" size="30" type="text"><input id="filter-clear-button" type="submit" value="Clear"/>
							</form>
						</td>
					</tr>
				</tfoot>
				<tbody>
					{foreach $customers as $customer}<tr>
						<td><a href="{link area='admin' section='customers' action='su' id=$customer.row.id}" rel="external">{$customer.row.loginname}</a></td>
						<td>
							{if $customer.handle.name != '' && $customer.handle.firstname != ''}
								{$customer.handle.firstname}&nbsp;{$customer.handle.name}
							{/if}
							{if ($customer.handle.name == '' || $customer.handle.firstname == '') &&  $customer.handle.organization != ''}
								{$customer.handle.organization}
							{/if}
						</td>
						<td>{$customer.admin.loginname}</td>
						<td>
								<span class="overviewcustomerextras">
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
						</td>
						<td>
								<span class="overviewcustomerextras">
									{if $customer.row.traffic != -1}
										<span class="progressBar" title="{$customer.row.traffic_used} / {$customer.row.traffic} GB">
											{if (($customer.row.traffic / 100) * $maxtraffic) < $customer.row.traffic_used}
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
						</td>
						<td>{$customer.last_login}</td>
						<td>{$customer.unlock_link}</td>
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

		</section>

	</article>
