<?php

namespace EcoSim\Tools;

use jc21\CliTable;

/**
 * Class DisplayTables
 * @package EcoSim\Tools
 */
class DisplayTables
{
    private $last_table_colour = "";

    const DISPLAY_COLUMNS = ['current', 'required'];
    const RESIDENTIAL_DISPLAY_COLUMNS = ['population', 'populationLimit'];

    private $populations;
    private $populationPool;
    private $round_counter;

    public function displayTable($counter, $populations, $type = 'business', $entities)
    {
        $this->populations = $populations;
        $this->round_counter = $counter;
        $classes = [];
        foreach ($populations as $population) {
            $classes[] = $population->getClass();
        }
        if ($type == 'business') {
            $this->outputBusinessPopulationTable($classes, $this->getBusinessPopulationData($entities));
        } elseif ($type == 'residential') {
            $this->outputResidentialPopulationTable($classes, $this->getResidentialPopulationData($entities));
        }
    }

    private function outputBusinessPopulationTable($classes, $data)
    {
        $this->last_table_colour = $table_colour = $this->getColor($this->last_table_colour);

        $table = new CliTable();
        $table->setTableColor($table_colour);
        $table->setHeaderColor('yellow');
        $table->addField('Round', 'round', false, 'white');
        $table->addField('Business Name', 'business_name', false, 'white');
        foreach (self::DISPLAY_COLUMNS as $column) {
            foreach ($classes as $class) {
                $abv_parts = explode('_', $class);
                foreach ($abv_parts as &$abv_part) {
                    $abv_part = ucfirst($abv_part);
                }
                $abv = implode(' ', $abv_parts);
                $table->addField(ucfirst($column) . ' ' . $abv, $column . '_' . $class, false, 'white');
            }
        }
        $table->addField('Population', 'population', false, 'white');
        $table->injectData($data);
        $table->display();
        exit;
    }

    private function outputResidentialPopulationTable($classes, $data) {
        $this->last_table_colour = $table_colour = $this->getColor($this->last_table_colour);

        $table = new CliTable();
        $table->setTableColor($table_colour);
        $table->setHeaderColor('yellow');
        $table->addField('Round', 'round', false, 'white');
        $table->addField('Residential Name', 'residential_name', false, 'white');
        foreach (self::RESIDENTIAL_DISPLAY_COLUMNS as $column) {
            foreach ($classes as $class) {
                $abv_parts = explode('_', $class);
                foreach ($abv_parts as &$abv_part) {
                    $abv_part = ucfirst($abv_part);
                }
                $abv = implode(' ', $abv_parts);
                $table->addField(ucfirst($column) . ' ' . $abv, $column . '_' . $class, false, 'white');
            }
        }
        $table->addField('Population', 'population', false, 'white');
        $table->injectData($data);
        $table->display();
    }

    private function getBusinessPopulationData($businesses)
    {
        $data = [];

        foreach ($businesses as $name => $object) {
            $this_business_data = [];
            $this_business_data['round'] = $this->round_counter;
            $this_business_data['business_name'] = $object->getName();
            foreach (self::DISPLAY_COLUMNS as $column) {
                foreach ($this->populations as $class) {
                    $method = 'get' . ucfirst($column) . 'Workers';
                    $this_business_data[$column . '_' . $class->getClass()] = $object->$method($class->getClass());
                }
            }
            $workingPop = 0;
            foreach ($this->populations as $class) {
                foreach ($businesses as $business) {
                    $workingPop += $business->getCurrentWorkers($class->getClass());
                }
                $workingPop += $this->populationPool[$class];
            }


            $this_business_data['population'] = $workingPop;
            $data[] = $this_business_data;
        }
        return $data;
    }
    private function getResidentialPopulationData($residentials)
    {
        $data = [];

        foreach ($residentials as $residential) {
            $this_residential_data = [];
            $this_residential_data['round'] = $this->round_counter;
            $this_residential_data['residential_name'] = $residential->getName();
            foreach (self::RESIDENTIAL_DISPLAY_COLUMNS as $column) {
                foreach ($this->populationClasses as $class) {
                    if ($residential->getClass() == $class) {
                        $method = 'get' . ucfirst($column);
                        $this_residential_data[$column . '_' . $class] = $residential->$method();
                    }
                }
            }
            $workingPop = 0;
            foreach ($this->populations as $class) {
                foreach ($this->game_businesses as $business) {
                    $workingPop += $business->getCurrentWorkers($class);
                }
                $workingPop += $this->populationPool[$class];
            }


            $this_residential_data['population'] = $workingPop;
            $data[] = $this_residential_data;
        }

        return $data;
    }

    private function getColor($old)
    {
        $colours = ['blue', 'green', 'cyan', 'magenta'];
        do {
            $colour = $colours[mt_rand(0, count($colours) - 1)];
        } while ($colour == $old);

        return $colour;
    }
}