<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ActivitiesLog extends Authenticatable
{
    use Notifiable;
	use Sortable;
	
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activities_logs';
 
    /**
     * Get the client associated with this activity
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Admin', 'client_id', 'id');
    }
    
    /**
     * Get the user who created this activity
     */
    public function createdBy()
    {
        return $this->belongsTo('App\Models\Admin', 'created_by', 'id');
    }
	
}
