<?php

namespace Spengine\external_models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ExternalResidential
 *
 * 
 */
class ExternalResidential extends Model
{
    protected $connection = "mysql";
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
    protected $table = 'external_residentials';

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
