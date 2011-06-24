	<article>

	{if $awaitingtickets > 0 && $settings.ticket.enabled == 1}
	<div class="messagewrapperfull">
		<div class="warningcontainer bradius">
			<div class="warningtitle">{t}WARNING - Please note!{/t}</div>
			<div class="warning"><br /><strong>{$awaitingtickets_text}</strong></div>
		</div>
	</div>
	{/if}

<div style="width:100%; overflow:hidden;"></div>

	<section class="dboarditem bradiusodd">
		<h2>{t}Used resources (Used by customers (assigned/available)){/t}</h2>
		<table>
		<tr>
			<td>{t}Customers{/t}</td>
			<td>{$overview.number_customers} ({$user->getData('resources', 'customers')})</td>
		</tr>
		<tr>
			<td>{t}Domain{/t}</td>
			<td>{$overview.number_domains} ({$user->getData('resources', 'domains')})</td>
		</tr>
		<tr>
			<td>{t}Subdomains{/t}</td>
			<td>{$overview.subdomains_used} ({$user->getData('resources', 'subdomains_used')}/{$user->getData('resources', 'subdomains')})</td>
		</tr>
		<tr>
			<td>{t}Webspace{/t}</td>
			<td>{$overview.diskspace_used}{t}MB{/t} ({$userinfo.diskspace_used}{t}MB{/t}/{$userinfo.diskspace}{t}MB{/t})</td>
		</tr>
		<tr>
			<td>{t}Traffic{/t}</td>
			<td>{$overview.traffic_used}{t}GB{/t} ({$userinfo.traffic_used}{t}GB{/t}/{$userinfo.traffic}{t}GB{/t})</td>
		</tr>
		<tr>
			<td>{t}MySQL-databases{/t}</td>
			<td>{$overview.mysqls_used} ({$user->getData('resources', 'mysqls_used')}/{$user->getData('resources', 'mysqls')})</td>
		</tr>
		<tr>
			<td>{t}E-mail - addresses{/t}</td>
			<td>{$overview.emails_used} ({$user->getData('resources', 'emails_used')}/{$user->getData('resources', 'emails')})</td>
		</tr>
		<tr>
			<td>{t}E-Mail - accounts{/t}</td>
			<td>{$overview.email_accounts_used} ({$user->getData('resources', 'email_accounts_used')}/{$user->getData('resources', 'email_accounts')})</td>
		</tr>
		<tr>
			<td>{t}E-mail - forwarders{/t}</td>
			<td>{$overview.email_forwarders_used} ({$user->getData('resources', 'email_forwarders_used')}/{$user->getData('resources', 'email_forwarders')})</td>
		</tr>
		{if $settings.system.mail_quota_enabled == 1}
		<tr>
			<td>{t}E-mail quota{/t}</td>
			<td>{$overview.email_quota_used} ({$user->getData('resources', 'email_quota_used')}/{$user->getData('resources', 'email_quota')})</td>
		</tr>
		{/if}
		{if $settings.autoresponder.autoresponder_active == 1}
		<tr>
			<td>{t}E-mail - autoresponder{/t}</td>
			<td>{$user->getData('resources', 'email_autoresponder_used')} ({$user->getData('resources', 'email_autoresponder')})</td>
		</tr>
		{/if}
		{if (int)$settings.aps.aps_active == 1}
		<tr>
			<td>{t}APS - installations{/t}</td>
			<td>{$overview.aps_packages_used} ({$user->getData('resources', 'aps_packages_used')}/{$user->getData('resources', 'aps_packages')})</td>
		</tr>
		{/if}
		<tr>
			<td>{t}FTP - accounts{/t}</td>
			<td>{$overview.ftps_used} ({$user->getData('resources', 'ftps_used')}/{$user->getData('resources', 'ftps')})</td>
		</tr>
		{if $settings.ticket.enabled == 1}
		<tr>
			<td>{t}Support - tickets{/t}</td>
			<td>{$overview.tickets_used} ({$user->getData('resources', 'tickets_used')}/{$user->getData('resources', 'tickets')})</td>
		</tr>
		{/if}
		<tr>
			<td colspan="2" style="border:0;height:5px;"></td>
		</tr>
		</table>
	</section>
	<section class="dboarditem bradiusodd">
		<h2>{t}System details{/t}</h2>
		<table>
		<tr>
			<td>{t}Serversoftware{/t}</td>
			<td>{$smarty.server.SERVER_SOFTWARE}</td>
		</tr>
		<tr>
			<td>{t}PHP - version{/t}</td>
			<td>{$phpversion}</td>
		</tr>
		<tr>
			<td>{t}PHP - memorylimit{/t}</td>
			<td>{$phpmemorylimit}</td>
		</tr>
		<tr>
			<td>{t}MySQL - server version{/t}</td>
			<td>{$mysqlserverversion}</td>
		</tr>
		<tr>
			<td>{t}MySQL - client version{/t}</td>
			<td>{$mysqlclientversion}</td>
		</tr>
		<tr>
			<td>{t}MySQL - webserver interface{/t}</td>
			<td>{$webserverinterface}</td>
		</tr>
		<tr>
			<td>{t}System load{/t}</td>
			<td>{$load}</td>
		</tr>
		{if $showkernel == 1}
			<tr>
				<td>{t}Kernel:{/t}</td>
				<td>{$kernel}</td>
			</tr>
		{/if}
		{if $uptime != ''}
		<tr>
			<td>{t}Uptime:{/t}</td>
			<td>{$uptime}</td>
		</tr>
		<tr>
			<td colspan="2" style="border:0;height:5px;"></td>
		</tr>
		{/if}
		</table>
	</section>
	<section class="dboarditemfull bradiusodd">
		<h2>{t}Froxlor details{/t}</h2>
		<table>
		<tr>
			<td>{t}Outstanding tasks:{/t}</td>
			<td><ul>{foreach $outstanding_tasks as $key=>$value}
			<li>{$value}</li>
			{foreachelse}
			<li>{t}There are currently no outstanding tasks for Froxlor{/t}</li>
			{/foreach}</ul>
			</td>
		</tr>
		<tr>
			<td>{t}Last execution of cronjobs{/t}</td>
			<td>
				<ul>{foreach $cron_last_runs as $key=>$value}
					<li>{$value.text}: {$value.lastrun|date_format:"%Y-%m-%d %H:%M:%S"}</li>
				{/foreach}</ul>
		</tr>
		<tr>
			<td>{t}Installed version{/t}:</td>
			<td>{$version}{$branding}</td>
		</tr>
		<tr>
			<td>{t}Latest version:{/t}</td>
			{if $isnewerversion != 0 }
				<td><a href="{$lookfornewversion_link}"><strong>{$lookfornewversion_lable}</strong></a></td>
			{else}
				<td><a href="{$lookfornewversion_link}">{$lookfornewversion_lable}</a></td>
			{/if}
		</tr>
		{if $isnewerversion != 0}
		<tr>
			<td colspan="2"><strong>{t}There is a newer version of Froxlor available{/t}</strong></td>
		</tr>
			{if $lookfornewversion_addinfo != ''}
			<tr>
				<td colspan="2">{$lookfornewversion_addinfo}</td>
			</tr>
			{/if}
		{/if}
		<tr>
			<td colspan="2" style="border:0;height:5px;"></td>
		</tr>
		</table>
	</section>

	</article>
