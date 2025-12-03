
<?php $__env->startSection('title', 'Edit Leads'); ?>

<?php $__env->startSection('content'); ?>

<!-- Main Content -->
<div class="main-content">
	<section class="section">
		<div class="section-body">
		    <div class="server-error">
				<?php echo $__env->make('../Elements/flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
			</div>
			<?php echo Form::open(array('url' => 'admin/leads/edit', 'name'=>"edit-leads", 'autocomplete'=>'off', "enctype"=>"multipart/form-data")); ?> 
			 <?php echo Form::hidden('id', @$fetchedData->id); ?>

				<div class="row">   
					<div class="col-12 col-md-12 col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4>Edit Leads</h4>
								<div class="card-header-action">
									<a href="<?php echo e(route('admin.leads.index')); ?>" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back</a>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-12 col-lg-12">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-3 col-md-3 col-lg-3">
										<div class="form-group">
											<input type="hidden" id="old_profile_img" name="old_profile_img" value="<?php echo e(@$fetchedData->profile_img); ?>" />
											<div class="profile_upload">
												<div class="upload_content">
												<?php if(@$fetchedData->profile_img != ''): ?>
													<img src="<?php echo e(asset('img/profile_imgs')); ?>/<?php echo e(@$fetchedData->profile_img); ?>" style="width:100px;height:100px;" id="output"/> 
												<?php else: ?>
													<img id="output"/> 
												<?php endif; ?>
													<i <?php if(@$fetchedData->profile_img != ''){ echo 'style="display:none;"'; } ?> class="fa fa-camera if_image"></i>
													<span <?php if(@$fetchedData->profile_img != ''){ echo 'style="display:none;"'; } ?> class="if_image">Upload Profile Image</span>
												</div>
												<input onchange="loadFile(event)" type="file" id="profile_img" name="profile_img" class="form-control" autocomplete="off" />
											</div>	
											
											<?php if($errors->has('profile_img')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('profile_img')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-9 col-md-9 col-lg-9">
										<div class="row">
											<div class="col-4 col-md-4 col-lg-4">
												<div class="form-group"> 
													<label for="first_name">First Name <span class="span_req">*</span></label>
													<?php echo Form::text('first_name', @$fetchedData->first_name, array('class' => 'form-control', 'data-valid'=>'required', 'autocomplete'=>'off','placeholder'=>'' )); ?>

													<?php if($errors->has('first_name')): ?>
														<span class="custom-error" role="alert">
															<strong><?php echo e(@$errors->first('first_name')); ?></strong>
														</span> 
													<?php endif; ?>
												</div>
											</div>
											<div class="col-4 col-md-4 col-lg-4">
												<div class="form-group"> 
													<label for="last_name">Last Name <span class="span_req">*</span></label>
													<?php echo Form::text('last_name', @$fetchedData->last_name, array('class' => 'form-control', 'data-valid'=>'required', 'autocomplete'=>'off','placeholder'=>'' )); ?>

													<?php if($errors->has('last_name')): ?>
														<span class="custom-error" role="alert">
															<strong><?php echo e(@$errors->first('last_name')); ?></strong>
														</span> 
													<?php endif; ?>
												</div>
											</div>
											<div class="col-4 col-md-4 col-lg-4">
												<div class="form-group"> 
													<label style="display:block;" for="gender">Gender <span class="span_req">*</span></label>
													<div class="form-check form-check-inline">
														<input class="form-check-input" type="radio" id="male" value="Male" name="gender" <?php if(@$fetchedData->gender == "Male"): ?> checked <?php endif; ?>>
														<label class="form-check-label" for="male">Male</label>
													</div>
													<div class="form-check form-check-inline">
														<input class="form-check-input" type="radio" id="female" value="Female" name="gender" <?php if(@$fetchedData->gender == "Female"): ?> checked <?php endif; ?>>
														<label class="form-check-label" for="female">Female</label>
													</div>
													<div class="form-check form-check-inline">
														<input class="form-check-input" type="radio" id="other" value="Other" name="gender" <?php if(@$fetchedData->gender == "Other"): ?> checked <?php endif; ?>>
														<label class="form-check-label" for="other">Other</label>
													</div>
													<?php if($errors->has('gender')): ?>
														<span class="custom-error" role="alert">
															<strong><?php echo e(@$errors->first('gender')); ?></strong>
														</span> 
													<?php endif; ?>
												</div>
											</div>
											<?php
											if($fetchedData->dob != ''){
												$dob = date('d/m/Y', strtotime($fetchedData->dob));
											}
											?>	
											<div class="col-4 col-md-4 col-lg-4">
												<div class="form-group">
													<label for="dob">
													Date of Birth</label>
													<div class="input-group">
														<div class="input-group-prepend">
															<div class="input-group-text">
																<i class="fas fa-calendar-alt"></i>
															</div>
														</div>
														<?php echo Form::text('dob', @$dob, array('class' => 'form-control dobdatepickers', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?> 
														<?php if($errors->has('dob')): ?>
															<span class="custom-error" role="alert">
																<strong><?php echo e(@$errors->first('dob')); ?></strong>
															</span> 
														<?php endif; ?>
													</div>
												</div>
											</div>
											<div class="col-4 col-md-4 col-lg-4">
												<div class="form-group"> 
													<label for="age">Age</label>
													<div class="input-group">
														<div class="input-group-prepend">
															<div class="input-group-text">
																<i class="fas fa-calendar-alt"></i>
															</div>
														</div>
														<?php echo Form::text('age', $fetchedData->age, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

														<?php if($errors->has('age')): ?>
															<span class="custom-error" role="alert">
																<strong><?php echo e(@$errors->first('age')); ?></strong>
															</span> 
														<?php endif; ?>
													</div>
												</div>
											</div>
											<div class="col-4 col-md-4 col-lg-4">
												<div class="form-group">
													<label for="martial_status">
													Marital Status</label>
													<select style="padding: 0px 5px;" name="martial_status" id="martial_status" class="form-control">
														<option value="">Select Marital Status</option>
														<option value="Married" <?php if(@$fetchedData->martial_status == "Married"): ?> selected <?php endif; ?>>Married</option>
														<option value="Never Married" <?php if(@$fetchedData->martial_status == "Never Married"): ?> selected <?php endif; ?>>Never Married</option>
														<option value="Engaged" <?php if(@$fetchedData->martial_status == "Engaged"): ?> selected <?php endif; ?>>Engaged</option>
														<option value="Divorced" <?php if(@$fetchedData->martial_status == "Divorced"): ?> selected <?php endif; ?>>Divorced</option>
														<option value="Separated" <?php if(@$fetchedData->martial_status == "Separated"): ?> selected <?php endif; ?>>Separated</option>
														<option value="De facto" <?php if(@$fetchedData->martial_status == "De facto"): ?> selected <?php endif; ?>>De facto</option>
														<option value="Widowed" <?php if(@$fetchedData->martial_status == "Widowed"): ?> selected <?php endif; ?>>Widowed</option>
														<option value="Others" <?php if(@$fetchedData->martial_status == "Others"): ?> selected <?php endif; ?>>Others</option>
													</select>
													<?php if($errors->has('martial_status')): ?>
														<span class="custom-error" role="alert">
															<strong><?php echo e(@$errors->first('martial_status')); ?></strong>
														</span> 
													<?php endif; ?>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="contact_type">
											Contact Type <span style="color:#ff0000;">*</span></label>
											<select style="padding: 0px 5px;" name="contact_type" id="contact_type" class="form-control" data-valid="required">
												<option value="Personal" <?php if(@$fetchedData->contact_type == "Personal"): ?> selected <?php endif; ?>> Personal</option>
												<option value="Office" <?php if(@$fetchedData->contact_type == "Office"): ?> selected <?php endif; ?>>Office</option>
											</select>
											<?php if($errors->has('contact_type')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('contact_type')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="phone">Contact No.<span style="color:#ff0000;">*</span></label>
											<div class="cus_field_input">
												<div class="country_code"> 
													<input class="telephone" id="telephone" type="tel" name="country_code" readonly >
												</div>	
												<?php echo Form::text('phone', @$fetchedData->phone, array('class' => 'form-control tel_input', 'data-valid'=>'required', 'autocomplete'=>'off','placeholder'=>'' )); ?>

												<?php if($errors->has('phone')): ?>
													<span class="custom-error" role="alert">
														<strong><?php echo e(@$errors->first('phone')); ?></strong>
													</span> 
												<?php endif; ?>
											</div>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="email_type">
											Email Type <span style="color:#ff0000;">*</span></label>
											<select style="padding: 0px 5px;" name="email_type" id="email_type" class="form-control" data-valid="required">
												<option value="Personal" <?php if(@$fetchedData->email_type == "Personal"): ?> selected <?php endif; ?>> Personal</option>
												<option value="Business" <?php if(@$fetchedData->email_type == "Business"): ?> selected <?php endif; ?>>Business</option>
											</select>
											<?php if($errors->has('email_type')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('email_type')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="email">Email <span style="color:#ff0000;">*</span></label>
											<?php echo Form::text('email', @$fetchedData->email, array('class' => 'form-control', 'data-valid'=>'required', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											<?php if($errors->has('email')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('email')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="att_email">Email </label>
											<?php echo Form::text('att_email', @$fetchedData->att_email, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											<?php if($errors->has('att_email')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('att_email')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div> 
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="att_phone">Phone</label>
											<div class="cus_field_input">
												<div class="country_code"> 
													<input class="telephone" id="telephone" type="tel" name="att_country_code"  readonly >
												</div>	
												<?php echo Form::text('att_phone', @$fetchedData->att_phone, array('class' => 'form-control tel_input', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

												<?php if($errors->has('att_phone')): ?>
													<span class="custom-error" role="alert">
														<strong><?php echo e(@$errors->first('att_phone')); ?></strong>
													</span> 
												<?php endif; ?>
											</div>
										</div>
									</div>	
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="visa_type">Visa Type</label>
											<select class="form-control select2" name="visa_type">
											<option value="">- Select Visa Type -</option>
											<?php $__currentLoopData = \App\Models\VisaType::orderby('name', 'ASC')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visalist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<option <?php if($fetchedData->visa_type == $visalist->name): ?> selected <?php endif; ?> value="<?php echo e($visalist->name); ?>"><?php echo e($visalist->name); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
											</select>
											<?php if($errors->has('visa_type')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('visa_type')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<?php
										$visa_expiry_date = '';
										if($fetchedData->visa_expiry_date != '' && $fetchedData->visa_expiry_date != '0000-00-00'){
											$visa_expiry_date = date('d/m/Y', strtotime($fetchedData->visa_expiry_date));
										}

										?>	
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="visa_expiry_date">Visa Expiry Date</label>
											<div class="input-group">
												<div class="input-group-prepend">
													<div class="input-group-text">
														<i class="fas fa-calendar-alt"></i>
													</div>
												</div>
												<?php echo Form::text('visa_expiry_date', $visa_expiry_date, array('class' => 'form-control dobdatepicker', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'Select Date' )); ?>

												<?php if($errors->has('visa_expiry_date')): ?>
													<span class="custom-error" role="alert">
														<strong><?php echo e(@$errors->first('visa_expiry_date')); ?></strong>
													</span> 
												<?php endif; ?>
											</div>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="preferredIntake">Preferred Intake</label>
											<div class="input-group">
												<div class="input-group-prepend">
													<div class="input-group-text">
														<i class="fas fa-calendar-alt"></i>
													</div>
												</div>
												<?php echo Form::text('preferredIntake', @$fetchedData->preferredIntake, array('class' => 'form-control datepicker', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

												<?php if($errors->has('preferredIntake')): ?>
													<span class="custom-error" role="alert">
														<strong><?php echo e(@$errors->first('preferredIntake')); ?></strong>
													</span> 
												<?php endif; ?>
											</div>
										</div> 
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="country_passport">Country of Passport</label>
											<select class="form-control  select2" name="country_passport" >
											<?php
												foreach(\App\Models\Country::all() as $list){
													?>
													<option <?php if(@$fetchedData->country_passport == $list->sortname){ echo 'selected'; } ?> value="<?php echo e(@$list->sortname); ?>" ><?php echo e(@$list->name); ?></option>
													<?php
												}
												?>
											</select>
											<?php if($errors->has('country_passport')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('country_passport')); ?></strong>
												</span> 
											<?php endif; ?> 
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="passport_no">Passport Number</label>
											<?php echo Form::text('passport_no', @$fetchedData->passport_no, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											<?php if($errors->has('passport_no')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('passport_no')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="address">Address</label>
											<?php echo Form::text('address', $fetchedData->address, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											<?php if($errors->has('address')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('address')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="city">City</label>
											<?php echo Form::text('city', $fetchedData->city, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											<?php if($errors->has('city')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('city')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="state">State</label>
											<select class="form-control" name="state">
												<option value="">- Select State -</option>
												<option value="Australian Capital Territory" <?php if(@$fetchedData->state == "Australian Capital Territory"): ?> selected <?php endif; ?>>Australian Capital Territory</option>
												<option value="New South Wales" <?php if(@$fetchedData->state == "New South Wales"): ?> selected <?php endif; ?>>New South Wales</option>
												<option value="Northern Territory" <?php if(@$fetchedData->state == "Northern Territory"): ?> selected <?php endif; ?>>Northern Territory</option>
												<option value="Queensland" <?php if(@$fetchedData->state == "Queensland"): ?> selected <?php endif; ?>>Queensland</option>
												<option value="South Australia" <?php if(@$fetchedData->state == "South Australia"): ?> selected <?php endif; ?>>South Australia</option>
												<option value="Tasmania" <?php if(@$fetchedData->state == "Tasmania"): ?> selected <?php endif; ?>>Tasmania</option>
												<option value="Victoria" <?php if(@$fetchedData->state == "Victoria"): ?> selected <?php endif; ?>>Victoria</option>
												<option value="Western Australia" <?php if(@$fetchedData->state == "Western Australia"): ?> selected <?php endif; ?>>Western Australia</option>
											</select>
											<?php if($errors->has('state')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('state')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="zip">Post Code</label>
											<?php echo Form::text('zip', $fetchedData->zip, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											<?php if($errors->has('zip')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('zip')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
								</div>
								<hr style="border-color: #000;"/>
								<div class="row">
									<div class="col-sm-4">
										<div class="form-group"> 
											<label for="country">Country</label>
											<select class="form-control select2" name="country" >
											<?php
												foreach(\App\Models\Country::all() as $list){
													?>
													<option <?php if(@$fetchedData->country == $list->sortname){ echo 'selected'; } ?> value="<?php echo e(@$list->sortname); ?>" ><?php echo e(@$list->name); ?></option>
													<?php
												}
												?>
											</select>
											<?php if($errors->has('country')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('country')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-8">
										<div class="form-group"> 
											<label for="related_files">Similar related files</label>
												<select multiple class="form-control js-data-example-ajaxcc" name="related_files[]">
											
												
											</select>
											<?php if($errors->has('related_files')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('related_files')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="nomi_occupation">Nominated Occupation</label>
											<?php echo Form::text('nomi_occupation', $fetchedData->nomi_occupation, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											
											<?php if($errors->has('nomi_occupation')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('nomi_occupation')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="skill_assessment">Skill Assessment</label>
											<select class="form-control" name="skill_assessment">
												<option  value="">Select</option>
												<option <?php if($fetchedData->skill_assessment == 'Yes'): ?> selected <?php endif; ?> value="Yes">Yes</option>
												<option <?php if($fetchedData->skill_assessment == 'No'): ?> selected <?php endif; ?> value="No">No</option>
											</select>
											<?php if($errors->has('skill_assessment')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('skill_assessment')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="high_quali_aus">Highest Qualification in Australia</label>
											<?php echo Form::text('high_quali_aus', $fetchedData->high_quali_aus, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											
											<?php if($errors->has('high_quali_aus')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('high_quali_aus')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="high_quali_overseas">Highest Qualification Overseas</label>
											<?php echo Form::text('high_quali_overseas', $fetchedData->high_quali_overseas, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											
											<?php if($errors->has('high_quali_overseas')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('high_quali_overseas')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group"> 
											<label for="relevant_work_exp_aus">Relevant work experience in Australia</label>
											<?php echo Form::text('relevant_work_exp_aus', $fetchedData->relevant_work_exp_aus, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											
											<?php if($errors->has('relevant_work_exp_aus')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('relevant_work_exp_aus')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group"> 
											<label for="relevant_work_exp_over">Relevant work experience in Overseas</label>
											<?php echo Form::text('relevant_work_exp_over', $fetchedData->relevant_work_exp_over, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

												
											<?php if($errors->has('relevant_work_exp_over')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('relevant_work_exp_over')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group"> 
											<label for="married_partner">If married, English score of partner</label>
											<?php echo Form::text('married_partner', $fetchedData->married_partner, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

												
											<?php if($errors->has('married_partner')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('married_partner')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="naati_py">Naati/PY</label>
												<select class="form-control" name="naati_py">
													<option value="">Select</option>
													<option <?php if($fetchedData->naati_py == 'Naati'): ?> selected <?php endif; ?> value="Naati">Naati</option>
													<option <?php if($fetchedData->naati_py == 'PY'): ?> selected <?php endif; ?> value="PY">PY</option>
											</select>
											<?php if($errors->has('naati_py')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('naati_py')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="total_points">Total Points</label>
											<?php echo Form::text('total_points', $fetchedData->total_points, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

												
											<?php if($errors->has('total_points')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('total_points')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group"> 
											<label for="start_process">When You want to start Process</label>
												<select class="form-control" name="start_process">
													<option value="">Select</option>
													<option <?php if($fetchedData->start_process == 'As soon As Possible'): ?> selected <?php endif; ?> value="As soon As Possible">As soon As Possible</option>
													<option <?php if($fetchedData->start_process == 'In Next 3 Months'): ?> selected <?php endif; ?> value="In Next 3 Months">In Next 3 Months</option>
													<option <?php if($fetchedData->start_process == 'In Next 6 Months'): ?> selected <?php endif; ?> value="In Next 6 Months">In Next 6 Months</option>
													<option <?php if($fetchedData->start_process == 'Advise Only'): ?> selected <?php endif; ?> value="Advise Only">Advise Only</option>
											</select>
											<?php if($errors->has('start_process')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('start_process')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
								</div>
								<hr style="border-color: #000;"/>
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group">
											<label for="service">Service <span style="color:#ff0000;">*</span></label>
												<select class="form-control select2" name="service" data-valid="required">
											<option value="">- Select Lead Service -</option>
													<?php $__currentLoopData = \App\Models\LeadService::orderby('name', 'ASC')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leadservlist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<option <?php if($fetchedData->service == $leadservlist->name): ?> selected <?php endif; ?> value="<?php echo e($leadservlist->name); ?>"><?php echo e($leadservlist->name); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
											</select>
											<?php if($errors->has('service')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('service')); ?></strong>
												</span> 
											<?php endif; ?> 
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="assign_to">Assign To <span style="color:#ff0000;">*</span></label>
											<select style="padding: 0px 5px;" name="assign_to" id="assign_to" class="form-control select2" data-valid="required">
											<option value="">Select User</option>
												<?php
												$admins = \App\Models\Admin::where('role','!=',7)->orderby('first_name','ASC')->get();
												foreach($admins as $admin){
													 $branchname = \App\Models\Branch::where('id',$admin->office_id)->first();
												?>
												<option <?php if(@$fetchedData->assign_to == $admin->id): ?> selected <?php endif; ?> value="<?php echo $admin->id; ?>"><?php echo $admin->first_name.' '.$admin->last_name.' ('.@$branchname->office_name.')'; ?></option>
												<?php } ?>
											</select>
											<?php if($errors->has('assign_to')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('assign_to')); ?></strong>
												</span> 
											<?php endif; ?> 
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="status">Status</label>
											<select style="padding: 0px 5px;" name="status" id="status" class="form-control" data-valid="">
												<option value="">Select Status</option>
												<option value="Unassigned" <?php if(@$fetchedData->status == "Unassigned"): ?> selected <?php endif; ?>>Unassigned</option>
												<option value="Assigned" <?php if(@$fetchedData->status == "Assigned"): ?> selected <?php endif; ?>>Assigned</option>
												<option value="In-Progress" <?php if(@$fetchedData->status == "In-Progress"): ?> selected <?php endif; ?>>In-Progress</option>
												<option value="Closed" <?php if(@$fetchedData->status == "Closed"): ?> selected <?php endif; ?>>Closed</option>
											</select>
											<?php if($errors->has('status')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('status')); ?></strong>
												</span> 
											<?php endif; ?> 
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="lead_quality">Lead Quality <span style="color:#ff0000;">*</span></label>
											<select style="padding: 0px 5px;" name="lead_quality" id="lead_quality" class="form-control" data-valid="required">
												<option value="1" <?php if(@$fetchedData->lead_quality == "1"): ?> selected <?php endif; ?>>1</option>
												<option value="2" <?php if(@$fetchedData->lead_quality == "2"): ?> selected <?php endif; ?>>2</option>
												<option value="3" <?php if(@$fetchedData->lead_quality == "3"): ?> selected <?php endif; ?>>3</option>
												<option value="4" <?php if(@$fetchedData->lead_quality == "4"): ?> selected <?php endif; ?>>4</option>
												<option value="5" <?php if(@$fetchedData->lead_quality == "5"): ?> selected <?php endif; ?>>5</option>
											</select>
											<?php if($errors->has('lead_quality')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('lead_quality')); ?></strong>
												</span> 
											<?php endif; ?> 
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="lead_source">Lead Source <span style="color:#ff0000;">*</span></label>
											<select style="padding: 0px 5px;" name="lead_source" id="lead_source" class="form-control" data-valid="required">
												<option value="">Lead Source</option>
													<?php $__currentLoopData = \App\Models\Source::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sources): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($sources->name); ?>" <?php if(@$fetchedData->lead_source == $sources->name): ?> selected <?php endif; ?>><?php echo e($sources->name); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
											
											</select>
											<?php if($errors->has('lead_source')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('lead_source')); ?></strong>
												</span> 
											<?php endif; ?> 
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group"> 
											<label for="tags_label">Tags/Label </label>
											<?php echo Form::text('tags_label', @$fetchedData->tags_label, array('class' => 'form-control', 'data-valid'=>'', 'autocomplete'=>'off','placeholder'=>'' )); ?>

											<?php if($errors->has('tags_label')): ?>
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('tags_label')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group">
											<label for="comments_note">Comments / Note</label>
											<textarea class="form-control" name="comments_note" placeholder="" data-valid=""><?php echo e(@$fetchedData->comments_note); ?></textarea>
											<?php if($errors->has('comments_note')): ?> 
												<span class="custom-error" role="alert">
													<strong><?php echo e(@$errors->first('comments_note')); ?></strong>
												</span> 
											<?php endif; ?>
										</div>
									</div> 
									<div class="col-sm-12">
										<div class="form-group float-right">
											<?php echo Form::button('Save', ['class'=>'btn btn-primary', 'onClick'=>'customValidate("edit-leads")' ]); ?>

										</div>
									</div>
								</div> 
							</div>
						</div>	
					</div>
				</div>  
			 <?php echo Form::close(); ?>	
		</div>
	</section>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<?php
if($fetchedData->related_files != ''){
    $exploder = explode(',', $fetchedData->related_files);
       foreach($exploder AS $EXP){ 
			$relatedclients = \App\Models\Admin::where('id', $EXP)->first();	
			?>
			<input type="hidden" class="relatedfile" data-id="<?php echo e(@$relatedclients->id); ?>" data-email="<?php echo e(@$relatedclients->email); ?>" data-name="<?php echo e(@$relatedclients->first_name); ?> <?php echo e(@$relatedclients->last_name); ?>">
			<?php
								
}
}
?>
<script>
jQuery(document).ready(function($){
    <?php if($fetchedData->related_files != ''){ ?>
    	var array = [];
	var data = [];
    $('.relatedfile').each(function(){
		
			var id = $(this).attr('data-id');
			 array.push(id);
			var email = $(this).attr('data-email');
			var name = $(this).attr('data-name');
			var status = 'Client';
			
			data.push({
				id: id,
  text: name,
  html:  "<div  class='select2-result-repository ag-flex ag-space-between ag-align-center'>" +

      "<div  class='ag-flex ag-align-start'>" +
        "<div  class='ag-flex ag-flex-column col-hr-1'><div class='ag-flex'><span  class='select2-result-repository__title text-semi-bold'>"+name+"</span>&nbsp;</div>" +
        "<div class='ag-flex ag-align-center'><small class='select2-result-repository__description'>"+email+"</small ></div>" +
      
      "</div>" +
      "</div>" +
	   "<div class='ag-flex ag-flex-column ag-align-end'>" +
        
        "<span class='ui label yellow select2-result-repository__statistics'>"+ status +
          
        "</span>" +
      "</div>" +
    "</div>",
  title: name
				});
	});
	$(".js-data-example-ajaxcc").select2({
  data: data,
  escapeMarkup: function(markup) {
    return markup;
  },
  templateResult: function(data) {
    return data.html;
  },
  templateSelection: function(data) {
    return data.text;
  }
});
	$('.js-data-example-ajaxcc').val(array);
		$('.js-data-example-ajaxcc').trigger('change');
	
	
	<?php } ?>
	
$('.js-data-example-ajaxcc').select2({
		 multiple: true,
		 closeOnSelect: false,
	
		  ajax: {
			url: '<?php echo e(URL::to('/admin/clients/get-recipients')); ?>',
			dataType: 'json',
			processResults: function (data) {
			  // Transforms the top-level key of the response object from 'items' to 'results'
			  return {
				results: data.items
			  };
			  
			},
			 cache: true
			
		  },
	templateResult: formatRepo,
	templateSelection: formatRepoSelection
});
function formatRepo (repo) {
  if (repo.loading) {
    return repo.text;
  }

  var $container = $(
    "<div  class='select2-result-repository ag-flex ag-space-between ag-align-center'>" +

      "<div  class='ag-flex ag-align-start'>" +
        "<div  class='ag-flex ag-flex-column col-hr-1'><div class='ag-flex'><span  class='select2-result-repository__title text-semi-bold'></span>&nbsp;</div>" +
        "<div class='ag-flex ag-align-center'><small class='select2-result-repository__description'></small ></div>" +
      
      "</div>" +
      "</div>" +
	   "<div class='ag-flex ag-flex-column ag-align-end'>" +
        
        "<span class='ui label yellow select2-result-repository__statistics'>" +
          
        "</span>" +
      "</div>" +
    "</div>"
  );

  $container.find(".select2-result-repository__title").text(repo.name);
  $container.find(".select2-result-repository__description").text(repo.email);
  $container.find(".select2-result-repository__statistics").append(repo.status);
 
  return $container;
}

function formatRepoSelection (repo) {
  return repo.name || repo.text;
}
});

  var loadFile = function(event) {
    var output = document.getElementById('output');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.onload = function() {
      URL.revokeObjectURL(output.src); // free memory
	  $('.if_image').hide();
	  $('#output').css({'width':"100px",'height':"100px"});
    }
  };
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bansalcrm\resources\views/Admin/leads/edit.blade.php ENDPATH**/ ?>