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
		this.uploadmedia	= $('#collapseUpload');

		this.updatepaths	= $('input.update-folder');
		this.updatecontexts = $('input.update-context');

		this.frame		= window.frames['folderframe'];
		this.frameurl	= this.frame.location.href;

		$('#collapseUpload').on('hidden', function () {
			$('#folderframe').contents().find('tr').removeClass('success');
			$('#folderframe').contents().find('li.imgOutline.thumbnail').removeClass('active');
			$('#upload-file').prop('disabled', 0);
			$('#uploadFile').find('input').val('');
			$('#uploadFile').find('select').val('');
			this.updateMediaFolderFormAction('#uploadFile', 'file.update', 'file.create');
		});

		$('#collapseFolder').on('hidden', function () {
			$('#folderframe').contents().find('tr').removeClass('success');
			$('#folderframe').contents().find('li.imgOutline.thumbnail').removeClass('active');
			$('#uploadFolder').find('input').val('');
			$('#uploadFolder').find('select').val('');
			this.updateMediaFolderFormAction('#uploadFolder', 'folder.update', 'folder.create');
		});
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

		this.updatecontexts.each(function(path, el){ el.value = context})
		this.updateMediaFileForm(context, folder);
		this.updateMediaFolderForm(context, folder);

		$('#' + viewstyle).addClass('active');

		var form_id = '#uploadForm';
		var action = $(form_id).attr('action');

		if (typeof action === "undefined")
		{
			form_id = '#folderForm';
			action = $(form_id).attr('action');
		}

		a = this._getUriObject(action);
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

	updateMediaFileForm: function(context, folder) {
		var url = 'index.php?option=com_media&task=file.form&context=' + context + '&folder=' + folder + '&format=json';
		this._processAjaxRequest(url, '#collapseUpload');
	},

	updateMediaFolderForm: function(context, folder) {
		var url = 'index.php?option=com_media&task=folder.form&context=' + context + '&folder=' + folder + '&format=json';
		this._processAjaxRequest(url, '#collapseFolder');
	},

	updateMediaFileFormAction: function(current, updated) {
		_updateMediaFormAction('#uploadFile', current, updated);
	},

	updateMediaFolderFormAction: function(current, updated) {
		_updateMediaFormAction('#uploadFolder', current, updated);
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

	initializeFolderTree: function()
	{

		this.mediatree = $('#media-tree_tree');

		var f = document.getElementById('folder').value;
		var c = document.getElementById('context').value;

		this.activeContext = c;

		if (typeof f !== 'undefined' && typeof c !== 'undefined') {
			this.mediatree.find('li').removeClass('active');
			if (f.length === 0) {
				$('li#' + c).addClass('active');
			} else {
				var activeId = f.replace('/','-');
				$('li#' + activeId).addClass('active');
			}
		}

		this.mediatree.find('li').each(function(){
			if ($(this).children('ul').length > 0) {
				$(this).addClass('children');
				$(this).children('i').removeClass('icon-folder-2').addClass('icon-folder-open');
			} else {
				$(this).addClass('childless');
			}
		});

		$('li.children').on('shown', function(){
			$(this).children('i').removeClass('icon-folder-2').addClass('icon-folder-open');
		});

		$('li.children').on('hidden', function(){
			$(this).children('i').removeClass('icon-folder-open').addClass('icon-folder-2')
		});
	},

	populateMediaForm: function(properties) {
		alert('populate media form');
	},

	_updateMediaFormAction: function(form, current, updated) {
		var action = $(form).attr('action');
		$(form).attr('action', str_replace(current, updated, action));
	},

	_processAjaxRequest: function(url, replace) {
		$.ajax({
			url: url,
			dataType: 'json',
			success: function(response) {
				if (!response.success && response.message) {
					alert(response.message);
				}

				if (response.messages) {
					Joomla.renderMessages(repsonse.messages);
				}

				$(replace).html(response.data);

			},
			error: function(x,y,z) {
				console.log(x);
				console.log(y);
				console.log(z);
			}
		})
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
	document.getElementById('folderframe').onload = function() { MediaManager.initializeFolderTree();	};

	// Added to populate data on iframe load
	MediaManager.initialize();
	MediaManager.trace = 'start';
	document.updateUploader = function() { MediaManager.onloadframe(); };
	//MediaManager.onloadframe();
});
