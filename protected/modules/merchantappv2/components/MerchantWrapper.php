<?php
class MerchantWrapper
{
	public static function paginateLimit()
	{
		return 10;
	}
	
	public static function dropdownFormat($data=array(),$value='', $label='')
	{
		$list = array();
		$list['']='';
		if(is_array($data) && count($data)>=1){
			foreach ($data as $val) {
				if(isset($val[$value]) && isset($val[$label])){
			 	   $list[ $val[$value] ] = translate($val[$label]);
				}
			}
		}
		return $list;
	}
	
	public static function getMerchantInformation($merchant_id=0)
	{	
		if($merchant_id>0){
			$resp = Yii::app()->db->createCommand()
	          ->select()
	          ->from('{{merchant}}')   
	          ->where("merchant_id=:merchant_id",array(
	             ':merchant_id'=>$merchant_id
	          )) 
	          ->limit(1)
	          ->queryRow();		
	          
	        if($resp){
	        	$resp = Yii::app()->request->stripSlashes($resp);
	        	return $resp;
	        }
		}
        throw new Exception( "Merchant not found" );	
	}
	
	public static function udapteMerchantInfo($params=array(), $merchant_id=0 )
	{
		if(is_array($params) && count($params)>=1 && $merchant_id>0){
			$up =Yii::app()->db->createCommand()->update("{{merchant}}",$params,
	  	    'merchant_id=:merchant_id',
		  	    array(
		  	      ':merchant_id'=>$merchant_id
		  	    )
	  	    );
	  	    if($up){
	  	    	return true;
	  	    } else throw new Exception( "Failed cannot update records" );	
		}
		throw new Exception( "Invalid parameters" );	
	}
	
