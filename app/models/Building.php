<?php

namespace Spengine\models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Building
 *
 * @param integer id
 * @param integer building_id
 * @param string building_name
 * @param integer building_location_x
 * @param integer building_location_y
 * @param integer building_age_desirability
 * @param integer building_condition_desirability
 * @param integer building_crime_desirability
 * @param integer building_civil_service_desirability
 * @param integer building_services_desirability
 * @param integer building_leisure_desirability
 * @param integer building_population_desirability
 * @param integer building_pollution_desirability
 * @param integer building_beauty_desirability
 * @param integer building_commute_desirability
 * @param integer building_class_desirability
 * @param integer building_connection_desirability
 *
 */
class Building extends Model
{
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'buildings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ //TODO: edit fillable
        
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'secret'
    ];

}
