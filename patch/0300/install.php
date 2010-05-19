<?php
/**
 * @author intportg
 * @package modules.forum
 */
class forum_patch_0300 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		parent::execute();
		
		$linkPath = f_util_FileUtils::buildDocumentRootPath('bbeditor');
		$linkTarget = f_util_FileUtils::buildWebeditPath('modules', 'forum', 'lib', 'bbeditor');
		if (is_link($linkPath))
		{
			if (is_link($linkPath) && readlink($linkPath) == $linkTarget)
			{
				unlink($linkPath);
			}
			else
			{
				$this->logWarning('/bbeditor exists but doesn\'t links the expected directory.');
			}
		}
		else if (file_exists($linkPath))
		{
			$this->logWarning('/bbeditor exists but is not a symlink.');
		}
	}

	/**
	 * Returns the name of the module the patch belongs to.
	 *
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'forum';
	}

	/**
	 * Returns the number of the current patch.
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0300';
	}
}