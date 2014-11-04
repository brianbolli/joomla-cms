(function($){

	$(document).on('click', '.media-detail-form', function(e){
		e.preventDefault();

		if ($(this).hasClass('media-detail'))
		{
			$(this).parent('td').parent('tr').siblings().removeClass('success');
			$(this).parent('td').parent('tr').addClass('success');
		}
		else
		{
			$(this).parent('div').parent('li').siblings().removeClass('active');
			$(this).parent('div').parent('li').addClass('active');
		}

		var fileCollapseButton = $(window.parent.document.getElementById('toolbar-upload')).children('button');
		var folderCollapseButton = $(window.parent.document.getElementById('toolbar-new')).children('button');

		var file_collapse = window.parent.document.getElementById('collapseUpload');
		var folder_collapse = window.parent.document.getElementById('collapseFolder');

		var target_form, target_submit, task_old, task_new, button_text;
		if ($(this).hasClass('media-folder'))
		{
			target_form = 'uploadFolder';
			task_old = 'folder.create';
			task_new = 'folder.update';
			target_submit = 'folder-form-submit';
			button_text = ' Update Container';

			if ($(file_collapse).hasClass('in'))
			{
				$(fileCollapseButton).click();
			}

			if (!$(folder_collapse).hasClass('in'))
			{
				$(folderCollapseButton).click();
			}

			window.parent.document.getElementById('jform_foldername').setAttribute('readonly', 1);
		}
		else
		{
			target_form = 'uploadFile';
			task_old = 'file.upload';
			task_new = 'file.update';
			target_submit = 'file-form-submit';
			button_text = ' Update Blob';


			if (!$(file_collapse).hasClass('in'))
			{
				$(fileCollapseButton).click();
			}

			if ($(folder_collapse).hasClass('in'))
			{
				$(folderCollapseButton).click();
			}

			window.parent.document.getElementById('upload-file').setAttribute('disabled', 1);
		}

		var json = $(this).attr('data-properties');
		var properties = JSON.parse(json);

		for (var property in properties) {
			window.parent.document.getElementById('jform_' + property).value = properties[property];
		}

		var uploadform = window.parent.document.getElementById(target_form);
		var href = uploadform.getAttribute('action');
		href = href.replace(task_old, task_new);
		uploadform.setAttribute('action', href);

		window.parent.document.getElementById(target_submit).childNodes[1].nodeValue = button_text;
	});
})(jQuery);