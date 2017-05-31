<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Cyclo;
use App\UserBooking;
use App\Points;
use Validator;
use Auth;
use App\Http\Requests;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CycloController extends Controller {

	protected $rules = [
		'name' => 'required|max:255',
		'status' => 'required|boolean',
		// 'number' => 'required',
		// 'start_datetime' =>'required|date_format:Y-m-d',
		// 'end_datetime' => 'required|date_format:Y-m-d',
	];
	
	private function createDateRangeArray($strDateFrom,$strDateTo)
	{
		$aryRange = array();
		$iDateFrom = mktime(1,0,0,substr($strDateFrom,5,2),substr($strDateFrom,8,2),substr($strDateFrom,0,4));
		$iDateTo = mktime(1,0,0,substr($strDateTo,5,2),substr($strDateTo,8,2),substr($strDateTo,0,4));
		if ($iDateTo>=$iDateFrom)
		{
			array_push($aryRange,date('Y-m-d',$iDateFrom));
			while ($iDateFrom<$iDateTo)
			{
				$iDateFrom+=86400;
				array_push($aryRange,date('Y-m-d',$iDateFrom));
			}
		}
		return $aryRange;
	}


	public function getIndex(Request $request) {
		if($request->has('search') && !empty($request->get('search'))){
   			$search = $request->get('search');   			
   			$cyclos = Cyclo::where('name','LIKE',"%{$search}%")->get()->appends(['search' => $search]);
   			// $cyclos = Cyclo::where('name','LIKE',"%{$search}%")->orWhere('number','LIKE',"%{$search}%")->paginate(15);
		}else{
   			$cyclos = Cyclo::orderBy('id', 'DESC')->get();
   		}
   		$title = "Cyclo";
   		return View('admin.cyclo.cyclo', compact('cyclos', 'title','search'));
   	}

   	public function getAvailability(Request $request) {
		$title = "Cyclo";
		$cyclos = Cyclo::where('status', 1)->get();
		$cyc_ids = [];
		$all = 0;
		$avec_cyclo = Cyclo::whereRaw('cy_type=? AND status=?', [0, 1])->first();
		if(! $avec_cyclo) return redirect('/admin')->withError('Cyclo introuvable!!');
		if(!$cyclos->isEmpty()){
			$start = date('Y-m-d', strtotime('-6 months'));
	   		$end = date('Y-m-d', strtotime('+6 months'));
	   		$hash_timestamp = time();
	   		if($request->has('resource_id') && !empty($request->get('resource_id'))){
	   			$cy =  Cyclo::find($request->get('resource_id')); 
	   			if(! $cy) return redirect('/admin')->withError('Cyclo introuvable!!');
	   			$res_id = $cy->resource_id;
	   			$vacation_resource = $res_id;
	   			$cyc_ids[] = $res_id;
	   			$quantity = $cy->quantity;
	   		}else{
	   			$cy = $avec_cyclo;
	   			$res_id = $cy->resource_id;
	   			$vacation_resource = $res_id;
	   			$cyc_ids[] = $res_id;
	   			$quantity = $cy->quantity;
	   			/* --all cyclo results -- */
	   			/*$cy = null;
   			 	$res_id = null;	
   			 	$vacation_resource = 'all';
				$cyc_ids = $cyclos->pluck('resource_id')->toArray();
				$quantity = $cyclos->sum('quantity');
				$all = 1;*/
	   		}
	   		/*-- reservations listing --*/
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://www.planyo.com/rest/?method=list_reservations&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "list_reservations"),
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => array(
					'resource_id' => $res_id,
					'start_time' => $start,
					'end_time' => $end,
					'sort' => 'start_time',
					'excluded_status' => '24',
					'site_id'=> env('SITE_ID1'),
					'language'=>'FR',
				)
			));
			$resv = curl_exec($curl);
			curl_close($curl);
			$resp = json_decode($resv, true);
			/*-- vacation listing --*/
			$curl1 = curl_init();
			curl_setopt_array($curl1, array(
				CURLOPT_URL => "https://www.planyo.com/rest/?method=list_vacations&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "list_vacations"),
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => array(
					'resource_id' => $vacation_resource,
					'start_time' => $start,
					'end_time' => $end,
					'type'=> 1,
					'site_id'=> env('SITE_ID1'),
					'vacation_recurrence_type'=>'all',
					'include_site_vacations'=>true,
					'language'=>'FR',
				)
			));
			$resv1 = curl_exec($curl1);
			curl_close($curl1);
			$resp1 = json_decode($resv1, true);
			/*-- resource usage --*/
			$usage_curl = curl_init();
				curl_setopt_array($usage_curl, array(
					CURLOPT_URL => "https://www.planyo.com/rest/?method=get_resource_usage&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "get_resource_usage"),
					CURLOPT_POST => 1,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POSTFIELDS => array(
						'resource_id' => $res_id,
						'start_date' => $start,
						'end_date' => $end,
					)
				));
			$usage_resv = curl_exec($usage_curl);
			curl_close($usage_curl);
			$usage_resp = json_decode($usage_resv, true);

			$count = [];
			$resv_counts = [];
			$vacation_counts = [];
			$avails = [];
			$date_qty = [];
			if( $resp['response_code'] != '0') return redirect('/admin')->withError($resp['response_message']);
			if( $resp1['response_code'] != '0') return redirect('/admin')->withError($resp1['response_message']);
			if( $usage_resp['response_code'] != '0') return redirect('/admin')->withError($usage_resp['response_message']);

			if( $resp['response_code'] == '0' && $resp1['response_code'] == '0' && $usage_resp['response_code'] == '0' ) {
				$results = $resp['data']['results'];
				$results1 = $resp1['data']['results'];
				$usage_results = $usage_resp['data']['usage'];
				if( !empty($results) || $results != null){
					if($all == 0) $bookings = UserBooking::whereRaw('start_datetime >=? AND end_datetime <=? AND resv_status=? AND cyclo_id=?', [$start, $end, 'reserved', $cy->id])->get()->pluck('resv_id')->toArray();
					else $bookings = UserBooking::whereRaw('start_datetime >=? AND end_datetime <=? AND resv_status=?', [$start, $end, 'reserved'])->get()->pluck('resv_id')->toArray();
					foreach ($results as $val) {
						if( in_array($val['reservation_id'], $bookings) ){
							$count[ date('Y-m-d',strtotime($val['start_time'])) ][]= $val;
						}
					}
					foreach ($count as $k => $arr) {
						$resv_counts[$k] = count($arr);
					}
				}				
				if( !empty($results1) || $results1 != null){
					foreach ($results1 as $val1) {
						$v_count[ date('Y-m-d',strtotime($val1['start_time'])) ][]= $val1;
					}
					$vac_qty = 0;
					foreach ($v_count as $d => $vacates) {
						foreach($vacates as $vacate){
							$vac_qty = $vac_qty + $vacate['quantity'];
						}
						$vacation_counts[$d] = $vac_qty;
						$vac_qty = 0;
					}
				}
				if( !empty($usage_results) || $usage_results != null){
					foreach ($cyc_ids as $cycloId) {
						if(array_key_exists($cycloId, $usage_results)){
							$usages = $usage_results[$cycloId];
							foreach ($usages as $Ykey => $year) {
								foreach ($year as $Mkey => $months) {
									if($Mkey < 10) $Mkey = '0'.$Mkey;
									foreach ($months as $key => $month) {
										if($key < 10) $key = '0'.$key;
										$avails[$Ykey.'-'.$Mkey.'-'.$key] = $quantity - $month;
									}
								}
							}
						}
					}
				}				
				foreach ($avails as $a => $avail) {				
					$date_qty[$a]['avails'] = $avail;
				}
				foreach ($vacation_counts as $v => $vacation_count) {
					$date_qty[$v]['vacation_counts'] = $vacation_count;
					if(! array_key_exists('resvd_counts', $date_qty[$v]) ) $date_qty[$v]['resvd_counts'] = 0;
					if(! array_key_exists('avails', $date_qty[$v]) ) $date_qty[$v]['avails'] = $quantity - $date_qty[$v]['resvd_counts'] - $date_qty[$v]['vacation_counts'];
				}
				foreach ($resv_counts as $c => $resv_count) {
					$date_qty[$c]['resvd_counts'] = $resv_count;
					if(! array_key_exists('vacation_counts', $date_qty[$c]) ) $date_qty[$c]['vacation_counts'] = 0;
					if(! array_key_exists('avails', $date_qty[$c]) ) $date_qty[$c]['avails'] = $quantity - $date_qty[$c]['resvd_counts'] - $date_qty[$c]['vacation_counts'];
				}
				foreach ($date_qty as $date => $qty) {
					if(! array_key_exists('resvd_counts', $date_qty[$date]) ) $date_qty[$date]['resvd_counts'] = 0;
					if(! array_key_exists('vacation_counts', $date_qty[$date]) ) $date_qty[$date]['vacation_counts'] = 0;
					if(! array_key_exists('avails', $date_qty[$date]) ) $date_qty[$date]['avails'] = 0;					
				}				
				foreach ($date_qty as $vac_date => $vc_cnt) {
					$date_qty[$vac_date]['vacation_counts'] = $quantity - $vc_cnt['resvd_counts'] - $vc_cnt['avails'];
				}
				$dates = json_encode($date_qty);
				return view('admin.cyclo.availability', compact('cyclos', 'cy', 'title', 'dates', 'quantity'));
			} else return redirect('/admin')->withError('On a rencontré une erreur!');
		} else return redirect('/admin')->withError('Les cyclos ne sont pas encore ajoutés!');
	}

   	public function getAddcyclo() {
		$title = "Ajouter Cyclo";
		return view('admin.cyclo.addcyclo', compact('title'));
	}

	public function postAddcyclo(Request $request){
		$validator = Validator::make($request->all(), $this->rules);
		if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
		
		$slug = SlugService::createSlug(Cyclo::class, 'slug', $request->input('name'));
		// $data = $request->only(['name', 'status', 'number', 'start_datetime','end_datetime','quantity']);
		$data = $request->only(['name', 'status', 'quantity', 'cy_type', 'agent']);
		$data['slug'] = $slug;
		// $num = Cyclo::where('number', '=', $data['number'])->first();
		// if($num) return redirect()->back()->withError('Number already exist!')->withInput();
		// else{
			// if($data['start_datetime'] < $data['end_datetime']){
				$hash_timestamp = time();
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => "https://www.planyo.com/rest/?method=add_resource&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "add_resource"),
					CURLOPT_POST => 1,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POSTFIELDS => array(
						'base_resource_id'=>env('RESOURCE_ID1'),
						'name'=>$data['name'],
						'quantity'=>$data['quantity'],
						'language'=>'FR',
					)
				));
				$resv = curl_exec($curl);
				curl_close($curl);
				$resp = json_decode($resv, true);
				if($resp['response_code'] == '0' || $resp['response_code'] == 0){
					$data['resource_id'] = $resp['data']['new_resource_id'];
					$insert = \App\Cyclo::create($data);
					if($insert) return redirect('/admin/cyclo')->withMessage('Cyclo ajouté avec succès!');
					else return redirect()->back()->withError('On a rencontré une erreur!')->withInput();
				}else return redirect()->back()->withError('On a rencontré une erreur..!')->withInput();
			// }
			// else return redirect()->back()->withError('please check the dates!')->withInput();
		// }
	}

	/*-- delete cyclo --*/
	/*public function getDelete($id){
		$cyclo = Cyclo::find($id);
		if($cyclo){
			$hash_timestamp = time();
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://www.planyo.com/rest/?method=remove_resource&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "remove_resource"),
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => array(
					'resource_name'=>$cyclo->name,
					'resource_id'=>$cyclo->resource_id,
					'parent_site_id'=>env('SITE_ID1'),
					'resource_id_md5'=>md5($cyclo->resource_id),
				)
			));
			$resv = curl_exec($curl);
			curl_close($curl);
			$resp = json_decode($resv, true);
			if( $resp['response_code'] == '0'){
				Points::where('cyclo_id', '=', $cyclo->id)->delete();
				UserBooking::where('cyclo_id', '=', $cyclo->id)->delete();
				$cyclo->delete();
				return redirect('/admin/cyclo')->withMessage("Cyclo supprimés avec succès!");
			}else return redirect('/admin/cyclo')->withError("On a rencontré une erreur!");
		}else return redirect('/admin/cyclo')->withError("Cyclo Pas trouvé!");
	}*/

	public function getEdit($id) {
		$cyclo = Cyclo::find($id);
		$title = "Cyclo modifier";
		if($cyclo) return view('admin.cyclo.editcyclo', compact('cyclo', 'title'));
		else return redirect('/admin/cyclo')->withError("Cyclo Pas trouvé!");
	}

	public function postEdit(Request $request, $id){
		$validator = Validator::make($request->all(), $this->rules);
		if($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
		$cyclo = Cyclo::find($id);
		if($cyclo){
			$data = $request->only(['name', 'status', 'quantity', 'cy_type', 'agent']);
			// $data = $request->only(['name', 'status', 'number', 'start_datetime','end_datetime','quantity']);
			// if($data['start_datetime'] < $data['end_datetime']){
				$hash_timestamp = time();
				$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => "https://www.planyo.com/rest/?method=modify_resource&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "modify_resource"),
						CURLOPT_POST => 1,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_POSTFIELDS => array(
							'resource_id' => $cyclo->resource_id,
							'name' => $data['name'],
							'quantity' => $data['quantity'],
							'language'=>'FR',
						)
					));
				$resv = curl_exec($curl);
				curl_close($curl);
				$resp = json_decode($resv, true);
				if( !$resp['response_code'] == '0') return redirect()->back()->withError('On a rencontré une erreur!')->withInput();
				else{
					$insert = $cyclo->update($data);
		   			if($insert) return redirect('/admin/cyclo')->withMessage('Cyclo mis à jour avec succès!');
					else return redirect()->back()->withError('On a rencontré une erreur!')->withInput();
				}
			// }else return redirect()->back()->withError('please check the dates!')->withInput();
		}else return redirect('/admin/cyclo')->withError("Cyclo Pas trouvé!");
	}

	public function postRemovevacation(Request $request){
		$vac_id = $request->get('vacation_id');
		// return $vac_id;
		$hash_timestamp = time();
		$curl1 = curl_init();
			curl_setopt_array($curl1, array(
				CURLOPT_URL => "https://www.planyo.com/rest/?method=remove_vacation&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "remove_vacation"),
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => array(
					'vacation_id' => $vac_id,
					'language'=>'FR',
				)
			));
		$resv1 = curl_exec($curl1);
		curl_close($curl1);
		$resp = json_decode($resv1, true); 
		if( $resp['response_code'] == '0' ) return response()->json(['success' => true,'message' =>'Vacation Removed Successfully'], 200);
		else return response()->json(['success' => false,'errors' => $resp['response_message']], 400);
	}
	public function postAddvacation(Request $request){
		$cy =  Cyclo::find($request->get('res_id'));
		$date = $request->get('date');
		$start_date = date('Y-m-d', strtotime($date));
		$end_date = date('Y-m-d 23:59:59', strtotime($date));
		// return ['a'=>$cy->resource_id, 'b'=>$start_date, 'c'=>$end_date];
		$hash_timestamp = time();
		$curl1 = curl_init();
			curl_setopt_array($curl1, array(
				CURLOPT_URL => "https://www.planyo.com/rest/?method=add_vacation&api_key=". env('API_KEY1') ."&hash_timestamp=" . $hash_timestamp . "&hash_key=" . md5( env('HASH_KEY1') . $hash_timestamp . "add_vacation"),
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => array(
					'resource_id ' => $cy->resource_id,
					'start_time'=>$start_date,
					'end_time'=>$end_date,
					'site_id '=>env('SITE_ID1'),
					'language'=>'FR',
				)
			));
		$resv1 = curl_exec($curl1);
		curl_close($curl1);
		$resp = json_decode($resv1, true); 
		if( $resp['response_code'] == '0' ) return response()->json(['success' => true,'message' =>'Vacation ajouté avec succès'], 200);
		else return response()->json(['success' => false,'errors' => $resp['response_message']], 400);
	}
}