	public static function insertCuisine($merchant_id='', $cuisine=array())
	{		
		if(!Yii::app()->db->schema->getTable("{{cuisine_merchant}}")){
			return false;
		}
		
		$merchant_id = (integer)$merchant_id;
		
		Yii::app()->db->createCommand("DELETE FROM 
		{{cuisine_merchant}} WHERE merchant_id=".q($merchant_id)." ")->query();
			
		if(is_array($cuisine) && count($cuisine)>=1){
			foreach ($cuisine as $cuisine_id) {
				Yii::app()->db->createCommand()->insert("{{cuisine_merchant}}",array(
				  'merchant_id'=>(integer)$merchant_id,
				  'cuisine_id'=>(integer)$cuisine_id
				));
			}
		}
	}
	
	public static function dashboardMenu()
	{
		$is_location = false;
		if (FunctionsV3::isSearchByLocation()){
			$is_location = true;
		}
		$data = array();
		$data[] = array(
		 'icon'=>"1.png",
		 'label'=>translate("Info"),
		 'page'=>"info.html",
		);
		$data[] = array(
		 'icon'=>"2.png",
		 'label'=>translate("Settings"),
		 'page'=>"merchant_settings.html",
		);
		
		$data[] = array(
		 'icon'=>"2.png",
		 'label'=>translate("Opening"),
		 'page'=>"store_hours.html",
		);
		
		$data[] = array(
		 'icon'=>"3.png",
		 'label'=>translate("All Orders"),
		 'page'=>"all_orders.html",
		);
		
		$data[] = array(
		 'icon'=>"3.png",
		 'label'=>translate("Cancel"),
		 'page'=>"cancel_orders.html",
		);
		
		$data[] = array(
		 'icon'=>"3.png",
		 'label'=>translate("Booking"),
		 'page'=>"all_booking.html",
		);
		
		$data[] = array(
		 'icon'=>"3.png",
		 'label'=>translate("Category"),
		 'page'=>"category.html",
		);
		$data[] = array(
		 'icon'=>"4.png",
		 'label'=>translate("Addon"),
		 'page'=>"addon_list.html",
		);
		$data[] = array(
		 'icon'=>"5.png",
		 'label'=>translate("Addon item"),
		 'page'=>"addon_item_list.html",
		);
		$data[] = array(
		 'icon'=>"6.png",
		 'label'=>translate("Items"),
		 'page'=>"item_list.html",
		);
		$data[] = array(
		 'icon'=>"7.png",
		 'label'=>translate("Ingredients"),
		 'page'=>"ingredients_list.html",
		);
		$data[] = array(
		 'icon'=>"8.png",
		 'label'=>translate("Cooking"),
		 'page'=>"cooking_list.html",
		);
		
		$data[] = array(
		 'icon'=>"3.png",
		 'label'=>translate("Sizes"),
		 'page'=>"size_list.html",
		);
		
		if(!$is_location):
		$data[] = array(
		 'icon'=>"10.png",
		 'label'=>translate("Fee"),
		 'page'=>"shipping_list.html",
		);				
		$data[] = array(
		 'icon'=>"9.png",
		 'label'=>translate("Min. Table"),
		 'page'=>"mintable_list.html",
		);
		endif;
		
		$data[] = array(
		 'icon'=>"10.png",
		 'label'=>translate("Offers"),
		 'page'=>"offers_list.html",
		);
		
		$data[] = array(
		 'icon'=>"2.png",
		 'label'=>translate("Gallery"),
		 'page'=>"gallery_settings.html",
		);
		
		$data[] = array(
		 'icon'=>"2.png",
		 'label'=>translate("Banner"),
		 'page'=>"banner_settings.html",
		);
		
		$data[] = array(
		 'icon'=>"10.png",
		 'label'=>translate("Voucher"),
		 'page'=>"voucher_list.html",
		);
		$data[] = array(
		 'icon'=>"5.png",
		 'label'=>translate("Scheduler"),
		 'page'=>"scheduler_list.html",
		);
		$data[] = array(
		 'icon'=>"9.png",
		 'label'=>translate("Payment"),
		 'page'=>"payment_list.html",
		);
		$data[] = array(
		 'icon'=>"6.png",
		 'label'=>translate("Social"),
		 'page'=>"social_settings.html",
		);
		
		$data[] = array(
		 'icon'=>"2.png",
		 'label'=>translate("Alert"),
		 'page'=>"alert_settings.html",
		);
		
		return $data;
	}
	
	public static function twoFlavorOptions()
	{
		return array(
		  1=>translate("Highest price"),
          2=>translate("Sumup and divided by 2")
		);
	}
	
	public static function tipList()
	{
		return array(
    		   ''=>translate("None"),
	    	   '0.1'=>"10%",
	    	   '0.15'=>"15%",
	    	   '0.2'=>"20%",
	    	   '0.25'=>"25%"    	   
    	    );
	}
	
	public static function voucherType()
	{
		return array(
		  'fixed amount'=>t("fixed amount"),
		  'percentage'=>t("percentage"),
		);
	}
	
	public static function parseTagTify($options_name='')
	{
		$format='';
		$options = getOptionA($options_name);
		if( $json = json_decode($options,true)){
			foreach ($json as $val) {
				$format.=$val['value'].",";
			}
			$format= substr($format,0,-1);
		}
		return $format;
	}
	
	public static function timeListReady()
	{
		$data = array(); $order_estimated_time = array();
		$time = getOptionA('order_estimated_time');
		if ( $json = json_decode($time,true)){
			foreach ($json as $val) {				
				$data[]=array(
				  'value'=>$val['value'],
				  'label'=>translate("[mins] MIN",array(
				   '[mins]'=>$val['value']
				  )),
				);
			}
		}
					
		if(count((array)$data)<=0){
			$data[] = array(
			  'value'=>10,
			  'label'=>"10 MIN"
			);
			$data[] = array(
			  'value'=>20,
			  'label'=>"20 MIN"
			);
			$data[] = array(
			  'value'=>30,
			  'label'=>"30 MIN"
			);
		}
		
		return $data;
	}
	
	public static function reasonDeclineList()
	{
		$data = array(); $order_estimated_time = array();
		$time = getOptionA('decline_reason_list');
		if ( $json = json_decode($time,true)){
			foreach ($json as $val) {				
				$data[]=array(
				  'value'=>$val['value'],
				  'label'=>translate($val['value']),
				);
			}
		}
		
		if(count((array)$data)<=0){
			$data[] = array(
			  'value'=>"Closing early",
			  'label'=>"Closing early"
			);
			$data[] = array(
			  'value'=>"Problem with merchant",
			  'label'=>"Problem with merchant"
			);
			$data[] = array(
			  'value'=>"Out of stock",
			  'label'=>"Out of stock"
			);
		}
		return $data;
	}
	
	public static function merchantSettingsOption()
	{
		$options = array(
			  'order_verification','order_sms_code_waiting','food_option_not_available',
			  'disabled_food_gallery','food_viewing_private','merchant_two_flavor_option',
			  'merchant_tax_number','printing_receipt_width','printing_receipt_size',
			  'free_delivery_above_price','merchant_close_store','merchant_show_time',
			  'merchant_disabled_ordering','merchant_extenal','merchant_enabled_voucher',
			  'merchant_required_delivery_time','merchant_minimum_order','merchant_required_delivery_time',
			  'merchant_minimum_order','merchant_maximum_order','merchant_minimum_order_pickup',
			  'merchant_maximum_order_pickup','merchant_minimum_order_dinein','merchant_maximum_order_dinein',
			  'merchant_packaging_wise','merchant_packaging_charge','merchant_packaging_increment',
			  'merchant_tax','merchant_delivery_charges','merchant_tax_charges',
			  'merchant_opt_contact_delivery','merchant_delivery_estimation','merchant_tax',
			  'merchant_distance_type','merchant_enabled_tip','merchant_tip_default',
			  'merchant_timezone','website_merchant_time_picker_interval','merchant_delivery_miles',
			  'merchant_photo','merchant_photo_bg'
			);
		return $options;
	}
	
	public static function getMerchantSettings($merchant_id=0)
	{
		if($merchant_id>0){
			$options_in='';	$options = self::merchantSettingsOption();		
			foreach ($options as $val) {
				$options_in.= q($val)."," ;
			}
			$options_in = substr($options_in,0,-1);
			$stmt="
			SELECT option_name,option_value
			FROM {{option}} 
			WHERE merchant_id=".q($merchant_id)."
			AND option_name IN (".$options_in.")
			";			
			if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
				return $res;
			}
		}
		throw new Exception( "Failed cannot update record" );	
	}
	
