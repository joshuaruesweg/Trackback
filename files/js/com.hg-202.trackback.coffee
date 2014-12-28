###
# @author	Joshua Rüsweg
# @package	com.hg-202.trackback
###
 
(($, window) ->
	"use strict";
	
	console =
		log: (message) ->
			window.console.log "[com.hg-202.Trackback] #{message}" unless production?
		warn: (message) ->
			window.console.warn "[com.hg-202.Trackback] #{message}" unless production?
		error: (message) ->
			window.console.error "[com.hg-202.Trackback] #{message}" unless production?
 
	Trackback = Class.extend
		_objectType: ''
		_objectID: 0
		_itemsLoaded: 0
		_list: null
		_lastSeenTime: 0
		_itemsCount: 0
 
		_canDelete: no
		_canBlock: no
		_canViewIPAddresses: no
 
		_proxy: null
 
		_loadNextButton: null
 
		_dialog: null
 
		init: (objectType, objectID, itemsLoaded, lastSeenTime) ->
			@_objectType = objectType
			@_objectID = objectID
			@_itemsLoaded = itemsLoaded
			@_lastSeenTIme = lastSeenTime
 
			@_proxy = new WCF.Action.Proxy
				failure: @_failure.bind @
				success: @_success.bind @
 
			@_list = $ """.trackbackList[data-object-id="#{objectID}"][data-object-type="#{objectType}"]"""
 
			unless @_list.length
				console.debug "unable to find list"
 
			@_canBlock = @_list.data 'canBlock'
			@_canDelete = @_list.data 'canDelete'
			@_canViewIPAddresses = @_list.data 'canViewIpAddresses'
			@_itemsCount = @_list.data 'trackbacks'
 
			do @_handleLoadNext
			do @_initTrackbacks
 
		_handleLoadNext: ->
			if @_itemsLoaded < @_itemsCount
				unless @_loadNextButton?
					@_loadNextButton = $ '<li class="trackbackLoadNext"><button class="small">' + WCF.Language.get('wcf.trackback.more') + '</button></li>'
					@_loadNextButton.appendTo @_list
 
					@_loadNextButton.children('button').on 'click', @_loadItems.bind @
 
				do @_loadNextButton.enable
			else if @_loadNextButton
				do @_loadNextButton.hide
 
		_initTrackbacks: ->
			self = @
			@_list.find('.jsTrackback').each (index, trackback) =>
				trackback = $ trackback
				trackback.removeClass 'jsTrackback'
 
				if @_canBlock
					if trackback.data 'blocked'
						blockButton = $ "<li><a href='#' class='jsTooltip' title='#{WCF.Language.get "wcf.trackback.unblock"}'><span class='icon icon16 fa-undo' /> <span class='invisible'>#{WCF.Language.get "wcf.trackback.unblock"}</span></a></li>"
					else
						blockButton = $ "<li><a href='#' class='jsTooltip' title='#{WCF.Language.get "wcf.trackback.block"}'><span class='icon icon16 fa-ban' /> <span class='invisible'>#{WCF.Language.get "wcf.trackback.block"}</span></a></li>"
					blockButton.data "trackbackID", trackback.data "trackbackID"
					blockButton.appendTo trackback.find "ul.trackbackOptions:eq(0)"
					blockButton.click (event) ->
						self._toogleBlock event
					
				if @_canViewIPAddresses and trackback.data 'hasIpAddress'
					ipButton = $ "<li><a href='#' class='jsTooltip' title='#{WCF.Language.get "wcf.trackback.ipaddress"}'><span class='icon icon16 icon-globe' /> <span class='invisible'>#{WCF.Language.get "wcf.trackback.ipaddress"}</span></a></li>"
					ipButton.data "trackbackID", trackback.data "trackbackID"
					ipButton.appendTo trackback.find "ul.trackbackOptions:eq(0)"
					ipButton.click (event) ->
						self._showIPAddress event
						
				if @_canDelete
					deleteButton = $ "<li><a href='#' class='jsTooltip' title='#{WCF.Language.get "wcf.global.button.delete"}'><span class='icon icon16 icon-remove' /> <span class='invisible'>#{WCF.Language.get "wcf.global.button.delete"}</span></a></li>"
					deleteButton.data "trackbackID", trackback.data "trackbackID"
					deleteButton.appendTo trackback.find "ul.trackbackOptions:eq(0)"
					deleteButton.click (event) ->
						self._delete event
		_loadItems: ->
			do @_loadNextButton.disable
 
			@_proxy.setOption 'data',
				actionName: 'loadTrackbacks'
				className: 'wcf\\data\\trackback\\TrackbackAction'
				parameters:
					objectType: @_objectType
					objectID: @_objectID
					lastSeenTime: @_lastSeenTime
			do @_proxy.sendRequest
 
		_addItems: (template, count, lastSeenTime) ->
			if count
				@_itemsLoaded += count
				@_lastSeenTime = lastSeenTime
 
				$(template).appendTo @_list
 
				do @_initTrackbacks
 
				WCF.System.Event.fireEvent 'com-hg-202.trackback', 'addItems',
					self: @
			else
				@_itemsLoaded = @_itemsCount
 
			do @_handleLoadNext
 
		_toogleBlock: (event) -> 
			do event.preventDefault
			
			@_proxy.setOption 'data',
				actionName: 'toogleBlock' 
				className: 'wcf\\data\\trackback\\TrackbackAction'
				objectIDs: [$(event.currentTarget).data 'trackbackID']
			do @_proxy.sendRequest
			
		_delete: (event) -> 
			do event.preventDefault

			WCF.System.Confirmation.show (WCF.Language.get('wcf.trackback.delete.confirmMessage')), $.proxy (action) =>
				if action 'confirm'
					@_proxy.setOption 'data',
						actionName: 'remove'
						className: 'wcf\\data\\trackback\\TrackbackAction'
						objectIDs: [$(event.currentTarget).data 'trackbackID']
					do @_proxy.sendRequest
			
		_showIPAddress: (event) -> 
			do event.preventDefault
			
			ip = $(event.currentTarget).parent('.trackback').data 'ipAddress'
			
			if @_dialog?
				@_dialog = $ '<div id="trackbackIPAddressDialog" />'
				do @_dialog.hide
				@_dialog.appendTo document.body
			
			@_dialog.html ip
			@_dialog.wcfDialog title: 
				WCF.Language.get 'wcf.trackback.ipaddress'
			@_dialog.wcfDialog 'render'
			
		_success: (data, jqXHR, textStatus) -> 
			switch data.actionName
				when 'remove' 
					for objectID in data.returnValues.objectIDs
						do $("li.trackback[data-trackback-id='#{objectID}']").remove
				when 'toogleBlock'
					for objectID in data.returnValues.blocked
						$("li.trackback[data-trackback-id='#{objectID}']").find(".fa-ban").removeClass("fa-ban").addClass "fa-undo"
						$("li.trackback[data-trackback-id='#{objectID}']").addClass "trackbackBlocked"
						
					for objectID in data.returnValues.unblocked
						$("li.trackback[data-trackback-id='#{objectID}']").find(".fa-undo").removeClass("fa-undo").addClass "fa-ban"
						$("li.trackback[data-trackback-id='#{objectID}']").removeClass "trackbackBlocked"
						
				when 'loadTrackback'
					@_addItems data.returnValues.template, data.returnValues.count, data.returnValues.lastSeenTime
		
		_failure: (data, jqXHR, textStatus, errorThrown) -> 
			console.error 'request failed'
			
	window.com ?= {}
	com.hg202 ?= {}
	com.hg202.Trackback = Trackback	
)(jQuery, @)