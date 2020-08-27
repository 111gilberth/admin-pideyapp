<?php
class OrderWrapper
{
	
	public static function getAllOrder($today_order=true,$order_type='',$merchant_id=0,$start=0, $total_rows=10,$search_string='')
	{
				
		$and='';  $order_by = 'ORDER BY order_id ASC';
		$todays_date = date("Y-m-d");
		
		/*if($today_order){			
			$and.=" AND CAST(a.date_created as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)." ";
		}*/
		
		if(!empty($search_string)){							
			$and.="
			AND (
			  a.order_id = ".q($search_string)."
			  OR
			  b.first_name LIKE ".q("$search_string%")." 
			)
			";
		}		
				
		if(!empty($order_type)){
			switch ($order_type) {				
				case "incoming":
					$stats = self::getStatusFromSettings('order_incoming_status',array('pending','paid'));
					
					$and.=" AND a.status IN ($stats)
					AND request_cancel='2'
					";
					$and.=" AND CAST(a.date_created as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)." ";
					break;
			
				case "outgoing":
					
					$stats = self::getStatusFromSettings('order_outgoing_status',array('accepted','delayed'));
					
					$and.=" AND a.status IN ($stats)
					AND request_cancel='2'
					";		
					$and.=" AND CAST(a.delivery_date as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)." ";
					break;
						
				case "ready":
					$stats = self::getStatusFromSettings('order_ready_status',array('ready for delivery'));
					
				    $and.=" AND a.status IN ($stats)
				    AND request_cancel='2'
				    ";
				    $and.=" AND CAST(a.delivery_date as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)." ";
					break;
					
				case "cancel_order":
					$and.=" AND a.status NOT in ('".initialStatus()."') 
					AND request_cancel = '1'
					";
					break;
					
				case "all":
					$and.=" AND a.status NOT in ('".initialStatus()."') ";
					$order_by = 'ORDER BY order_id DESC';
					break;
					  
				default:
					break;
			}			
		}		
				
		$stmt="
		select SQL_CALC_FOUND_ROWS 
		a.order_id, a.merchant_id,a.total_w_tax as total_order_amount,
		a.status,a.status as status_raw,a.trans_type, a.trans_type as trans_type_raw,
		a.date_created,
		a.delivery_date,a.delivery_time,
		a.request_cancel,
		a.date_modified,
		concat(b.first_name,' ',b.last_name) as customer_name,	
		b.estimated_time, b.estimated_date_time,
		(
		 select count(*) from {{order_details}}
		 where order_id = a.order_id
		) as total_items
		
		FROM
		{{order}} a
		left join {{order_delivery_address}} b
		ON
		a.order_id = b.order_id
				
		WHERE a.merchant_id = ".q( (integer)$merchant_id )."		
		$and
		$order_by
		LIMIT $start,$total_rows
		";		
        if($resp = Yii::app()->db->createCommand($stmt)->queryAll()){           		    
        	return $resp;
        }        
        return false;     
	}				
		
	public static function getActionStatus($action='')
	{
		$status='';
		switch ($action) {
			case "accept":				
				$order_action_accepted_status = getOptionA('order_action_accepted_status');
				$status = !empty($order_action_accepted_status)?$order_action_accepted_status:'accepted';
				break;
			case "ready_for_delivery":
				$status = 'ready for delivery';
			    break;
			    
			case "decline_order":
			case "decline":	
				//$status = 'decline';
				$stats = getOptionA('order_action_decline_status');
				$status = !empty($stats)?$stats:'decline';
				break;
				
			case "cancel_order":	
			    //$status = 'cancelled';
			    $stats = getOptionA('order_action_cancel_status');
				$status = !empty($stats)?$stats:'cancelled';
				break;
			
			case "food_is_done":
				//$status = 'ready for delivery';
				$stats = getOptionA('order_action_food_done_status');
				$status = !empty($stats)?$stats:'ready for delivery';
				break;
				
			case "delay_order":	
			    //$status = 'delayed';
			    $stats = getOptionA('order_action_delayed_status');
				$status = !empty($stats)?$stats:'delayed';
			    break;
			    
			case "complete_order":	
			    //$status = 'completed';
			    $stats = getOptionA('order_action_completed_status');
				$status = !empty($stats)?$stats:'completed';
			    break;
			        
			case "approved_cancel_order":    
			   //$status = 'cancelled';
			   $stats = getOptionA('order_action_approved_cancel_order');
			   $status = !empty($stats)?$stats:'cancelled';
			   break;
			   
			case "decline_cancel_order":
				//$status = 'pending';
				$stats = getOptionA('order_action_decline_cancel_order');
			   $status = !empty($stats)?$stats:'pending';
				break;  
			   
			default:
				$status = 'unknow status';
				break;
		}
		return $status;
	}
	
