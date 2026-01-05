<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use App\Models\Admin;
// Tax model removed - taxes table dropped (January 2026) 
  
use Auth; 
use Config;

/**
 * DEPRECATED: Tax Management feature removed - taxes table dropped (January 2026)
 * This controller is kept for reference but all functionality has been disabled.
 */
class TaxController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
		
	public function index(Request $request)
	{
		// Feature removed - taxes table dropped
		return redirect()->back()->with('error', 'Tax Management feature has been removed.');
	}
	
	public function create(Request $request)
	{
		// Feature removed - taxes table dropped
		return redirect()->back()->with('error', 'Tax Management feature has been removed.');
	}
	 
	public function store(Request $request)
	{		
		// Feature removed - taxes table dropped
		return redirect()->back()->with('error', 'Tax Management feature has been removed.');
	}
	
	public function edit(Request $request, $id = NULL)
	{
		// Feature removed - taxes table dropped
		return redirect()->back()->with('error', 'Tax Management feature has been removed.');
	}
}
