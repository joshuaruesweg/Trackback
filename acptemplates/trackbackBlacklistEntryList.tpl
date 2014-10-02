{include file='header' pageTitle='wcf.trackback.blacklist.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.trackback.blacklist.list{/lang}</h1>

	<script data-relocate="true" type="text/javascript">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\trackback\\blacklist\\entry\\TrackbackBlacklistEntryAction', '.jsJCPRow');
		});
		//]]>
	</script>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="JCoinsPremiumList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
			<ul>
				<li>
					<a href="{link controller='TrackbackBlacklistEntryAdd'}{/link}" title="" class="button">
						<span class="icon icon16 icon-plus"></span> 
						<span>{lang}wcf.trackback.blacklist.add{/lang}</span>
					</a>
				</li>
			</ul>
			{event name='additonalNavigationLinks'}
	</nav>
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.trackback.blacklist.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>

		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnEntryID{if $sortField == 'entryID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TrackbackBlacklistEntryList'}pageNo={@$pageNo}&sortField=entryID&sortOrder={if $sortField == 'entryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnHost{if $sortField == 'host'} active {@$sortOrder}{/if}"><a href="{link controller='TrackbackBlacklistEntryList'}pageNo={@$pageNo}&sortField=host&sortOrder={if $sortField == 'host' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.trackback.blacklist.host{/lang}</a></th>

					{event name='headColumns'}
				</tr>
			</thead>

			<tbody>
				{content}
					{foreach from=$objects item=entry}
						<tr class="jsJCPRow">
							<td class="columnIcon">
								<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" data-object-id="{@$entry->entryID}" title="{lang}wcf.global.button.delete{/lang}"></span>
								{event name='buttons'}
							</td>
							<td class="columnEntryID">
								<p>{$entry->entryID}</p>
							</td>
							<td class="columnHost"><p>{$entry->host}</p></td>

							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>

	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

<div class="contentNavigation">
	{@$pagesLinks}

	<nav>
		<ul>
			<li>
				<a href="{link controller='TrackbackBlacklistEntryAdd'}{/link}" title="" class="button">
					<span class="icon icon16 icon-plus"></span> 
					<span>{lang}wcf.trackback.blacklist.add{/lang}</span>
				</a>
			</li>
		</ul>
	</nav>
</div>

{include file='footer'}
