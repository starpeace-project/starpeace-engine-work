<?php

namespace Spengine\models;

/**
 * Class Building
 *
 * 
 */
class Building extends Eloquent
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
