<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CheckinLog extends Authenticatable
{
    use Notifiable;
	use Sortable;

	protected $fillable = [
        'id', 'created_at', 'updated_at'
    ];
	
	public $sortable = ['id','created_at', 'updated_at'];
	
	/**
     * Get the client for this check-in
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Admin', 'client_id', 'id');
    }
}