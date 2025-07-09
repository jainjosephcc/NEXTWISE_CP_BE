<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Mt5userlist extends Authenticatable
{
	/**
     * The table associated with the model.
     *
     * @var string
     */
		protected $table = 'mt5_userlist';
	
	/**
     * The primary key defines in table.
     *
     * @var string
     */
		protected $primaryKey = 'id';
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
		protected $fillable = [
			'mt_userid', 'crm_userid', 'name', 'groupname','investpass','mainpass',
		];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
		protected $hidden = [

		];
		
		/**
		 * This function get the Property Images data 
		 *
		 * @return Property Images
		 */
		 
}
