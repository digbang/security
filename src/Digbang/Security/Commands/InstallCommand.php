<?php namespace Digbang\Security\Commands;

use Illuminate\Console\Command;

/**
 * Class InstallCommand
 * @package Digbang\Security\Commands
 */
class InstallCommand extends Command
{
	protected $name = 'security:install';
	protected $description = 'Install the security package';

	public function fire()
	{
		$this->call('migrate', ['--package' => 'cartalyst/sentry']);
	}
}