	public static function validateOrder($merchant_id='',$order_id='')
	{
		$resp = Yii::app()->db->createCommand()
          ->select('order_id,delivery_date,delivery_time')
          ->from('{{order}}')   
          ->where("merchant_id=:merchant_id AND order_id=:order_id",array(
             ':merchant_id'=>$merchant_id,
             ':order_id'=>$order_id,
          )) 
          ->limit(1)
          ->queryRow();		
          
        if($resp){
        	return $resp;
        }
        return false;     
	}
	
	public static function getOrder($merchant_id='',$order_id='')
	{
		$resp = Yii::app()->db->createCommand()
          ->select('order_id,status')
          ->from('{{order}}')   
          ->where("merchant_id=:merchant_id AND order_id=:order_id",array(
             ':merchant_id'=>$merchant_id,
             ':order_id'=>$order_id,
          )) 
          ->limit(1)
          ->queryRow();		
          
        if($resp){
        	return $resp;
        }
         throw new Exception( "Order id not found" );
	}
	
	public static function updateOrderHistory($order_id='',$merchant_id,$params=array(),$params2=array())
	{		
		if($order_id>0){
			if(self::validateOrder($merchant_id,$order_id)){
				if(Yii::app()->db->createCommand()->insert("{{order_history}}",$params)){
					$up =Yii::app()->db->createCommand()->update("{{order}}",$params2,
			  	    'order_id=:order_id',
				  	    array(
				  	      ':order_id'=>$order_id
				  	    )
			  	    );
			  	    return true;
				} else throw new Exception( "Failed cannot insert records" );
			} else throw new Exception( "Order id not found" );
		}
		throw new Exception( "Invalid order id" );
	}
	
