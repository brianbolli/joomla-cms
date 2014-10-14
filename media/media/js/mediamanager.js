/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JMediaManager behavior for media component
 *
 * @package		Joomla.Extensions
 * @subpackage  Media
 * @since		1.5
 */
(function($) {
var MediaManager = this.MediaManager = {

	initialize: function()
	{
		this.folderframe	= $('#folderframe');
		this.folderpath		= $('#folderpath');
		this.mediacontext	= $('#context');

		this.updatepaths	= $('input.update-folder');
		this.updatecontexts = $('input.update-context');

		this.frame		= window.frames['folderframe'];
		this.frameurl	= this.frame.location.href;
	},

	submit: function(task)
	{
		form = window.frames['folderframe'].document.getElementById('mediamanager-form');
		form.task.value = task;
		if ($('#username').length) {
			form.username.value = $('#username').val();
			form.password.value = $('#password').val();
		}
		form.submit();
	},

	onloadframe: function()
	{
		// Update the frame url
		this.frameurl = this.frame.location.href;

		var context = this.getContext();
		var folder = this.getFolder();
		if (folder) {
			this.updatepaths.each(function(path, el){ el.value =folder; });
			this.folderpath.value = basepath+'/'+folder;
		} else {
			this.updatepaths.each(function(path, el){ el.value = ''; });
			this.folderpath.value = basepath;
		}

		console.log(context);
		if (context) {
			this.updatecontexts.each(function(path, el){ el.value = context})
		}

		$('#' + viewstyle).addClass('active');

		a = this._getUriObject($('#uploadForm').attr('action'));
		q = this._getQueryObject(a.query);
		q['folder'] = folder;
		q['context'] = context;
		var query = [];

				for (var k in q) {
						var v = q[k];
						if (q.hasOwnProperty(k) && v !== null) {
								query.push(k+'='+v);
						}
				}

		a.query = query.join('&');

		if (a.port) {
			$('#uploadForm').attr('action', a.scheme+'://'+a.domain+':'+a.port+a.path+'?'+a.query);
		} else {
			$('#uploadForm').attr('action', a.scheme+'://'+a.domain+a.path+'?'+a.query);
		}
	},

	oncreatefolder: function()
	{
		if ($('#foldername').val().length) {
			$('#dirpath').val() = this.getFolder();
			Joomla.submitbutton('createfolder');
		}
	},

	setViewType: function(type)
	{
		$('#' + type).addClass('active');
		$('#' + viewstyle).removeClass('active');
		viewstyle = type;
		var folder = this.getFolder();
		var context = this.getContext();
		this._setFrameUrl('index.php?option=com_media&view=mediaList&tmpl=component&context='+context+'&folder='+folder+'&layout='+type);
	},

	refreshFrame: function()
	{
		this._setFrameUrl();
	},

	getFolder: function()
	{
		var url	 = this.frame.location.search.substring(1);
		var args	= this.parseQuery(url);

		if (args['folder'] == "undefined") {
			args['folder'] = "";
		}

		return args['folder'];
	},

	getContext: function()
	{
		var url = this.frame.location.search.substring(1);
		var args = this.parseQuery(url);

		if (typeof args['context'] === "undefined") {
			args['context'] = 'joomla';
		}

		return args['context'];
	},

	parseQuery: function(query)
	{
		var params = new Object();
		if (!query) {
			return params;
		}
		var pairs = query.split(/[;&]/);
		for ( var i = 0; i < pairs.length; i++ )
		{
			var KeyVal = pairs[i].split('=');
			if ( ! KeyVal || KeyVal.length != 2 ) {
				continue;
			}
			var key = unescape( KeyVal[0] );
			var val = unescape( KeyVal[1] ).replace(/\+ /g, ' ');
			params[key] = val;
		}
		return params;
	},

	_setFrameUrl: function(url)
	{
		if (url != null) {
			this.frameurl = url;
		}
		this.frame.location.href = this.frameurl;
	},

	_getQueryObject: function(q) {
		var vars = q.split(/[&;]/);
		var rs = {};
		if (vars.length) vars.forEach(function(val) {
			var keys = val.split('=');
			if (keys.length && keys.length == 2) rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
		});
		return rs;
	},

	_getUriObject: function(u){
		var bitsAssociate = {}, bits = u.match(/^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/);
		['uri', 'scheme', 'authority', 'domain', 'port', 'path', 'directory', 'file', 'query', 'fragment'].forEach(function(key, index){
				bitsAssociate[key] = bits[index];
		});

		return (bits)
			? bitsAssociate
			: null;
	}
};
})(jQuery);

jQuery(function(){
	// Added to populate data on iframe load
	MediaManager.initialize();
	MediaManager.trace = 'start';
	document.updateUploader = function() { MediaManager.onloadframe(); };
	MediaManager.onloadframe();
});
