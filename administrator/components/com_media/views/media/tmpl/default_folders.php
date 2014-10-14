<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Set up the sanitised target for the ul
//$ulTarget = (empty($this->folders['data']->relative)) ? $this->folders['data']->context : $this->folders['data']->relative;
//$ulTarget = str_replace('/', '-', $ulTarget);
$ulTarget = ($this->folders['data']->relative) ? str_replace('/', '-', $this->folders['data']->relative) : $this->folders['data']->context;


//echo ($folder['data']->subfolders) ? 'children' : 'childless';
?>
<ul class="nav nav-list collapse in" id="collapseFolder-<?php echo $ulTarget; ?>">
<?php if (isset($this->folders['children'])) :
	foreach ($this->folders['children'] as $folder) :
	// Get a sanitised name for the target
	$target = (empty($folder['data']->relative)) ? $folder['data']->context : $folder['data']->relative;
	$target = str_replace('/', '-', $target); ?>
	<li id="<?php echo $target; ?>" class="">
		<i class="icon-folder-2 pull-left" data-toggle="collapse" data-target="#collapseFolder-<?php echo $target; ?>"></i>
		<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;context=<?php echo $folder['data']->context; ?>&amp;folder=<?php echo $folder['data']->relative; ?>" class="show-contents" target="folderframe">
			<?php echo $folder['data']->name; ?>
		</a>
		<?php echo $this->getFolderLevel($folder); ?>
	</li>
<?php endforeach;
endif; ?>
</ul>

