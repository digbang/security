<?php namespace Digbang\Security\Commands;

use Illuminate\Console\Command;

/**
 * Class MigrationsCommand
 * @package Digbang\Security\Commands
 */
class MigrationsCommand extends Command
{
	protected $name = 'security:migrations';
	protected $description = 'Install the security package migrations';

	public function fire()
	{
		$params = ['--package' => 'digbang/security'];
		if ($this->inWorkbench())
		{
			$params = ['--bench' => 'digbang/security'];
		}

		$this->call('migrate', $params);

	}

	/**
	 * @return bool
	 */
	protected function inWorkbench()
	{
		return strpos(__DIR__, DIRECTORY_SEPARATOR . 'workbench' . DIRECTORY_SEPARATOR) !== false;
	}
}