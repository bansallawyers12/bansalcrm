<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserLog extends Authenticatable
{
    use Notifiable;
	use Sortable;

	protected $fillable = [
        'id', 'user_id', 'level', 'message', 'ip_address', 'user_agent', 'created_at', 'updated_at'
    ];
	
	public $sortable = ['id'];
	
}