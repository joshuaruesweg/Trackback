<ul class="trackbackList containerList" data-object-type="{$objectType}" data-object-id="{$objectID}" data-can-block="{$__wcf->getSession()->getPermission('mod.general.trackback.canBlock')}" data-can-delete="{$__wcf->getSession()->getPermission('mod.general.trackback.canDelete')}" data-can-view-ip-addresses="{if $__wcf->session->getPermission('admin.user.canViewIpAddress') && LOG_IP_ADDRESS}1{else}0{/if}" data-trackbacks="{@$trackbackCount}">
	{if $trackbacks|count}
		{include file='trackbackList'}
	{else}
		<span class='info'>{lang}wcf.trackback.noTrackbacks{/lang}</span>
	{/if}
</ul>
<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Trackback{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Trackback.Handler('{$objectType}', {$objectID}, {$trackbacks|count}, {$trackbackLastSeenTime}); 
	});
	//]]>
</script>