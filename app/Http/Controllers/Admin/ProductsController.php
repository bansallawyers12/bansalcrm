<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Admin;
use App\Models\Product;
 
use Auth;
use Config;

class ProductsController extends Controller
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
	/**
     * All Vendors.
     *
     * @return \Illuminate\Http\Response
     */
	public function index(Request $request)
	{
		//check authorization start	
			
			/* if($check)
			{
				return Redirect::to('/admin/dashboard')->with('error',config('constants.unauthorized'));
			} */	
		//check authorization end
	
		$query 		= Product::where('id', '!=', ''); 
		  
		$totalData 	= $query->count();	//for all data
		if ($request->has('name')) 
		{
			$name 		= 	$request->input('name'); 
			if(trim($name) != '')
			{
				$query->where('name', 'LIKE', '%'.$name.'%');
					
			}
		}
		if ($request->has('branch')) 
		{
			$branch 		= 	$request->input('branch'); 
			if(trim($branch) != '')
			{
				$query->whereHas('branchdetail', function ($q) use($branch){
					$q->where('name','Like', '%'.$branch.'%');
				});
					
			}
		}
		if ($request->has('branch')) 
		{
			$branch 		= 	$request->input('branch'); 
			if(trim($branch) != '')
			{
				$query->whereHas('branchdetail', function ($q) use($branch){
					$q->where('name','Like', '%'.$branch.'%');
				});
					
			}
		}
		if ($request->has('partner')) 
		{
			$branch 		= 	$request->input('partner'); 
			if(trim($branch) != '')
			{
				$query->whereHas('partnerdetail', function ($q) use($branch){
					$q->where('partner_name','Like', '%'.$branch.'%');
				});
					
			}
		}
		
		$lists		= $query->sortable(['id' => 'desc'])->paginate(20);
		
		
		return view('Admin.products.index', compact(['lists', 'totalData'])); 	
				
		//return view('Admin.products.index'); 	 
	}
	
	
	public function create(Request $request)
	{
		//check authorization end
		//return view('Admin.users.create',compact(['usertype']));	
		
		return view('Admin.products.create');	
	}
	
	public function store(Request $request)
	{		
		//check authorization end
		if ($request->isMethod('post')) 
		{
			$this->validate($request, [
										'name' => 'required|max:255'
									  ]);
			
			$requestData 		= 	$request->all();
			 
			$obj				= 	new Product; 
			$obj->name	=	@$requestData['name'];
			$obj->partner	=	@$requestData['partner'];
			$obj->branches	=	@$requestData['branches'];
			$obj->product_type	=	@$requestData['product_type'];
			$obj->revenue_type	=	@$requestData['revenue_type']; 
			$obj->duration	=	@$requestData['duration'];
			$obj->intake_month	=	@$requestData['intake_month'];
			$obj->description	=	@$requestData['description'];
			$obj->note	=	@$requestData['note'];
			
			$saved				=	$obj->save();  
			
			if(!$saved)
			{
				return redirect()->back()->with('error', Config::get('constants.server_error'));
			}
			else
			{
				return Redirect::to('/admin/products')->with('success', 'Products Added Successfully');
			}				
		}	

		return view('Admin.products.create');	 
	}
	
	public function edit(Request $request, $id = NULL)
	{
	
		//check authorization end
		
		if ($request->isMethod('post')) 
		{
			$requestData 		= 	$request->all();
			
			$this->validate($request, [										
										'name' => 'required|max:255'
									  ]);
								  					  
			$obj							= 	Product::find(@$requestData['id']);
						
			$obj->name	=	@$requestData['name'];
			$obj->partner	=	@$requestData['partner'];
			$obj->branches	=	@$requestData['branches'];
			$obj->product_type	=	@$requestData['product_type'];
			$obj->revenue_type	=	@$requestData['revenue_type']; 
			$obj->duration	=	@$requestData['duration'];
			$obj->intake_month	=	@$requestData['intake_month'];
			$obj->description	=	@$requestData['description'];
			$obj->note	=	@$requestData['note'];
			
			$saved							=	$obj->save();
			
			if(!$saved)
			{
				return redirect()->back()->with('error', Config::get('constants.server_error'));
			}
			
			else
			{
				return Redirect::to('/admin/products')->with('success', 'Products Edited Successfully');
			}				
		}

		else
		{		
			if(isset($id) && !empty($id))
			{
				
				$id = $this->decodeString($id);	
				if(Product::where('id', '=', $id)->exists()) 
				{
					$fetchedData = Product::find($id);
					return view('Admin.products.edit', compact(['fetchedData']));
				}
				else 
				{
					return Redirect::to('/admin/products')->with('error', 'Products Not Exist');
				}	
			}
			else
			{
				return Redirect::to('/admin/products')->with('error', Config::get('constants.unauthorized'));
			}		
		} 	
		
	}
	
	public function detail(Request $request, $id = NULL){
		if(isset($id) && !empty($id))  
			{				
				$id = $this->decodeString($id);	
				if(Product::where('id', '=', $id)->exists()) 
				{ 
					$fetchedData = Product::find($id);
					return view('Admin.products.detail', compact(['fetchedData']));
				}
				else 
				{  
					return Redirect::to('/admin/products')->with('error', 'Products Not Exist');
				}	
			}
			else
			{
				return Redirect::to('/admin/products')->with('error', Config::get('constants.unauthorized'));
			}
	}
	
	public function getrecipients(Request $request){
		$squery = $request->q;
		if($squery != ''){
			
			 $partners = \App\Models\Admin::where('is_archived', '=', 0)
       ->where('role', '=', 7)
       ->where(
           function($query) use ($squery) {
             return $query
                    ->where('email', 'LIKE', '%'.$squery.'%')
                    ->orwhere('partner_name', 'LIKE','%'.$squery.'%');
            })
            ->get();
			
			$items = array();
			foreach($partners as $partner){
				$items[] = array('partner_name' => $partner->partner_name,'email'=>$partner->email,'status'=>'Partner','id'=>$partner->id,'cid'=>base64_encode(convert_uuencode(@$partner->id)));
			}
			
			echo json_encode(array('items'=>$items));
		}
	}
	
	
	public function getallproducts(Request $request){
		$squery = $request->q;
		if($squery != ''){
			
			 $partners = \App\Models\Admin::where('is_archived', '=', 0)
       ->where('role', '=', 7)
       ->where( 
           function($query) use ($squery) {
             return $query
                    ->where('email', 'LIKE', '%'.$squery.'%')
                    ->orwhere('partner_name', 'LIKE','%'.$squery.'%');
            })
            ->get();
			
			$items = array();
			foreach($partners as $partner){ 
				$items[] = array('partner_name' => $partner->partner_name,'email'=>$partner->email,'status'=>'Partner','id'=>$partner->id,'cid'=>base64_encode(convert_uuencode(@$partner->id)));
			}
			
			echo json_encode(array('items'=>$items));
		}
	}
	
	
	public function saveotherinfo(Request $request){
		// Feature removed - ProductAreaLevel model no longer exists
		$response['status'] = false;
		$response['message'] = 'Feature removed - ProductAreaLevel model no longer exists. Product academic requirements feature has been removed.';
		return response()->json($response);
	}
	
	
	public function getotherinfo(Request $request){
		// Feature removed - ProductAreaLevel model no longer exists
		return '<h4>Feature removed - ProductAreaLevel model no longer exists</h4>';
	}
	
	
	public function savefee(Request $request){
		// Feature removed - fee_options table no longer exists
		// Replaced by ApplicationFeeOption for application-specific fees
		$response['status'] = false;
		$response['message'] = 'Feature removed - fee_options table no longer exists. Please use Application Fee Options instead.';
		return response()->json($response);
	}
	
	public function getallfees(Request $request){
		// Feature removed - fee_options table no longer exists
		// Return empty collection
		return '';
	}
	
	public function editfee(Request $request){
		// Feature removed - fee_options table no longer exists
		return '<h4>Record Not Found - Feature removed</h4>';
	}
	
	
	public function editfeeform(Request $request){
		// Feature removed - fee_options table no longer exists
		$response['status'] = false;
		$response['message'] = 'Feature removed - fee_options table no longer exists. Please use Application Fee Options instead.';
		return response()->json($response);
	}
	
	public function deletefee(Request $request){
		// Feature removed - fee_options table no longer exists
		$response['status'] = false;
		$response['message'] = 'Feature removed - fee_options table no longer exists';
		return response()->json($response);
	}
}
