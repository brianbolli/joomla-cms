(function($){

	var file_collapse;
	var folder_collapse;

	$(function() {
		file_collapse = window.parent.document.getElementById('collapseUpload');
		folder_collapse = window.parent.document.getElementById('collapseFolder');
	})

	function expandMediaForm(form_id, detail) {

	}

	function shrinkMediaForm(form_id, detail) {

	}

	$(document).on('click', '.media-form', function(e){
		e.preventDefault();

		if ($(this).hasClass('media-detail'))
		{
			if ($(this).parent('td').parent('tr').hasClass('success'))
			{
				if ($(this).hasClass('media-folder'))
				{
					$(folder_collapse).collapse('hide');
				}
				else
				{
					$(file_collapse).collapse('hide');
				}
				return true;
			}
			else
			{
				$(this).parent('td').parent('tr').siblings().removeClass('success');
				$(this).parent('td').parent('tr').addClass('success');
			}
		}
		else
		{
			if ($(this).parent('div').parent('li').hasClass('active'))
			{
				if ($(this).hasClass('media-folder'))
				{
					$(folder_collapse).collapse('hide');
				}
				else
				{
					$(file_collapse).collapse('hide');
				}
				return true;
			}
			else
			{
				$(this).parent('div').parent('li').siblings().removeClass('active');
				$(this).parent('div').parent('li').addClass('active');
			}
		}


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
				$(file_collapse).collapse('hide');
			}

			if (!$(folder_collapse).hasClass('in'))
			{
				$(folder_collapse).collapse('hide');
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
				$(file_collapse).collapse('hide');
			}

			if ($(folder_collapse).hasClass('in'))
			{
				$(folder_collapse).collapse('hide');
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