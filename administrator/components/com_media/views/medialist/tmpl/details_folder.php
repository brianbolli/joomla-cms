<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$user = JFactory::getUser();

JHtml::_('bootstrap.tooltip');
?>
		<tr>
			<td class="center hidden-phone">
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>" />
			</td>
			<td class="imgTotal">
				<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;context=<?php echo $this->state->context; ?>&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe">
					<i class="icon-folder-2"></i></a>
			</td>
			<td class="description">
				<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo $this->_tmp_folder->name; ?></a>
			</td>
			<td>&#160;

			</td>
			<td>&#160;

			</td>
		<?php if ($user->authorise('core.delete', 'com_media')):?>
			<td>
				<?php if (!empty($this->_tmp_folder->properties)) : ?>
					<a
						href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;context=<?php echo $this->state->context; ?>&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>"
						class="btn btn-mini btn-default media-detail media-detail-form media-folder"
						data-properties='<?php echo $this->_tmp_folder->properties; ?>'
						target="folderframe">
						<i class="icon-edit icon-white"></i> <?php echo JText::_('JACTION_EDIT'); ?>
						</a>
				<?php endif; ?>
				&nbsp;
				<a class="delete-item btn btn-mini btn-danger" target="_top" href="index.php?option=com_media&amp;task=folder.delete&amp;tmpl=index&amp;context=<?php echo $this->state->context; ?>&amp;folder=<?php echo $this->state->folder; ?>&amp;<?php echo JSession::getFormToken(); ?>=1&amp;rm[]=<?php echo $this->_tmp_folder->name; ?>" rel="<?php echo $this->_tmp_folder->name; ?>' :: <?php echo $this->_tmp_folder->files + $this->_tmp_folder->folders; ?>"><i class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></i></a>
			</td>
		<?php endif;?>
		</tr>
