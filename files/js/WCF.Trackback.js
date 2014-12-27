/**
 * @author	Joshua Rüsweg
 * @package	com.hg-202.trackback
 */

WCF.Trackback = { }; 

WCF.Trackback.Handler = Class.extend({
	_objectType: '', 
	_objectID: 0, 
	_itemsLoaded: 0, 
	_list: null,
	_lastSeenTime: 0, 
	_itemsCount: 0, 
	
	// permissions 
	_canDelete: false, 
	_canBlock: false, 
	_canViewIPAddresses: false,
	
	_proxy: null,
	
	_loadNextButton: null, 
	
	_dialog: null,
	
	/**
	 * Creates a new object of this class.
	 */
	init: function(objectType, objectID, itemsLoaded, lastSeenTime) {
		this._objectID = objectID; 
		this._objectType = objectType; 
		this._itemsLoaded = itemsLoaded; 
		this._lastSeenTime = lastSeenTime; 
		
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			success: $.proxy(this._success, this)
		});
		
		this._list = $('.trackbackList[data-object-id="'+ objectID +'"][data-object-type="'+ objectType +'"]'); 
		
		if (!this._list.length) {
			console.debug("[WCF.Trackback.Handler] unable to find list");
		}
		
		this._canBlock = this._list.data('canBlock');
		this._canDelete = this._list.data('canDelete');
		this._canViewIPAddresses = this._list.data('canViewIpAddresses');
		this._itemsCount = this._list.data('trackbacks');
		
		this._handleLoadNext(); 
		this._initTrackbacks();
	}, 
	
	/**
	 * init buttons
	 */
	_handleLoadNext: function() {
		if (this._itemsLoaded < this._itemsCount) {
			if (this._loadNextButton === null) {
				this._loadNextButton = $('<li class="trackbackLoadNext"><button class="small">' + WCF.Language.get('wcf.trackback.more') + '</button></li>').appendTo(this._list);
				this._loadNextButton.children('button').click($.proxy(this._loadItems, this));
			}
			
			this._loadNextButton.enable(); 
		} else if (this._loadNextButton !== null) {
			this._loadNextButton.hide(); 
		}
	}, 
	
	_initTrackbacks: function() {
		var self = this; 
		this._list.find('.jsTrackback').each(function(index, trackback) {
			var $trackback = $(trackback).removeClass('jsTrackback'); // prevent a second action
			
			if (self._canBlock) {
				if ($trackback.data('blocked')) {
					var $blockButton = $('<li><a href="#" class="jsTooltip" title="' + WCF.Language.get('wcf.trackback.unblock') + '"><span class="icon icon16 fa-undo" /> <span class="invisible">' + WCF.Language.get('wcf.trackback.unblock') + '</span></a></li>');
				} else {
					var $blockButton = $('<li><a href="#" class="jsTooltip" title="' + WCF.Language.get('wcf.trackback.block') + '"><span class="icon icon16 fa-ban" /> <span class="invisible">' + WCF.Language.get('wcf.trackback.block') + '</span></a></li>');
				}
				
				$blockButton.data('trackbackID', $trackback.data('trackbackID')).appendTo($trackback.find('ul.trackbackOptions:eq(0)')).click(function(event) { self._toogleBlock(event); });
			}
			
			if (self._canViewIPAddresses && $trackback.data('hasIpAddress')) {
				$('<li><a href="#" class="jsTooltip" title="IP Adresse"><span class="icon icon16 icon-globe" /> <span class="invisible">IP Adresse</span></a></li>').data('trackbackID', $trackback.data('trackbackID')).appendTo($trackback.find('ul.trackbackOptions:eq(0)')).click(function(event) { self._showIPAddress(event); });
			}
			
			if (self._canDelete) {
				$('<li><a href="#" class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.delete') + '"><span class="icon icon16 icon-remove" /> <span class="invisible">' + WCF.Language.get('wcf.global.button.delete') + '</span></a></li>').data('trackbackID', $trackback.data('trackbackID')).appendTo($trackback.find('ul.trackbackOptions:eq(0)')).click(function(event) { self._delete(event); });
			}
		});
	},
	
	/**
	 * load more trackback-items
	 */
	_loadItems: function() {
		this._loadNextButton.disable(); 
		
		this._proxy.setOption('data', {
			actionName: 'loadTrackbacks',
			className: 'wcf\\data\\trackback\\TrackbackAction',
			parameters: {
				objectType: this._objectType, 
				objectID: this._objectID, 
				lastSeenTime: this._lastSeenTime
			}
		});
		this._proxy.sendRequest();
	}, 
	
	_addItems: function(template, count, lastSeenTime) {
		if (count !== 0) {
			this._itemsLoaded += count; 
			this._lastSeenTime = lastSeenTime; 

			$(template).appendTo(this._list); 

			// activate buttons 
			this._initTrackbacks(); 

			WCF.System.Event.fireEvent('com.hg-202.trackback', 'addItems', {
				self: this
			});
		} else {
			this._itemsLoaded = this._itemsCount;
		}
		
		this._handleLoadNext(); 
	}, 
	
	_toogleBlock: function(event) {
		event.preventDefault();
		
		this._proxy.setOption('data', {
			actionName: 'toogleBlock',
			className: 'wcf\\data\\trackback\\TrackbackAction',
			objectIDs: [$(event.currentTarget).data('trackbackID')]
		});
		this._proxy.sendRequest();
	}, 
	
	_delete: function(event) {
		event.preventDefault();
		
		WCF.System.Confirmation.show(WCF.Language.get('wcf.trackback.delete.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._proxy.setOption('data', {
					actionName: 'remove',
					className: 'wcf\\data\\trackback\\TrackbackAction',
					objectIDs: [$(event.currentTarget).data('trackbackID')]
				});
				this._proxy.sendRequest();
			}
		}, this));
	}, 
	
	_showIPAddress: function(event) {
		event.preventDefault();
		
		var ip = $(event.currentTarget)
			.parent() // ul
			.parent() // nav
			.parent() // .trackbackContent
			.parent() // div
			.parent() // li
			.data('ipAddress');
		
		if (this._dialog === null) {
			this._dialog = $('<div id="trackbackIPAddressDialog" />').hide().appendTo(document.body);
		}
		
		this._dialog.html(ip);
		this._dialog.wcfDialog({
			title: 'IP-Adresse'
		});
		this._dialog.wcfDialog('render');
	},
	
	_success: function(data, jqXHR, textStatus) {
		if (data.actionName === 'remove') {
			data.returnValues.objectIDs.forEach(function (objectID) {
				$('li.trackback[data-trackback-id="'+ objectID +'"]').remove(); 
			});
		} else if (data.actionName === 'toogleBlock') {
			data.returnValues.blocked.forEach(function (objectID) {
				$('li.trackback[data-trackback-id="'+ objectID +'"]').addClass('trackbackBlocked').find('.fa-ban').removeClass('fa-ban').addClass('fa-undo'); 
			});
			
			data.returnValues.unblocked.forEach(function (objectID) {
				$('li.trackback[data-trackback-id="'+ objectID +'"]').removeClass('trackbackBlocked').find('.fa-undo').removeClass('fa-undo').addClass('fa-ban'); ; 
			});
		} else if (data.actionName === 'loadTrackbacks') {
			this._addItems(data.returnValues.template, data.returnValues.count, data.returnValues.lastSeenTime); 
		}
	}, 
	
	_failure: function(data, jqXHR, textStatus, errorThrown) {
		console.debug('request failed');
	}
});