<?php namespace App\Helpers;
use Auth;
class Settings
{
    // Settings helper updated - settings table dropped (January 2026)
    // Returns default values instead of database values
    static function sitedata($fieldname)
    {
        // Settings table removed - returning default values
        // Default date format: 'Y-m-d'
        // Default time format: 'H:i'
        if($fieldname == 'date_format'){
            return 'Y-m-d'; // Default date format
        }elseif($fieldname == 'time_format'){
            return 'H:i'; // Default time format
        }
        return 'none';
    }
    
}
?>