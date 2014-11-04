<?php
defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
$session	= JFactory::getSession();
$config = JComponentHelper::getParams('com_media');
?>
		<form action="<?php echo JUri::base(); ?>index.php?option=com_media&amp;task=file.upload&amp;tmpl=component&amp;<?php echo $session->getName().'='.$session->getId(); ?>&amp;<?php echo JSession::getFormToken();?>=1&amp;format=html" id="uploadFile" class="form-horizontal" name="uploadFile" method="post" enctype="multipart/form-data">
			<div id="uploadform">
				<div id="upload-noflash" class="control-group">
					<legend><?php echo JText::_('COM_MEDIA_UPLOAD_FILE') ?></legend>
					<label for="upload-file" class="control-label"><?php echo JText::_('COM_MEDIA_UPLOAD_FILE'); ?></label>
					<div class="controls">
						<input type="file" id="upload-file" name="Filedata[]" multiple />
						<p class="help-block"><?php echo $config->get('upload_maxsize') == '0' ? JText::_('COM_MEDIA_UPLOAD_FILES_NOLIMIT') : JText::sprintf('COM_MEDIA_UPLOAD_FILES', $config->get('upload_maxsize')); ?></p>
					</div>
				</div>
				<?php foreach ($displayData['form']->getFieldsets() as $name => $fieldset) : ?>
						<?php if ($fieldset->label) : ?>
							<legend><?php echo JText::_($fieldset->label); ?></legend>
						<?php endif; ?>
						<?php if ($fieldset->description) : ?>
							<p><?php echo $fieldset->description; ?></p>
						<?php endif; ?>
						<?php foreach ($displayData['form']->getFieldset($name) as $field) : ?>
							<?php if ($field->hidden) : ?>
								<?php echo $field->input; ?>
							<?php endif; ?>
								<div class="control-group">
									<?php echo $field->label; ?>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
						<?php endforeach; ?>
				<?php endforeach; ?>
				<div class="control-group">
					<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $displayData['folder']; ?>" />
					<input class="update-context" type="hidden" name="context" id="context" value="<?php echo $displayData['context']; ?>" />
					<div class="controls">
						<button type="submit" class="btn btn-primary" id="file-form-submit"><i class="icon-upload icon-white"></i> <?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?></button>
					</div>
					<?php JFactory::getSession()->set('com_media.return_url', 'index.php?option=com_media'); ?>
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
