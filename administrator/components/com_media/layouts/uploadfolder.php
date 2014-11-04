<?php
defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
$session	= JFactory::getSession();
$config = JComponentHelper::getParams('com_media');
?>
	<form action="index.php?option=com_media&amp;task=folder.create&amp;context=<?php echo $displayData['context']; ?>&amp;folder=<?php $displayData['folder']; ?>&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="uploadFolder" id="uploadFolder" class="form-horizontal" method="post">
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
			<div class="controls">
				<button type="submit" class="btn btn-primary" id="folder-form-submit"><i class="icon-folder-open"></i> <?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?></button>
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
