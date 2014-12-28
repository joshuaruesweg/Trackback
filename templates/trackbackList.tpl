{foreach from=$trackbacks item="trackback"}
	<li class="trackback{if $trackback->isBlocked} trackbackBlocked{/if} jsTrackback" data-trackback-id="{@$trackback->getObjectID()}" data-blocked="{$trackback->isBlocked}" data-has-ip-address="{if $trackback->ipAddress}1{else}0{/if}"{if $trackback->ipAddress && $__wcf->session->getPermission('admin.user.canViewIpAddress') && LOG_IP_ADDRESS} data-ip-address="{$trackback->getIP()}"{/if}>
		<div>
			<div class="trackbackContent">
				<div class="containerHeadline">
					<h3>
						{if $trackback->title}
							<a href="{$trackback->url}"{if TRACKBACK_ENABLE_NOFOLLOW} rel="nofollow"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{$trackback->title}</a>
						{else}
							<a href="{$trackback->url}"{if TRACKBACK_ENABLE_NOFOLLOW} rel="nofollow"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{$trackback->url}</a>
						{/if}

						{if $trackback->getHost()}<small>{$trackback->getHost()}</small>{/if}
					</h3>
				</div>

				{if $trackback->excerpt}<p class="trackbackExcerpt">{$trackback->excerpt}</p>{/if}

				<nav class="jsMobileNavigation buttonGroupNavigation">
					<ul class="trackbackOptions">
						{event name='trackbackOptions'}
					</ul>
				</nav>
			</div>
		</div>
	</li>
{/foreach}