<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('bootstrap.framework');

$user = JFactory::getUser();
$params = new Registry;
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
		<tr>
			<td class="center hidden-phone">
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_img->name; ?>" />
			</td>
			<td>
				<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>"><?php echo JHtml::_('image', ($this->_tmp_img->path_relative) ? COM_MEDIA_BASEURL .'/'.$this->_tmp_img->path_relative : $this->_tmp_img->path_absolute, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_16, 'height' => $this->_tmp_img->height_16)); ?></a>
			</td>
			<td class="description">
				<a href="<?php echo  COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" rel="preview"><?php echo $this->escape($this->_tmp_img->title); ?></a>
			</td>
			<td class="dimensions">
				<?php echo JText::sprintf('COM_MEDIA_IMAGE_DIMENSIONS', $this->_tmp_img->width, $this->_tmp_img->height); ?>
			</td>
			<td class="filesize">
				<?php echo JHtml::_('number.bytes', $this->_tmp_img->size); ?>
			</td>
		<?php if ($user->authorise('core.delete', 'com_media')):?>
			<td>
				<?php if (!empty($this->_tmp_img->properties)) : ?>
					<a
						href=""
						class="img-edit btn btn-mini btn-default media-detail media-detail-form"
						title="<?php echo $this->_tmp_img->name; ?>"
						data-properties='<?php echo $this->_tmp_img->properties; ?>'
						rel="preview">
						<i class="icon-edit icon-white"></i> <?php echo JText::_('JACTION_EDIT'); ?>
					</a>
				<?php endif; ?>
				&nbsp;
				<a class="delete-item btn btn-mini btn-danger" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;context=<?php echo $this->state->context; ?>&amp;<?php echo JSession::getFormToken(); ?>=1&amp;context=<?php echo $this->state->context; ?>&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo ($this->_tmp_img->path_relative) ? $this->_tmp_img->name : urlencode($this->_tmp_img->path_absolute); ?>" rel="<?php echo $this->_tmp_img->name; ?>" rel="<?php echo $this->_tmp_img->name; ?>"><i class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></i></a>
			</td>
		<?php endif;?>
		</tr>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
