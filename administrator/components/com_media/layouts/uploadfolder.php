<?php
defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
$session	= JFactory::getSession();
$config = JComponentHelper::getParams('com_media');
?>
	<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="folderForm" id="folderForm" class="form-horizontal" method="post">
		<?php foreach ($displayData['form']->getFieldsets() as $name => $fieldset) : ?>
			<fieldset class="form-horizontal">
				<?php if ($fieldset->label) : ?>
					<legend><?php echo JText::_($fieldset->label); ?></legend>
				<?php endif; ?>
				<?php foreach ($displayData['form']->getFieldset($name) as $fields) : ?>
					<?php if ($fields->hidden) : ?>
						<?php echo $fields->input; ?>
					<?php else : ?>
						<div class="control-group">
							<?php echo $fields->label; ?>
							<div class="controls">
								<?php echo $fields->input; ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>
		<div class="control-group">
			<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $displayData['folder']; ?>" />
			<input class="update-context" type="hidden" name="contextbase" id="contextbase" value="<?php echo $displayData['context']; ?>" />
			<div class="controls">
				<button type="submit" class="btn"><i class="icon-folder-open"></i> <?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?></button>
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
