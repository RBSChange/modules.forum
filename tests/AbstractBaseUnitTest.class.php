<?php
abstract class forum_tests_AbstractBaseUnitTest extends forum_tests_AbstractBaseTest
{
	/**
	 * @return void
	 */
	public function prepareTestCase()
	{
		$this->resetDatabase();
	}
}