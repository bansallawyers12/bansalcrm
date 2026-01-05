<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use App\Models\Admin;
// Enquiry model removed - enquiries table dropped (January 2026)
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Auth;
use Config;
 
/**
 * DEPRECATED: Enquiries feature removed - enquiries table dropped (January 2026)
 * This controller is kept for reference but all functionality has been disabled.
 */
class EnquireController extends Controller
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
		// Feature removed - enquiries table dropped
		return redirect()->back()->with('error', 'Enquiries feature has been removed.');
	}
	
	public function archived(Request $request)
	{ 
		// Feature removed - enquiries table dropped
		return redirect()->back()->with('error', 'Enquiries feature has been removed.');
	}
	
	public function archivedenquiry(Request $request, $id){
		// Feature removed - enquiries table dropped
		$response['status'] = false;
		$response['message'] = 'Enquiries feature has been removed.';
		echo json_encode($response);
	}
	
	public function covertenquiry(Request $request, $id)
	{ 
		// Feature removed - enquiries table dropped
		$response['status'] = false;
		$response['message'] = 'Enquiries feature has been removed.';
		echo json_encode($response);
	}
}
?>