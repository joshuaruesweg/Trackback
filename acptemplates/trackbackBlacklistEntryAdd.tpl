{include file='header' pageTitle='wcf.trackback.blacklist.item.add'}

<header class="boxHeadline">
	<h1>{lang}wcf.trackback.blacklist.item.add{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.add{/lang}</p>	
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='TrackbackBlacklistEntryAdd'}{/link}">
        <div id="general" class="container containerPadding marginTop">
                <fieldset>
                        <legend>{lang}wcf.trackback.blacklist.general{/lang}</legend>

                        <dl>
                                <dt><label for="host">{lang}wcf.trackback.blacklist.host{/lang}</label></dt>
                                <dd>
                                        <input type="text" id="host" name="host" value="{$host}" class="long" />
                                         {if $errorField == 'host'}
                                                <small class="innerError">
                                                        {if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
                                                </small>
                                        {/if}
                                </dd>
                        </dl>
                        {event name='generalFields'}
                </fieldset>
	</div>
		
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}