    public static function getAllBankDeposit($merchant_id=0,$start=0, $total_rows=10,$search_string='')
	{
		$and='';
		if(!empty($search_string)){				
			$and=" AND b.first_name LIKE ".q("$search_string%")." ";
		}
		
		$stmt="
		select SQL_CALC_FOUND_ROWS 
		a.id, 
		concat(b.first_name,' ',b.last_name) as customer_name,
		a.branch_code,a.date_of_deposit , a.time_of_deposit, a.amount,
		a.scanphoto,a.status,a.date_created
		FROM
		{{bank_deposit}} a
		left join {{client}} b
		ON
		a.client_id = b.client_id
		
		WHERE a.merchant_id = ".q( (integer)$merchant_id )."		
		$and
		ORDER BY id DESC
		LIMIT $start,$total_rows
		";				
        if($resp = Yii::app()->db->createCommand($stmt)->queryAll()){        	
        	return $resp;
        }
        return false;     
	}				
		
    public static function setMerchantTimezone($merchant_id=''){		
		if($merchant_id>0){			
			$mt_timezone=Yii::app()->functions->getOption("merchant_timezone",$merchant_id);			
	    	if (!empty($mt_timezone)){
	    		Yii::app()->timeZone=$mt_timezone;
	    	}    	
		}
	}
	
	public static function getAllNotification($list_type='',$device_uiid=0,$start=0, $total_rows=10,$search_string='')
	{
		$and='';  $order_by = 'ORDER BY id DESC';
				
		if(!empty($search_string)){							
			$and.="
			AND (
			  a.push_title LIKE ".q("%$search_string%")." 
			  OR
			  a.push_message LIKE ".q("%$search_string%")." 
			)
			";		
		}		
				
		if(!empty($list_type)){
			switch ($list_type) {				
				case "unread":		
				    //$and.=" AND a.is_read='0'";			
					break;									
			}			
		}				
		
		/*SELECT a.id, a.push_type, a.merchant_name, a.device_platform,
		a.device_id, a.device_uiid, a.push_title, a.push_message, a.date_created,
		a.is_read*/
		
		$stmt="
		SELECT a.id, a.push_title, a.push_message, a.date_created,
		a.is_read
		
		FROM {{merchantapp_push_logs}} a
		WHERE a.device_uiid = ".q( $device_uiid )."		
		$and
		$order_by
		LIMIT $start,$total_rows
		";						
		if($resp = Yii::app()->db->createCommand($stmt)->queryAll()){           		    
        	return $resp;
        }        
        return false;     
	}
	
