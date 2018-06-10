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
     * This controls the speed of the simulation
     *
     * @var int $tickDelaySeconds
     */
	private $roundDelaySeconds = 2;

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
	    $this->info('Engine starting.....');

	    $this->runMigrations();
	    $this->defineStartTime();
        $this->runEngine();
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

    /**
     * This method is responsible for creating the table structure in the sqlite database being held
     * in memory.
     *
     * To build extra structure we only need to add the migrations for them.
     * Migrations are being used to allow for development of the structure, instead of altering the
     * same table definitions all the time as columns etc are added we just add a new migration which
     * will do the extra work.
     */
	private function runMigrations()
    {
        $this->info('Building Database in memory..');
        \Artisan::call('migrate', [
            '--force' => true,
        ]);
        $this->info('Migrations to create database have run.');
    }

    private function defineStartTime()
    {
        define('ENGINE_START', microtime(true));
        $this->info('Engine start defined: ' . ENGINE_START);
    }

    private function getEndTime() {
	    return microtime(true);
    }

    private function getRunningTime()
    {
        return microtime(true) - ENGINE_START;
    }

    private function runEngine()
    {
        while (true) {
            $loop_start_time = microtime(true);

            $this->calculate();

            $loop_end_time = microtime(true);

            $this->storeRoundData();
            $this->updateExternalData();

            sleep($this->roundDelaySeconds);
        }
    }

    private function calculate()
    {
        /**
         * First thing we do is fetch the data to work on.
         */
        $start_data = $this->fetchExternalData();
    }

    private function fetchExternalData()
    {
        /**
         * Here we will fetch the data we start with from the mysql server.
         *
         * We will fetch it each time the loop starts.
         */
    }

    private function storeRoundData()
    {

    }

    private function updateExternalData()
    {

    }
}
