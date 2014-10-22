<?php
defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
$session	= JFactory::getSession();
$config = JComponentHelper::getParams('com_media');
?>
<div id="collapseUpload" class="collapse">
	<form action="<?php echo JUri::base(); ?>index.php?option=com_media&amp;task=file.upload&amp;tmpl=component&amp;<?php echo $session->getName().'='.$session->getId(); ?>&amp;<?php echo JSession::getFormToken();?>=1&amp;format=html" id="uploadForm" class="form-inline" name="uploadForm" method="post" enctype="multipart/form-data">
		<div id="uploadform">
			<fieldset id="upload-noflash" class="actions">
					<label for="upload-file" class="control-label"><?php echo JText::_('COM_MEDIA_UPLOAD_FILE'); ?></label>
					<input type="file" id="upload-file" name="Filedata[]" multiple />
					<p class="help-block"><?php echo $config->get('upload_maxsize') == '0' ? JText::_('COM_MEDIA_UPLOAD_FILES_NOLIMIT') : JText::sprintf('COM_MEDIA_UPLOAD_FILES', $config->get('upload_maxsize')); ?></p>
			</fieldset>
			<?php foreach ($displayData['form']->getFieldsets() as $name => $fieldset) : ?>
				<fieldset>
					<?php if ($fieldset->label) : ?>
						<legend><?php echo $fieldset->label; ?></legend>
					<?php endif; ?>
					<?php if ($fieldset->description) : ?>
						<p><?php echo $fieldset->description; ?></p>
					<?php endif; ?>
					<?php foreach ($displayData['form']->getFieldset($name) as $field) : ?>
						<?php if (!$field->hidden) : ?>
							<?php echo $field->label; ?>
						<?php endif; ?>
							<?php echo $field->input; ?>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
			<fieldset>
				<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $displayData['folder']; ?>" />
				<input class="update-context" type="hidden" name="context" id="context" value="<?php echo $displayData['context']; ?>" />
				<button class="btn btn-primary" id="upload-submit"><i class="icon-upload icon-white"></i> <?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?></button>
				<?php JFactory::getSession()->set('com_media.return_url', 'index.php?option=com_media'); ?>
			</fieldset>
		</div>
	</form>
</div>
<div id="collapseFolder" class="collapse">
	<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="folderForm" id="folderForm" class="form-inline" method="post">
			<div class="path">
				<input type="text" id="folderpath" readonly="readonly" class="update-folder" />
				<input type="text" id="foldername" name="foldername" />
				<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $displayData['folder']; ?>" />
				<input class="update-context" type="hidden" name="contextbase" id="contextbase" value="<?php echo $displayData['context']; ?>" />
				<button type="submit" class="btn"><i class="icon-folder-open"></i> <?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?></button>
			</div>
			<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
