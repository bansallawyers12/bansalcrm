<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Task extends Authenticatable
{ 
    use Notifiable;
	use Sortable;  
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array  
     */
	
	
	protected $fillable = [
        'id', 'created_at', 'updated_at'
    ];
   
	public $sortable = ['id', 'created_at', 'due_date', 'status', 'priority_no'];
	
	/**
     * Get the user that created the task
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Admin', 'user_id', 'id');
    }
}