	public static function getViewNotification($list_type='',$device_uiid=0,
	$merchant_id='',
	$start=0, $total_rows=10,$search_string='')
	{
		$and=" AND is_remove='0' ";  $order_by = 'ORDER BY date_created DESC';
				
		if(!empty($search_string)){							
			$and.="
			AND (
			  push_title LIKE ".q("%$search_string%")." 
			  OR
			  push_message LIKE ".q("%$search_string%")." 
			)
			";		
		}		
		
		if(!empty($list_type)){
			switch ($list_type) {				
				case "unread":		
				    $and.=" AND is_read='0'";			
					break;									
			}			
		}						
			
		$stmt="
		SELECT * FROM		
		{{merchantapp_view_notification}}
		WHERE key_id IN (".q($device_uiid).",". q($merchant_id) .")
		$and
		$order_by
		LIMIT $start,$total_rows
		";		
		if($resp = Yii::app()->db->createCommand($stmt)->queryAll()){           		    
        	return $resp;
        }        
        return false;     
	}
	
	
	public static function MarkReadNotification($merchant_id='',$device_uiid='')
	{
		$up = true;
		$params = array(
		  'is_read'=>1, 
		  'date_modified'=>FunctionsV3::dateNow(),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);				
			
		if(Yii::app()->db->createCommand("SELECT id FROM {{merchantapp_push_logs}} 
		WHERE device_uiid=".q($device_uiid)." ")->queryRow()){			
			$up = Yii::app()->db->createCommand()->update("{{merchantapp_push_logs}}",$params,
	  	    'device_uiid=:device_uiid',
		  	    array(
		  	      ':device_uiid'=>$device_uiid
		  	    )
	  	    );
		}
  	    
		if(Yii::app()->db->createCommand("SELECT broadcast_id FROM {{merchantapp_broadcast}} 
			WHERE merchant_id=".q($merchant_id)." ")->queryRow()){			
	  	    $up = Yii::app()->db->createCommand()->update("{{merchantapp_broadcast}}",$params,
	  	    'merchant_id=:merchant_id',
		  	    array(
		  	      ':merchant_id'=>$merchant_id
		  	    )
	  	    );
		}
  	    
  	    if($up){
  	    	return true;
  	    }
  	    throw new Exception( "Failed cannot update record" );	
	}
	
	public static function MarkReadNotificationByID($record_type='',$device_uiid='',$id='')
	{		
		$params = array(
		  'is_read'=>1, 
		  'date_modified'=>FunctionsV3::dateNow(),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);
		
		if($record_type=="push_logs"){
			$up =Yii::app()->db->createCommand()->update("{{merchantapp_push_logs}}",$params,
	  	    'device_uiid=:device_uiid AND id=:id',
		  	    array(
		  	      ':device_uiid'=>$device_uiid,
		  	      ':id'=>$id
		  	    )
	  	    );
		} else {
			$up =Yii::app()->db->createCommand()->update("{{merchantapp_broadcast}}",$params,
	  	    'broadcast_id=:broadcast_id',
		  	    array(
		  	      ':broadcast_id'=>$id		  	      
		  	    )
	  	    );
		}
  	    if($up){
  	    	return true;
  	    }
  	    throw new Exception( "Failed cannot update record" );	
	}
	
	public static function PushRemoveAll($merchant_id='',$device_uiid='')
	{		
		$up = true;
		$params = array(
		  'is_remove'=>1,
		  'date_modified'=>FunctionsV3::dateNow(),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);
		
		if(Yii::app()->db->createCommand("SELECT id FROM {{merchantapp_push_logs}} 
			WHERE device_uiid=".q($device_uiid)." ")->queryRow()){			
			$up = Yii::app()->db->createCommand()->update("{{merchantapp_push_logs}}",$params,
	  	    'device_uiid=:device_uiid',
		  	    array(
		  	      ':device_uiid'=>$device_uiid
		  	    )
	  	    );  	    
		}
  	    
		if(Yii::app()->db->createCommand("SELECT broadcast_id FROM {{merchantapp_broadcast}} 
			WHERE merchant_id=".q($merchant_id)." ")->queryRow()){			
	  	    $up = Yii::app()->db->createCommand()->update("{{merchantapp_broadcast}}",$params,
	  	    'merchant_id=:merchant_id',
		  	    array(
		  	      ':merchant_id'=>$merchant_id
		  	    )
	  	    );  
		}	      	    
  	    
		if($up){						
			return true;
		}				
		
		throw new Exception( "Failed cannot delete records." );	
	}
	
	public static function PushRemoveByID($record_type='',$device_uiid='',$id='')
	{
		$params = array(
		  'is_remove'=>1,
		  'date_modified'=>FunctionsV3::dateNow(),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);
		if($record_type=="push_logs"){			
			$up = Yii::app()->db->createCommand()->update("{{merchantapp_push_logs}}",$params,
	  	    'device_uiid=:device_uiid AND id=:id',
		  	    array(
		  	      ':device_uiid'=>$device_uiid,
		  	      ':id'=>$id
		  	    )
	  	    );  	
	  	    return true; 
		} else {						
			$up = Yii::app()->db->createCommand()->update("{{merchantapp_broadcast}}",$params,
	  	    'broadcast_id=:broadcast_id',
		  	    array(
		  	      ':broadcast_id'=>$id
		  	    )
	  	    );  	 
	  	    return true;
		}
		throw new Exception( "Failed cannot delete records." );	
	}
	
	public static function getOptions($options_name=array(),$merchant_id=0)
	{
		$que='';
		if(is_array($options_name) && count($options_name)>=1){
			foreach ($options_name as $key=>$val) {
				$que.=q($val).",";
			}
			$que = substr($que,0,-1);
		}
		$stmt="
		SELECT option_name,option_value
		FROM {{option}}		
		WHERE option_name IN ($que)
		AND merchant_id=".q($merchant_id)."
		";			
		if($resp = Yii::app()->db->createCommand($stmt)->queryAll()){
			return $resp;
		}
		return false;
	}
	
	public static function getOptionsSettings()
	{
		$data = array();
		$name = array(
		 'order_unattended_minutes','ready_outgoing_minutes','ready_unattended_minutes',
		 'booking_incoming_unattended_minutes','booking_cancel_unattended_minutes','merchantapp_keep_awake',
		 'refresh_order','refresh_cancel_order','refresh_booking','refresh_cancel_booking',
		 'order_failed_status','order_successful_status',
		 'order_incoming_status','order_outgoing_status','order_ready_status'
		);
		if($resp = self::getOptions($name)){
			foreach ($resp as $val) {
				
				switch ($val['option_name']) {
					case "order_failed_status":						
					case "order_successful_status":
					case "order_incoming_status":
					case "order_outgoing_status":
					case "order_ready_status":
						$data[$val['option_name']] = !empty($val['option_value'])? json_decode( stripslashes($val['option_value']) ,true):'';
						break;
				
					default:
						$data[$val['option_name']] = trim($val['option_value']);
						break;
				}
				
			}
		}
		return $data;
	}
	
	public static function FlagList()
	{	
		$list = array()	;
		$path = Yii::getPathOfAlias('webroot')."/protected/modules/".APP_FOLDER."/assets/images/flag";			
		if(file_exists($path)){
		   $list = array_diff(scandir($path), array('..', '.'));		
		}
		return $list;
	}
	
    public static function prettyBadge($status='')
	{
		$$status=strtolower(trim($status));
		if($status=="pending"){
		   return '<span class="badge badge-primary">'.translate($status).'</span>';
		} elseif ( $status=="process" ){
			return '<span class="badge badge-success">'.translate($status).'</span>';
		} elseif ( preg_match("/properly set in/i", $status)){
			return '<span class="badge badge-danger">'.translate($status).'</span>';
		} elseif ( preg_match("/caught/i", $status)){
			return '<span class="badge badge-danger">'.translate($status).'</span>';	
		} elseif ( preg_match("/error/i", $status)){
			return '<span class="badge badge-danger">'.translate($status).'</span>';			
		} elseif ( preg_match("/failed/i", $status)){
			return '<span class="badge badge-danger">'.translate($status).'</span>';		
		} elseif ( preg_match("/no/i", $status)){
			return '<span class="badge badge-secondary">'.translate($status).'</span>';			
		} else {			
		   return '<span class="badge badge-success">'.translate($status).'</span>';
		}
	}			
	
	public static function acceptedBasedTime()
	{
		return array(
		  1=>Yii::t("merchantappv2","based on delivery date"),
		  2=>Yii::t("merchantappv2","based on current time"),
		);
	}
	
	public static function getTimeOpening($merchant_id='')
	{
		$resp = Yii::app()->db->createCommand()
          ->select()
          ->from('{{opening_hours}}')   
          ->where("merchant_id=:merchant_id",array(
             ':merchant_id'=>(integer)$merchant_id             
          )) 
          ->order("id ASC")  
          ->queryALl();		
          
        if($resp){
        	return $resp;
        }
        return false;     
	}
	
}
/*end class*/