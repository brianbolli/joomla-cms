<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user  = JFactory::getUser();
$input = JFactory::getApplication()->input;
?>
<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div class="span2">
		<div id="treeview">
			<div id="media-tree_tree" class="sidebar-nav">
				<?php echo $this->loadTemplate('folders'); ?>
			</div>
		</div>
	</div>
	<style>
		.overall-progress,
		.current-progress {
			width: 150px;
		}
	</style>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span10">
		<?php echo $this->loadTemplate('navigation'); ?>
		<?php if (($user->authorise('core.create', 'com_media')) and $this->require_ftp) : ?>
			<form action="index.php?option=com_media&amp;task=ftpValidate" name="ftpForm" id="ftpForm" method="post">
				<fieldset title="<?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?>">
					<legend><?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?></legend>
					<?php echo JText::_('COM_MEDIA_DESCFTP'); ?>
					<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
					<input type="text" id="username" name="username" size="70" value="" />

					<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
					<input type="password" id="password" name="password" size="70" value="" />
				</fieldset>
			</form>
		<?php endif; ?>

		<form action="index.php?option=com_media" name="adminForm" id="mediamanager-form" method="post" enctype="multipart/form-data" >
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="cb1" id="cb1" value="0" />
			<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->folder; ?>" />
			<input class="update-context" type="hidden" name="context" id="context" value="<?php echo $this->state->context; ?>" />
		</form>

		<?php if ($user->authorise('core.create', 'com_media')):?>
			<div id="collapseUpload" class="collapse">
				<form action="<?php echo JUri::base(); ?>index.php?option=com_media&amp;task=file.upload&amp;tmpl=component&amp;<?php echo $this->session->getName().'='.$this->session->getId(); ?>&amp;<?php echo JSession::getFormToken();?>=1&amp;format=html" id="uploadFile" class="form-horizontal" name="uploadFile" method="post" enctype="multipart/form-data"></form>
			</div>
			<div id="collapseFolder" class="collapse">
				<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="uploadFolder" id="uploadFolder" class="form-horizontal" method="post"></form>
			</div>
		<?php endif;?>

		<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="folderForm" id="folderForm" method="post">
			<div id="folderview">
				<div class="view">
					<iframe class="thumbnail" src="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;context=<?php echo $this->state->context; ?>&amp;folder=<?php echo $this->state->folder;?>" id="folderframe" name="folderframe" width="100%" marginwidth="0" marginheight="0" scrolling="auto" style="min-height: 500px;"></iframe>
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
	<!-- End Content -->
</div>
