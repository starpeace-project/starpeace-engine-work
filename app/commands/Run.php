<?php

namespace Spengine\commands;

use Bootstrap\Console\Artisan;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
class Run extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'engine:run';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This command will engage the simulation engine.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
	    $this->info('Engine started.');

	    $this->info('Building Database in memory..');
        \Artisan::call('migrate', [
            '--force' => true,
        ]);
        $this->info('Migrations to create database have run.');


	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
	    return [];
//		return array(
//			array('example', InputArgument::REQUIRED, 'An example argument.'),
//		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
	    return [];
//		return array(
//			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
//		);
	}

}
