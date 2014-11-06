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

JHtml::_('bootstrap.tooltip');

$user = JFactory::getUser();
$params = new Registry;
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
		<tr>
			<td class="center hidden-phone">
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_doc->name; ?>" />
			</td>
			<td>
				<a  title="<?php echo $this->_tmp_doc->name; ?>">
					<?php  echo JHtml::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, null, true, true) ? JHtml::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true);?> </a>
			</td>
			<td class="description"  title="<?php echo $this->_tmp_doc->name; ?>">
				<?php echo $this->_tmp_doc->title; ?>
			</td>
			<td>&#160;

			</td>
			<td class="filesize">
				<?php echo JHtml::_('number.bytes', $this->_tmp_doc->size); ?>
			</td>
		<?php if ($user->authorise('core.delete', 'com_media')):?>
			<td>
				<?php if (!empty($this->_tmp_doc->properties)) : ?>
					<a
						href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;context=<?php echo $this->state->context; ?>&amp;folder=<?php echo $this->_tmp_doc->path_relative; ?>"
						class="btn btn-mini btn-default media-detail media-form media-doc"
						data-properties='<?php echo $this->_tmp_doc->properties; ?>'
						target="folderframe">
						<i class="icon-edit icon-white"></i> <?php echo JText::_('JACTION_EDIT'); ?>
						</a>
				<?php endif; ?>
				&nbsp;
				<a class="delete-item btn btn-mini btn-danger" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;context=<?php echo $this->state->context; ?>&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_doc->name; ?>" rel="<?php echo $this->_tmp_doc->name; ?>"><i class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></i></a>
			</td>
		<?php endif;?>
		</tr>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_doc, &$params));
?>
