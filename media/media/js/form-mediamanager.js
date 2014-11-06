(function($){

	var file_collapse;
	var folder_collapse;

	var file_form;
	var folder_form;

	var file_buttom;
	var folder_button;

	$(function() {
		file_collapse = window.parent.document.getElementById('collapseUpload');
		folder_collapse = window.parent.document.getElementById('collapseFolder');

		file_form = window.parent.document.getElementById('uploadFile');
		folder_form = window.parent.document.getElementById('uploadFolder');

		file_button = $(window.parent.document.getElementById('toolbar-upload')).children('button');
		folder_button = $(window.parent.document.getElementById('toolbar-new')).children('button');
	})

	function expandMediaForm(form, container, old_task, new_task, submit, text, properties) {
		if (!$(container).hasClass('in')) {
			$(container).collapse('show');
		}
		updateFormActionTask(form, old_task, new_task);
		updateFormButtonText(submit, text);

		for (var property in properties) {
			window.parent.document.getElementById('jform_' + property).value = properties[property];
		}
	}

	function shrinkMediaForm(form, button, old_task, new_task, submit, text) {
		$(button).click();
		updateFormActionTask(form, old_task, new_task);
		updateFormButtonText(submit, text);

	}

	function updateFormActionTask(form, old_task, new_task) {
		var href = form.getAttribute('action');
		href = href.replace(old_task, new_task);
		form.setAttribute('action', href);
	}

	function updateFormButtonText(target, text) {
		window.parent.document.getElementById(target).childNodes[1].nodeValue = text;
	}

	function clearFormFields(form) {
		console.log('clear form');
		$(form).find('input').val('');
		$(form).find('select').val('');
	}

	$(document).on('click', '.media-form', function(e){
		console.log('media form anchor click');
		e.preventDefault();

		var json = $(this).attr('data-properties');
		var properties = JSON.parse(json);

		if ($(this).hasClass('media-detail'))
		{
			if ($(this).parent('td').parent('tr').hasClass('success'))
			{
				if ($(this).hasClass('media-folder'))
				{
					shrinkMediaForm(folder_form, folder_button, 'folder.update', 'folder.create', 'folder-form-submit', ' Create Folder');
				}
				else
				{
					shrinkMediaForm(file_form, file_button, 'file.update', 'file.upload', 'file-form-submit', ' Upload File');
				}
				$(this).parent('td').parent('tr').removeClass('success');
				return true;
			}
			else
			{
				$(this).parent('td').parent('tr').siblings().removeClass('success');
				$(this).parent('td').parent('tr').addClass('success');
				if ($(this).hasClass('media-folder'))
				{
					expandMediaForm(folder_form, folder_collapse, 'folder.upload', 'folder.update', 'folder-form-submit', ' Update Folder', properties);
					window.parent.document.getElementById('jform_foldername').setAttribute('readonly', 1);
				}
				else
				{
					expandMediaForm(file_form, file_collapse, 'file.upload', 'file.update', 'file-form-submit', ' Update File', properties);
					window.parent.document.getElementById('upload-file').setAttribute('disabled', 1);
				}
			}
		}
		else
		{
			if ($(this).parent('div').parent('li').hasClass('active'))
			{
				if ($(this).hasClass('media-folder'))
				{
					shrinkMediaForm(folder_form, folder_button, 'folder.update', 'folder.create', 'folder-form-submit', ' Create Folder');
				}
				else
				{
					shrinkMediaForm(file_form, file_button, 'file.update', 'file.upload', 'file-form-submit', ' Upload File');
				}
				$(this).parent('div').parent('li').removeClass('active')
				return true;
			}
			else
			{
				$(this).parent('div').parent('li').siblings().removeClass('active');
				$(this).parent('div').parent('li').addClass('active');
				if ($(this).hasClass('media-folder'))
				{
					expandMediaForm(folder_form, folder_collapse, 'folder.upload', 'folder.update', 'folder-form-submit', ' Update Folder', properties);
					window.parent.document.getElementById('jform_foldername').setAttribute('readonly', 1);
				}
				else
				{
					expandMediaForm(file_form, file_collapse, 'file.upload', 'file.update', 'file-form-submit', ' Update File', properties);
					window.parent.document.getElementById('upload-file').setAttribute('disabled', 1);
				}
			}
		}

	});
})(jQuery);