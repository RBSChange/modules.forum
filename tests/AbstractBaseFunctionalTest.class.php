<?php
abstract class forum_tests_AbstractBaseFunctionalTest extends forum_tests_AbstractBaseTest
{
	/**
	 * @return void
	 */
	public function prepareTestCase()
	{
		$this->loadSQLResource('functional-test.sql', true, false);
	}
}