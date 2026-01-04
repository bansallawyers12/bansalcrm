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
use App\Models\ProductAreaLevel;
 
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
		$requestData 		= 	$request->all();
			 if(ProductAreaLevel::where('product_id', @$requestData['client_id'])->exists()){
				 $ac = ProductAreaLevel::where('product_id', @$requestData['client_id'])->first();
				 $obj				= 	 ProductAreaLevel::find($ac->id); 
			 }else{
				$obj				= 	new ProductAreaLevel;  
			 }
			
			$obj->subject_area			=	@$requestData['subject_area'];
			$obj->subject	=	@$requestData['subject'];
			$obj->degree	=	@$requestData['degree_level'];
		
			$obj->product_id	=	@$requestData['client_id'];
			$saved				=	$obj->save();  
			
			
			if(!$saved)
			{
				
				$response['status'] 	= 	false;
			$response['message']	=	'Please try again';
		
			}
			else
			{
				$subjectarea = \App\Models\SubjectArea::where('id', $obj->subject_area)->first();
				$subject = \App\Models\Subject::where('id', $obj->subject)->first();
				$data = '<div class="row"><div class="col-md-4"><strong>Subject Area</strong><p>'.$subjectarea->name.'</p></div><div class="col-md-4"><strong>Subject</strong><p>'.$subject->name.'</p></div><div class="col-md-4"><strong>Degree Level</strong><p>'.$obj->degree.'</p></div></div>';
				$response['status'] 	= 	true;
				$response['message']	=	'You’ve successfully added a Subject Area.';
					$response['data']	=	$data;
			}	
			echo json_encode($response);	
	}
	
	
	public function getotherinfo(Request $request){
		$ac = ProductAreaLevel::where('product_id', @$request->id)->first();
		ob_start();
		?>
		<div class="col-12 col-md-6 col-lg-6">
						<div class="form-group">
							<label for="degree_level">Subject Area <span class="span_req">*</span></label> 	
							<select data-valid="" class="form-control subject_area select2" id="subjectlist" name="subject_area">
									<option value="">Please Select Subject Area</option>
									<?php
									foreach(\App\Models\SubjectArea::all() as $sublist){
										?>
										<option <?php if($ac->subject_area == $sublist->id){ echo 'selected'; } ?> value="<?php echo $sublist->id; ?>"><?php echo $sublist->name; ?></option>
										<?php
									}
									?>
								</select>
							<span class="custom-error degree_level_error" role="alert">
								<strong></strong>
							</span> 
						</div>
					</div>
					<div class="col-12 col-md-6 col-lg-6">
						<div class="form-group">
							<label for="degree_level">Subject<span class="span_req">*</span></label> 	
							<select data-valid="" class="form-control subject select2" id="subject" name="subject">
									<option value="">Please Select Subject</option>
									<?php
									foreach(\App\Models\Subject::where('subject_area',$ac->subject_area) ->orderby('name','ASC')->get() as $sublist){
										?>
										<option <?php if($ac->subject == $sublist->id){ echo 'selected'; } ?> value="<?php echo $sublist->id; ?>"><?php echo $sublist->name; ?></option>
										<?php
									}
									?>
								</select>
							<span class="custom-error degree_level_error" role="alert">
								<strong></strong>
							</span> 
						</div>
					</div>
					<div class="col-12 col-md-6 col-lg-6">
						<div class="form-group">
							<label for="degree_level">Degree Level</label> 	
							<select data-valid="required" class="form-control degree_level select2" name="degree_level">
								<option value=""></option>
								<option <?php if($ac->degree == 'Bachelor'){ echo 'selected'; } ?> value="Bachelor">Bachelor</option>
									<option value="Certificate" <?php if($ac->degree == 'Certificate'){ echo 'selected'; } ?>>Certificate</option>
									<option value="Diploma" <?php if($ac->degree == 'Diploma'){ echo 'selected'; } ?>>Diploma</option>
									<option value="High School" <?php if($ac->degree == 'High School'){ echo 'selected'; } ?>>High School</option>
									<option value="Master" <?php if($ac->degree == 'Master'){ echo 'selected'; } ?>>Master</option>
							</select>
							<span class="custom-error degree_level_error" role="alert">
								<strong></strong>
							</span> 
						</div>
					</div>
					<div class="col-12 col-md-12 col-lg-12">
						<button onclick="customValidate('editsubjectarea')" type="button" class="btn btn-primary">Save</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
		<?php
		return ob_get_clean();
		
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
