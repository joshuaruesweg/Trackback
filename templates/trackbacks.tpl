{foreach from=$trackbacks item="trackback"}
	{* TODO FOR MAX :) *}
	{if $trackback->isBlocked}::BLOCKED::{/if}{$trackback->url}
{/foreach}