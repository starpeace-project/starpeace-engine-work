<?php

namespace Spengine\commands;

use Bootstrap\Console\Artisan;
use Illuminate\Console\Command;
use Spengine\initialdata\Population;
use Spengine\initialdata\Residentials;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Spengine\initialdata\Businesses;
use EcoSim\Tools\Calculator;
use EcoSim\Entities\PopulationClass;
use EcoSim\Entities\Business;
use EcoSim\Tools\DisplayTables;

class Run extends Command {

    const ENGINE_VERSION = 5.1;
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
	private $round_delay_seconds = 500000;

	private $population;
	private $businesses;
	private $residentials;

	private $round = 1;

	const DISPLAY_OUTPUT = true;
	private $display_tables;
	const DEBUG = false;
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->display_tables = new DisplayTables();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
	    $this->info('Engine Version ' . self::ENGINE_VERSION);
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
        //Build our example data
        $this->initialise_entities();

        while (true) {
            $loop_start_time = microtime(true);

            $this->calculate();

            $loop_end_time = microtime(true);

            $this->storeRoundData();

            usleep($this->round_delay_seconds);
            $this->round++;
        }
    }

    private function calculate()
    {
        $loop_start_time = microtime(true);
        $this->calculate_desirabilities();

        if ($this->round % 24 === 0) {
            $this->increase_population();
            $this->distribute_population();
            $loop_end_time = microtime(true);
            system('clear');
            if (self::DISPLAY_OUTPUT) {
                echo PHP_EOL;
                $this->display_round_data_table($loop_start_time, $loop_end_time);
                $this->display_residential_data_table();
                $this->display_business_data_table();
                // $this->display_tables->displayTable($this->population, 'residential', $this->residentials);
            }
        } else {
            echo '.';
        }


    }

    private function storeRoundData()
    {

    }

    private function initialise_entities() {
	    $this->population = Population::get_population();
	    $this->businesses = Businesses::get_businesses();
	    $this->residentials = Residentials::get_residentials();
    }

    private function calculate_desirabilities()
    {
        $this->calculate_business_desirabilities();
        $this->calculate_residential_desirabilities();
    }

    private function calculate_business_desirabilities()
    {
        $business_wage_desire_totals = [];
        $unemployment_desire_totals = [];

        foreach ($this->businesses as $business) {
            $business->runCalculations($this->getWageAverages());
            $wage_desires = $business->getWageDesires();
            $unemployment_desires = $business->getUnemploymentDesires();
            foreach ($wage_desires as $desire_class => $desire) {
                $unemployment_desire_totals[$desire_class][] = $desire;
                $business_wage_desire_totals[$desire_class][] = $unemployment_desires[$desire_class];
            }
        }

        foreach ($this->population as $class) {
            $business_wage_desire_total = 0;
            foreach ($business_wage_desire_totals[$class->getClass()] as $total) {
                $business_wage_desire_total += $total;
            }
            $business_unemployment_desire_total = 0;
            foreach ($unemployment_desire_totals[$class->getClass()] as $total) {
                $business_unemployment_desire_total += $total;
            }

            $class->setUnemploymentDesire(Calculator::getPercentage($business_unemployment_desire_total, count($unemployment_desire_totals[$class->getClass()]) * 100));
            $class->setBusinessWageDesire(Calculator::getPercentage($business_wage_desire_total, count($business_wage_desire_totals[$class->getClass()]) * 100));
        }
    }

    private function calculate_residential_desirabilities()
    {
        $residential_rent_desire_totals = [];
        $residential_occupancy_desire_totals = [];
        foreach ($this->population as $class) {
            foreach ($this->residentials as $residential) {
                $residential->runCalculations($this->getAverageRents());
                if ($residential->getClass() == $class->getClass()) {
                    $residential_rent_desire_totals[] = $residential->getRentDesirability();
                    $residential_occupancy_desire_totals[] = $residential->getOccupancyDesire();
                }
            }
            $class->setResidentialRentDesire(Calculator::getArrayAverage($residential_rent_desire_totals));
            $class->setResidentialOccupancyDesire(Calculator::getArrayAverage($residential_occupancy_desire_totals));
        }
    }

    public function getAverageRents()
    {
        $average_rents = [];
        foreach ($this->population as $class) {
            $average_rents[$class->getClass()] = $class->getAverageRent();
        }
        return $average_rents;
    }

    public function getWageAverages()
    {
        $average_wages = [];
        foreach ($this->population as $class) {
            $average_wages[$class->getClass()] = $class->getAverageWageLevel();
        }
        return $average_wages;
    }

    private function increase_population()
    {
        $required_populations = [];
        foreach ($this->businesses as $business) {
            $required_workers = $business->getRequiredWorkers();
            foreach ($required_workers as $class => $value) {
                if (!empty($required_populations[$class])) {
                    $required_populations[$class] += $value;
                } else {
                    $required_populations[$class] = $value;
                }
            }
        }

        foreach ($required_populations as $class => $required_population) {
            echo self::DEBUG ? "Class = {$class}" . PHP_EOL : '';
            foreach ($this->population as $population) {
                echo self::DEBUG ? "Current class = {$population->getClass()}" . PHP_EOL : '';
                if ($population->getClass() == $class) {
                    echo self::DEBUG ? "Current Population Required = {$population->getRequiredPopulation()}" . PHP_EOL : '';
                    echo self::DEBUG ? "Adding Population Required += {$required_populations[$population->getClass()]}" . PHP_EOL : '';
                    echo self::DEBUG ? "Classes (class) = {$class}, (population->getClass()) = {$population->getClass()}" . PHP_EOL : '';
                    $population->setRequiredPopulation($required_populations[$population->getClass()]);
                    $population->increasePool();
                }
            }
        }
    }

    private function distribute_population()
    {
        foreach ($this->population as $population) {
            $population->distributePool($this->businesses, $this->residentials);
        }
    }

    private function display_round_data_table($start_time, $end_time)
    {
        $calculation_time = $end_time - $start_time;

        $headers = ['Round', 'Calculation Time (seconds)'];
        $row[] = [
            'round' => $this->round,
            'calc_time' => $calculation_time,
        ];
        $this->table($headers, $row);
    }

    private function display_business_data_table()
    {
        $headers = ['Business Name', 'LC Workers Req', 'MC Workers Req', 'HC Workers Req', 'Current LC Workers', 'Current MC Workers', 'Current HC Workers'];
        $rows = [];

        foreach ($this->businesses as $business) {
            $required_workers = $business->getRequiredWorkers();
            $current_workers = $business->getCurrentWorkers();
            $rows[] = [
                'name' => $business->getName(),
                'lcr' => $required_workers['low_class'],
                'mcr' => $required_workers['middle_class'],
                'hcr' => $required_workers['high_class'],
                'lcc' => $current_workers['low_class'],
                'mcc' => $current_workers['middle_class'],
                'hcc' => $current_workers['high_class'],
            ];
        }

        $this->table($headers, $rows);
    }

    private function display_residential_data_table()
    {
        $headers = ['Residence Name', 'Class', 'Max Population', 'Current Population'];
        $rows = [];

        foreach ($this->residentials as $residential) {
            $rows[] = [
                'name' => $residential->getName(),
                'class' => $residential->getClass(),
                'mp' => $residential->getPopulationLimit(),
                'cp' => $residential->getPopulation(),
            ];
        }

        $this->table($headers, $rows);
    }
}