	public static function orderStatusList($merchant_id='')
	{
		$data = array();
		$stmt ="
		SELECT description 
		FROM {{order_status}}
		WHERE merchant_id IN ('0',".q($merchant_id).")
		ORDER  BY description ASC
		";
		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			foreach ($res as $val) {
				$data[]=array(
				  'value'=>$val['description'],
				  'label'=>t($val['description']),
				);
			}
			return $data;
		}
		return false;
	}
	
	public static function AllOrderStatus()
	{
		$data = array();
		$stmt ="
		SELECT description 
		FROM {{order_status}}		
		ORDER  BY description ASC
		";
		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			return $res;
		}
		return false;
	}
	
	public static function updateEstimationTime($order_id='',$estimation_delay=0)
	{
		$resp = Yii::app()->db->createCommand()
          ->select('order_id,estimated_time')
          ->from('{{order_delivery_address}}')   
          ->where("order_id=:order_id",array(
             ':order_id'=>(integer)$order_id
          )) 
          ->limit(1)
          ->queryRow();		
          
        if($resp){
        	//$estimated_time = $resp['estimated_time'] + (float) $estimation_delay;           	
        	$estimated_date_time = date("Y-m-d H:i:s", strtotime("+$estimation_delay minutes"));
        	$params = array( 
        	  //'estimated_time'=>$estimated_time,
        	  'estimated_date_time'=>$estimated_date_time,
        	  'ip_address'=>$_SERVER['REMOTE_ADDR']
        	);        	
        	Yii::app()->db->createCommand()->update("{{order_delivery_address}}",$params,
	  	    'order_id=:order_id',
		  	    array(
		  	      ':order_id'=>(integer)$order_id
		  	    )
	  	    );
	  	    return true;
        }
        return false;     
	}
	
	public static function getReceiptByID($order_id=0, $client_id=0)
	{
		$and='';
		$order_id = (integer)$order_id;
		$client_id = (integer)$client_id;
		if($client_id>0){
			$and=" AND a.client_id=".q($client_id)."  ";
		}	
		$stmt="
		SELECT a.*,
		concat(b.first_name,' ',b.last_name) as full_name,
		b.location_name,
		concat(b.street,' ',b.area_name,' ',b.city,' ',b.state,' ',b.zipcode) as full_address,
		b.contact_phone,
		b.contact_phone as customer_phone,
		b.opt_contact_delivery,
		b.contact_email as email_address,
		b.contact_email as customer_email,
		b.estimated_time, b.estimated_date_time,
		b.google_lat as location_lat, b.google_lng as location_lng,
		
		c.restaurant_name as merchant_name,
		c.restaurant_phone as merchant_contact_phone
		
		FROM {{order}} a
		left join {{order_delivery_address}} b
		on
		a.order_id = b.order_id
		
		left join {{merchant}} c
		on
		a.merchant_id = c.merchant_id
		
		WHERE
		a.order_id=".q($order_id)."
		$and
		LIMIT 0,1
		";			
		if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
			/*FIXED OLD DATA*/
			if(empty( trim($res['full_name']) )){				
				$stmt2 = "
				select 
				concat(first_name,' ',last_name) as full_name,
				contact_phone
				
				from {{client}}
				where client_id = ".q($res['client_id'])."
				";
				if($res2 = Yii::app()->db->createCommand($stmt2)->queryRow()){
					$res['full_name'] = $res2['full_name'];
					$res['contact_phone'] = $res2['contact_phone'];
				}
			}		
			return $res;
		}
		return false;
	}
	
	public static function prepareReceipt($order_id='')
	{
		$details_details = array(); $data = array(); $order_details=array();
		if ($data=self::getReceiptByID($order_id)){
							
			$merchant_id=$data['merchant_id'];
			$json_details=!empty($data['json_details'])?json_decode($data['json_details'],true):false;

			if ( $json_details !=false){
				Yii::app()->functions->displayOrderHTML(array(
			   'order_id'=>$order_id,
			   'merchant_id'=>$data['merchant_id'],
			   'delivery_type'=>$data['trans_type'],
			   'delivery_charge'=>$data['delivery_charge'],
			   'packaging'=>$data['packaging'],
			   'cart_tip_value'=>$data['cart_tip_value'],
			   'cart_tip_percentage'=>$data['cart_tip_percentage']/100,
			   'card_fee'=>$data['card_fee'],
			   'tax'=>$data['tax'],
			   'points_discount'=>isset($data['points_discount'])?$data['points_discount']:'' /*POINTS PROGRAM*/,
			   'voucher_amount'=>$data['voucher_amount'],
			   'voucher_type'=>$data['voucher_type']
			  ),$json_details,true,$order_id);
			}		
			
					
			$details_details['order_id']=$data['order_id'];
			$details_details['customer_name']=$data['full_name'];			
			$details_details['rfc']=$data['rfc'];			
			$details_details['razonSocial']=$data['razonSocial'];			
			$details_details['date_created']=FunctionsV3::prettyDate($data['date_created'])." ".FunctionsV3::prettyTime($data['date_created']);			
			$details_details['payment_type']=self::prettyPaymentType($data['payment_type'],$data['trans_type']);			
			$details_details['trans_type'] = t($data['trans_type']);
			$details_details['trans_type_raw'] = $data['trans_type'];
			
			$details_details['status']=t($data['status']);
			$details_details['status_raw']=$data['status'];
			
						
			$details_details['estimated_time']=$data['estimated_time'];
			$details_details['estimated_date_time']=$data['estimated_date_time'];
			
			$details_details['sub_total']=$data['sub_total'];
			$details_details['total']=FunctionsV3::prettyPrice($data['total_w_tax']);
			$details_details['delivery_address']=$data['full_address'];
			$details_details['location_lat']=!empty($data['location_lat'])?$data['location_lat']:'';
			$details_details['location_lng']=!empty($data['location_lng'])?$data['location_lng']:'';
			
			$details_details['contact_phone']=$data['contact_phone'];
			$details_details['delivery_date']=FunctionsV3::prettyDate($data['delivery_date']);
			$details_details['delivery_time']=FunctionsV3::prettyTime($data['delivery_time']);
			$details_details['delivery_asap']=$data['delivery_asap'];
			$details_details['delivery_instruction']=$data['delivery_instruction'];
			$details_details['location_name']=$data['location_name'];
			$details_details['order_change_raw']=$data['order_change'];
			$details_details['order_change']=FunctionsV3::prettyPrice($data['order_change']);
			
			$details_details['payment_gateway_ref']=$data['payment_gateway_ref'];
			$details_details['dinein_number_of_guest']=$data['dinein_number_of_guest'];
			$details_details['dinein_special_instruction']=$data['dinein_special_instruction'];
			$details_details['dinein_table_number']=$data['dinein_table_number'];
			
			$details_details['opt_contact_delivery']=$data['opt_contact_delivery'];
			if($data['opt_contact_delivery']>=1){
				$details_details['opt_contact']=array(
				  'label'=>translate("Delivery options"),
				  'value'=>translate("Leave order at the door or gate")
				);
			}
			
			$raw = Yii::app()->functions->details['raw'];
			
			foreach ($raw['item'] as $item){
				$price = $item['normal_price']; $qty = $item['qty']; $addon_total=0;
				if($item['discount']>0){
   	               $price = $item['discounted_price']; 
                }
                $item_total = (integer)$qty* (float) $price; 
                
                $item_name=''; $line_items = array(); 
                
                if(!empty($item['size_words'])){
			   	   $item_name = translate("[item_name] ([size_words])",array(
			   	     '[item_name]'=>$item['item_name'],
			   	     '[size_words]'=>$item['size_words'],
			   	   ));
			    } else $item_name = $item['item_name'];
			    
			    /*SUB*/			    
			    $line_sub_item=array(); $subitem =array();
			    if(isset($item['sub_item'])){
			    	if(is_array($item['sub_item']) && count($item['sub_item'])>=1){
			    		foreach ($item['sub_item'] as $sub_item){					    			
			    			$sub_item_total = (float)$sub_item['addon_qty']*(float)$sub_item['addon_price'];
			    			$subitem[] = array(
			    			  'name'=>$sub_item['addon_name'],
			    			  'qty'=>$sub_item['addon_qty'],
			    			  'price'=>FunctionsV3::prettyPrice($sub_item['addon_price']),
			    			  'qty'=>$sub_item['addon_qty'],
			    			  'sub_item_total'=>FunctionsV3::prettyPrice($sub_item_total),
			    			);			    						    			
			    		}			    		
			    		$line_sub_item[]= array(
		    			  'addon_category'=>$sub_item['addon_category'],
		    			  'item'=>$subitem
		    			);
			    	}
			    }
                			    
			    $item_total_price = (float)$qty*(float)$price;
			    
			    $line_items[]=array(
			      'name'=>$item_name,
			      'qty'=>$qty,
			      'price'=>FunctionsV3::prettyPrice($item['normal_price']),
			      'discount'=>$item['discount'],
			      'price_after_discount'=>FunctionsV3::prettyPrice($price),
			      'item_total_price'=>FunctionsV3::prettyPrice($item_total_price),
			      'cooking_ref'=>$item['cooking_ref'],
			      'order_notes'=>$item['order_notes'],
			      'ingredients'=>$item['ingredients'],
			      'sub_item'=>$line_sub_item
			    );
			    
                $order_details[]=array(
                  'category_name'=>$item['category_name'],
                  'item'=>$line_items
                );
			}			
			
			/*TOTAL*/
			$total_details = array();
			$total = $raw['total'];
			
			if($total['less_voucher']>0){
				$total_details['less_voucher']=FunctionsV3::prettyPrice($total['less_voucher']);
			}
			if($total['pts_redeem_amt']>0){
				$total_details['pts_redeem_amt']=FunctionsV3::prettyPrice($total['pts_redeem_amt']);
			}
			if($total['subtotal']>0){
				$total_details['subtotal']=FunctionsV3::prettyPrice($total['subtotal']);
			}
			if($total['delivery_charges']>0){
				$total_details['delivery_charges']=FunctionsV3::prettyPrice($total['delivery_charges']);
			}
			if($total['merchant_packaging_charge']>0){
				$total_details['packaging_charge']=FunctionsV3::prettyPrice($total['merchant_packaging_charge']);
			}
			if($total['taxable_total']>0){
				$total_details['tax']=array(
				  'taxable_total'=>FunctionsV3::prettyPrice($total['taxable_total']),
				  'tax_label'=>translate("Tax [tax]%",array('[tax]'=> normalPrettyPrice($total['tax']*100) ))
				);
			}
			if($total['tips_percent']>0){
				$total_details['tips']=array(
				  'value'=>FunctionsV3::prettyPrice($total['tips']),
				  'label'=>translate("Tips [tips]",array('[tips]'=> $total['tips_percent'] ))
				);
			}
			if($total['total']>0){
				$total_details['total'] = FunctionsV3::prettyPrice($total['total']);
			}
						
			return array(
			  'order_data'=>$details_details,
			  'order_details'=>$order_details,
			  'total_details'=>$total_details
			);
			
		} else throw new Exception( t("order not found"));
	}
	
	public static function prettyPaymentType($code='',$transaction_type='')
	{
		$list = FunctionsV3::PaymentOptionList();
		if(array_key_exists($code,$list)){
			switch ($transaction_type) {
				case "pickup":			
				    $data= translate("Cash on pickup");				    
					break;
			
				case "dinein":							
				    $data= translate("Pay in person");		   
					break;
						
				default:
					$data = translate($list[$code]);
					break;
			}			
		}
		return $data;
	}
	
	public static function getStatusFromSettings($option_name='', $status = array())
	{
		$status_new = '';
		$order_incoming_status = getOptionA($option_name);
		if(!empty($order_incoming_status)){
			if($order_incoming_status = json_decode($order_incoming_status,true)){
			   $status = $order_incoming_status;
			}
		}
		
		if(is_array($status) && count($status)>=1){
			foreach ($status as $val) {
				$status_new.=q($val).",";
			}
			$status_new = substr($status_new,0,-1);
		}		
		return $status_new;		
	}
	
	public static function getNewestOrder($order_ids='',$merchant_id='')
	{		
		$todays_date = date("Y-m-d");
		$status = self::getStatusFromSettings('order_incoming_status',array('pending','paid'));
		if(!empty($order_ids)){
			$stmt="
			SELECT order_id 
			FROM {{order}}				
			WHERE CAST(date_created as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)."			
			AND order_id NOT IN ($order_ids)
			AND status IN ($status)
			AND request_cancel='2'
			AND merchant_id = ".q($merchant_id)."
			LIMIT 0,1
			";					
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
				return true;
			}
		} else {			
			$stmt="
			SELECT order_id 
			FROM {{order}}				
			WHERE CAST(date_created as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)."			
			AND status IN ($status)
			AND request_cancel='2'
			AND merchant_id = ".q($merchant_id)."
			LIMIT 0,1
			";							
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){				
				return true;
			}
		}
		return false;
	}
	
	public static function reheckNewestOrder($order_ids='',$merchant_id='')
	{
		$todays_date = date("Y-m-d");
		$status = self::getStatusFromSettings('order_incoming_status',array('pending','paid'));
		if(!empty($order_ids)){
			$stmt="
			SELECT order_id 
			FROM {{order}}				
			WHERE CAST(date_created as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)."			
			AND order_id IN ($order_ids)
			AND status IN ($status)
			AND request_cancel='2'
			AND merchant_id = ".q($merchant_id)."
			LIMIT 0,1
			";					
			if(!$res = Yii::app()->db->createCommand($stmt)->queryRow()){
				return true;
			}
		}
		return false;
	}
	
	public static function getNewestCancel($order_ids='',$merchant_id='')
	{
		if(!empty($order_ids)){
			$stmt="
			SELECT order_id 
			FROM {{order}}	
			WHERE order_id NOT IN ($order_ids)
			AND request_cancel='1'
		    AND request_cancel_viewed = '2'
		    AND request_cancel_status='pending'
		    AND merchant_id = ".q($merchant_id)."
			LIMIT 0,1
			";						
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
				return true;
			}
		} else {
			$todays_date = date("Y-m-d");
			$stmt="
			SELECT order_id 
			FROM {{order}}				
			WHERE 1
			AND request_cancel='1'
		    AND request_cancel_viewed = '2'
		    AND request_cancel_status='pending'
		    AND merchant_id = ".q($merchant_id)."
			LIMIT 0,1
			";						
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){				
				return true;
			}
		}
		return false;
	}
	
	public static function verifyNewestOrder($order_ids='')
	{		
		if(!empty($order_ids)){
			$stmt="
			SELECT order_id 
			FROM {{order}}	
			WHERE order_id IN ($order_ids)
			LIMIT 0,1
			";			
			if($res = Yii::app()->db->createCommand($stmt)->queryRow()){
				return true;
			}
		} else {
			//echo 'd2';
		}
		return false;
	}
	
	public static function getOrderHistory($order_id=0)
	{
		$resp = Yii::app()->db->createCommand()
          ->select('order_id,status,remarks,date_created,remarks2,remarks_args')
          ->from('{{order_history}}')   
          ->where("order_id=:order_id",array(
             ':order_id'=>(integer)$order_id,             
          )) 
          ->order('id asc')              
          ->queryAll();		
          
        if($resp){
        	return $resp;
        }
        return false;     
	}
	
	public static function prettyDateTime($date_time='')
	{
		if(!empty($date_time)){
		   return FunctionsV3::prettyDate($date_time)." ".FunctionsV3::prettyTime($date_time);
		}
		return '-';
	}
	
	public static function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
	{
	    $datetime1 = date_create($date_1);
	    $datetime2 = date_create($date_2);	   
	    $interval = date_diff($datetime1, $datetime2);	   
	    return $interval->format($differenceFormat);	  
	}
	
	public static function InsertOrderTrigger($order_id='',$status='',$remarks='',$trigger_type='order')
	{
		$order_id  = (integer) $order_id;
		if(Yii::app()->db->schema->getTable("{{merchantapp_order_trigger}}")){
			$lang=Yii::app()->language; 
			if($order_id>0){			
				$stmt="SELECT order_id FROM
				{{merchantapp_order_trigger}}
				WHERE
				order_id=".FunctionsV3::q($order_id)."
				AND order_status=".q($status)."
				AND status='pending'			
				LIMIT 0,1
				";	
				if(!$res = Yii::app()->db->createCommand($stmt)->queryRow()){	
					$params = array(
					  'order_id'=>$order_id,
					  'order_status'=>$status,
					  'remarks'=>$remarks,
					  'language'=>$lang,
					  'date_created'=>FunctionsV3::dateNow(),
					  'ip_address'=>$_SERVER['REMOTE_ADDR'],
					  'trigger_type'=>$trigger_type
					);					
					Yii::app()->db->createCommand()->insert("{{merchantapp_order_trigger}}",$params);					
					self::consumeUrl(FunctionsV3::getHostURL().Yii::app()->createUrl("merchantappv2/cron/trigger_order"));	
					if($trigger_type=="booking"){
					   self::consumeUrl(FunctionsV3::getHostURL().Yii::app()->createUrl("merchantappv2/cron/trigger_order_booking"));
					}
				}
			}
		}
	}	
	
	
	public static function consumeUrl($url='')
	{		
		$is_curl_working = true;
		$ch = curl_init();
	 	curl_setopt($ch, CURLOPT_URL, $url);
	 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 	$result = curl_exec($ch);
	 	if (curl_errno($ch)) {		    
		    $is_curl_working = false;
		}
	 	curl_close($ch);
	 	
	 	if(!$is_curl_working){
	 		 $response = @file_get_contents($url);		 	 
		 	 if (isset($http_response_header)) {
		 	 	if (!in_array('HTTP/1.1 200 OK',(array)$http_response_header) && !in_array('HTTP/1.0 200 OK',(array)$http_response_header)) {
		 	 		//
		 	 	}
		 	 }
	 	}
	}
	
}
/*end class*/