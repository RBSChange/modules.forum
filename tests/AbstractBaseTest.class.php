<?php
abstract class forum_tests_AbstractBaseTest extends f_tests_AbstractBaseTest
{
	/**
	 * @return String
	 */
	protected final function getPackageName()
	{
		return 'modules_forum';
	}

	/**
	 * @return void
	 */
	protected function clearServicesCache()
	{
		parent::clearServicesCache();
		RequestContext::clearInstance();
		self::clearModuleServiceCache();
	}

	/**
	 * @return void
	 */
	public static function clearModuleServiceCache()
	{
		// Call here methods to clear caches in services.
	}
}