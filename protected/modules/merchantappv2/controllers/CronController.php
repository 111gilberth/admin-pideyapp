<?php
class CronController extends CController
{

	public function __construct()
	{				
		$website_timezone=getOptionA('website_timezone');
	    if (!empty($website_timezone)){
	 	   Yii::app()->timeZone=$website_timezone;
	    }		
	}		
	
	public function actionIndex()
	{
		echo 'cron is working';
	}
	
	public function actiontrigger_order()
	{
		dump("cron start..");
        define('LOCK_SUFFIX', APP_FOLDER.'_trigger_order');		
        if(($pid = cronHelper::lock()) !== FALSE):

		$sitename = getOptionA('website_title'); $siteurl=websiteUrl();
		$pattern = array('order_id','customer_name','restaurant_name','total_amount');
		
		$stmt="
		SELECT a.trigger_id, a.trigger_type, a.order_id, a.order_status as template_type,
		a.remarks as trigger_remarks, a.language, a.status as status_trigger,
		b.merchant_id,b.customer_name, b.profile_customer_name, b.restaurant_name,b.total_amount						
		
		FROM {{merchantapp_order_trigger}} a	
		LEFT JOIN {{view_order}} b
		ON
		a.order_id = b.order_id
				
		WHERE a.status='pending'
		AND trigger_type='order'
		LIMIT 0,10
		";		
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			$resp = Yii::app()->request->stripSlashes($resp);
			foreach ($res as $val) {				
				$trigger_id = $val['trigger_id'];  $status_trigger='process';
				$template_type = trim($val['template_type']);				
				try {
					
					$tpl = CustomerNotification::getNotificationTemplate($template_type,$val['language'],'push',false);	
					$push_title = $tpl['push_title']; 
					$push_content = $tpl['push_content']; 
										
					foreach ($pattern as $pattern_val) {
						$data[$pattern_val]=isset($val[$pattern_val])?$val[$pattern_val]:'';
					}
					$data['sitename']=$sitename;					
					$data['siteurl']=$siteurl;
					
					$push_title = FunctionsV3::replaceTags($push_title,$data);
					$push_content = FunctionsV3::replaceTags($push_content,$data);	
					
					$params = array(
					 'merchant_id'=>(integer)$val['merchant_id'],
					 'merchant_name'=>$val['restaurant_name'],
					 'order_id'=>$val['order_id'],
					 'push_title'=>$push_title,
					 'push_message'=>$push_content,
					 'topics'=>CHANNEL_TOPIC.$val['merchant_id'],
					 'date_created'=>FunctionsV3::dateNow(),
					 'ip_address'=>$_SERVER['REMOTE_ADDR'],
					);									
					Yii::app()->db->createCommand()->insert("{{merchantapp_broadcast}}",$params);					
				} catch (Exception $e) {
			        $status_trigger = $e->getMessage();
		        }		
		        	
		        $params_update = array(
		          'status'=>$status_trigger,
		          'date_process'=>FunctionsV3::dateNow(),
		          'ip_address'=>$_SERVER['REMOTE_ADDR']
		        );				        
		        Yii::app()->db->createCommand()->update("{{merchantapp_order_trigger}}",$params_update,
		  	    'trigger_id=:trigger_id',
			  	    array(
			  	      ':trigger_id'=>$trigger_id
			  	    )
		  	    );
			} /*end foreach */
			
			OrderWrapper::consumeUrl(FunctionsV3::getHostURL().Yii::app()->createUrl("merchantappv2/cron/processbroadcast"));
			
		}		
		
		cronHelper::unlock();
        endif;	
        dump("cron end..");		
	}
	
	public function actiontrigger_order_booking()
	{
		dump("cron start..");
        define('LOCK_SUFFIX', APP_FOLDER.'_trigger_order_booking');		
        if(($pid = cronHelper::lock()) !== FALSE):

		$sitename = getOptionA('website_title'); $siteurl=websiteUrl();
		
		$pattern = array('booking_id','restaurant_name','number_guest','date_booking',
		'time','customer_name','email','mobile','instruction','status');
		
		$data = array();
		
		$stmt="
		SELECT a.trigger_id, a.trigger_type, a.order_id, a.order_status as template_type,
		a.remarks as trigger_remarks, a.language, a.status as status_trigger,
		b.booking_id, b.merchant_id, b.number_guest, b.date_booking , b.booking_time as time,
		b.booking_name as customer_name, b.email , b.mobile, b.booking_notes as instruction,
		b.status,
		c.restaurant_name 
		
		FROM {{merchantapp_order_trigger}} a	
		LEFT JOIN {{bookingtable}} b
		ON
		a.order_id = b.booking_id
		
		LEFT JOIN {{merchant}} c
		ON
		b.merchant_id = c.merchant_id
				
		WHERE a.status='pending'
		AND trigger_type='booking'
		LIMIT 0,10
		";
				
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){
			foreach ($res as $val) {				
				$trigger_id = $val['trigger_id'];  $status_trigger='process';
				$template_type = trim($val['template_type']);				
				
				try {
					
					$tpl = CustomerNotification::getNotificationTemplate($template_type,$val['language'],'push',false);
					
					$push_title = $tpl['push_title']; 
					$push_content = $tpl['push_content']; 
					
					foreach ($pattern as $pattern_val) {
						$data[$pattern_val]=isset($val[$pattern_val])?$val[$pattern_val]:'';
					}
					$data['sitename']=$sitename;					
					$data['siteurl']=$siteurl;
										
					$push_title = FunctionsV3::replaceTags($push_title,$data);
					$push_content = FunctionsV3::replaceTags($push_content,$data);	
					
					$params = array(
					 'merchant_id'=>isset($val['merchant_id'])?(integer)$val['merchant_id']:0,
					 'merchant_name'=>isset($val['restaurant_name'])?$val['restaurant_name']:'',
					 'booking_id'=>isset($val['booking_id'])?(integer)$val['booking_id']:0,
					 'push_title'=>$push_title,
					 'push_message'=>$push_content,
					 'topics'=>CHANNEL_TOPIC.$val['merchant_id'],
					 'date_created'=>FunctionsV3::dateNow(),
					 'ip_address'=>$_SERVER['REMOTE_ADDR'],
					);			
					Yii::app()->db->createCommand()->insert("{{merchantapp_broadcast}}",$params);
					
				} catch (Exception $e) {
			        $status_trigger = translate($e->getMessage());
		        }		
		        
		        $params_update = array(
		          'status'=>$status_trigger,
		          'date_process'=>FunctionsV3::dateNow(),
		          'ip_address'=>$_SERVER['REMOTE_ADDR']
		        );
		        		        
		        Yii::app()->db->createCommand()->update("{{merchantapp_order_trigger}}",$params_update,
		  	    'trigger_id=:trigger_id',
			  	    array(
			  	      ':trigger_id'=>$trigger_id
			  	    )
		  	    );
		        
			} //end foreach
			
			OrderWrapper::consumeUrl(FunctionsV3::getHostURL().Yii::app()->createUrl("merchantappv2/cron/processbroadcast"));
		}
		
		cronHelper::unlock();
        endif;	
        dump("cron end..");		
	}
	
	public function actionprocesspush()
	{
		dump("cron start..");
		define('LOCK_SUFFIX', APP_FOLDER.'_processpush');		
		if(($pid = cronHelper::lock()) !== FALSE):
		
		$stmt="
		SELECT a.*,
		(
		 select option_value
		 from {{option}}
		 where		 		 
		 option_name = 'merchantapp_services_account_json'
		 limit 0,1
		) as services_account_json

		FROM {{merchantapp_push_logs}} a
		WHERE a.status='pending'		
		ORDER BY id ASC		
		LIMIT 0,10		
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){			
			$file = FunctionsV3::uploadPath()."/".$res[0]['services_account_json'];
						
			foreach ($res as $val) {			
				$process_status=''; $json_response='';
				$process_date = FunctionsV3::dateNow();				
				$device_id = trim($val['device_id']);																			
				
				 try {		    		
	    			$json_response = FcmWrapper::ServiceAccount($file,APP_FOLDER.'_fcm')
					->setTarget($val['device_id'])
					->setTitle($val['push_title'])
					->setBody($val['push_message'])
					->setChannel(CHANNEL_ID)
					->setSound(CHANNEL_SOUNDNAME)
					->setAppleSound(CHANNEL_SOUNDFILE)
					->setBadge(1)
					->setForeground("true")
					->prepare()
					->send();						
					$process_status = 'process';
	    		} catch (Exception $e) {
	    			$process_status = 'failed';
					$json_response = $e->getMessage();						
				}			
								
				if(!empty($process_status)){
		   	  	   $process_status=substr( strip_tags($process_status) ,0,255);
		   	    } else $process_status='failed';	
		   	    
		   	    if(is_array($json_response) && count($json_response)>=1){
		   	    	$json_response = json_encode($json_response);
		   	    } 
		   	    
		   	    $params = array(
				  'status'=>$process_status,
				  'date_process'=>$process_date,
				  'json_response'=>$json_response
				);		
				
				Yii::app()->db->createCommand()->update("{{merchantapp_push_logs}}",$params,
		  	    'id=:id',
			  	    array(
			  	      ':id'=>$val['id']
			  	    )
		  	    );
				  
			} //end foreach
		}
		
		cronHelper::unlock();
		endif;	
		dump("cron end..");
	}
	
	public function actionprocessbroadcast()
	{
		dump("cron start..");
		define('LOCK_SUFFIX', APP_FOLDER.'_broadcast');		
		if(($pid = cronHelper::lock()) !== FALSE):
		
		$stmt="
		SELECT a.*,
		(
		 select option_value
		 from {{option}}
		 where		 		 
		 option_name = 'merchantapp_services_account_json'
		 limit 0,1
		) as services_account_json

		FROM {{merchantapp_broadcast}} a
		WHERE a.status='pending'		
		ORDER BY broadcast_id ASC		
		LIMIT 0,10
		";
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){		
			
			$file = FunctionsV3::uploadPath()."/".$res[0]['services_account_json'];
						
			foreach ($res as $val) {				
				$process_status=''; $json_response='';
				$process_date = FunctionsV3::dateNow();
				
				 try {		    		
	    			$json_response = FcmWrapper::ServiceAccount($file,APP_FOLDER.'_fcm')
					->setTarget($val['topics'])
					->setTitle($val['push_title'])
					->setBody($val['push_message'])
					->setChannel(CHANNEL_ID)
					->setSound(CHANNEL_SOUNDNAME)
					->setAppleSound(CHANNEL_SOUNDFILE)
					->setBadge(1)
					->setForeground("true")
					->prepare()
					->send();						
					$process_status = 'process';
	    		} catch (Exception $e) {
	    			$process_status = 'failed';
					$json_response = $e->getMessage();						
				}			
								
				if(!empty($process_status)){
		   	  	   $process_status=substr( strip_tags($process_status) ,0,255);
		   	    } else $process_status='failed';	
		   	    
		   	    if(is_array($json_response) && count($json_response)>=1){
		   	    	$json_response = json_encode($json_response);
		   	    } 
		   	    
		   	    $params = array(
				  'status'=>$process_status,
				  'date_modified'=>$process_date,
				  'fcm_response'=>$json_response
				);		
				
				Yii::app()->db->createCommand()->update("{{merchantapp_broadcast}}",$params,
		  	    'broadcast_id=:broadcast_id',
			  	    array(
			  	      ':broadcast_id'=>$val['broadcast_id']
			  	    )
		  	    );
				  
			} //end foreach
		}
		
		cronHelper::unlock();
		endif;	
		dump("cron end..");
	}
	
	public function actionunattented_order()
	{		
		dump("cron start..");
        define('LOCK_SUFFIX', APP_FOLDER.'_unattented_order');		
        if(($pid = cronHelper::lock()) !== FALSE):


		$and=''; $lang=Yii::app()->language;
		$pattern = array('order_id','customer_name','restaurant_name','total_amount');
								
		$order_unattended_minutes = (integer)getOptionA('order_unattended_minutes');
		if($order_unattended_minutes<=0){
			$order_unattended_minutes = 5;
		}		
					
				
		$interval_date = date("Y-m-d H:i:s", strtotime("+$order_unattended_minutes minutes"));
		$todays_date = date("Y-m-d");
		
		
		$end = date("Y-m-d H:i:s");
		$start = date("Y-m-d H:i:s", strtotime("-$order_unattended_minutes minutes"));
						
		$stats = OrderWrapper::getStatusFromSettings('order_incoming_status',array('pending','paid'));
				
		$and.=" AND a.status IN ($stats)
		AND a.request_cancel='2'
		";		
		
		$and.=" AND CAST(a.date_created as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)." ";
				
		$and.=" AND ".q($interval_date)." > a.date_created  ";
				
		$and.=" AND a.order_id NOT IN (
		  select order_id from
		  {{merchantapp_broadcast}}
		  where order_id=a.order_id
		  and 
		  date_created BETWEEN ".q($start)." AND ".q($end)."
		)";
				
		$tpl = CustomerNotification::getNotificationTemplate('receipt_send_to_merchant',$lang,'push',false);
		$push_title = isset($tpl['push_title'])?$tpl['push_title']:''; 
		$push_content = isset($tpl['push_content'])?$tpl['push_content']:'';
		
		$stmt="
		SELECT a.*
		FROM {{view_order}} a
		WHERE 1
		$and
		LIMIT 0,50
		";					
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){						
			foreach ($res as $val) {				
				$data=array();
				foreach ($pattern as $pattern_val) {
					$data[$pattern_val]=isset($val[$pattern_val])?$val[$pattern_val]:'';
				}
				$push_title = FunctionsV3::replaceTags($push_title,$data);
				$push_content = FunctionsV3::replaceTags($push_content,$data);	
				
				$params = array(
				 'merchant_id'=>(integer)$val['merchant_id'],
				 'merchant_name'=>$val['restaurant_name'],
				 'order_id'=>$val['order_id'],
				 'push_title'=>$push_title,
				 'push_message'=>$push_content,
				 'topics'=>CHANNEL_TOPIC.$val['merchant_id'],
				 'date_created'=>FunctionsV3::dateNow(),
				 'ip_address'=>$_SERVER['REMOTE_ADDR'],
				);						
				Yii::app()->db->createCommand()->insert("{{merchantapp_broadcast}}",$params);					
			} //end foreach
			OrderWrapper::consumeUrl(FunctionsV3::getHostURL().Yii::app()->createUrl("merchantappv2/cron/processbroadcast"));
		} 
		
		cronHelper::unlock();
        endif;	
        dump("cron end..");		
	}
	
	public function actionunattented_booking()
	{
		dump("cron start..");
        define('LOCK_SUFFIX', APP_FOLDER.'_unattented_booking');		
        if(($pid = cronHelper::lock()) !== FALSE):


		$and=''; $lang=Yii::app()->language;
		$pattern = array('booking_id','restaurant_name','number_guest','date_booking',
		'time','customer_name','email','mobile','instruction','status');
								
		$unattended_minutes = (integer)getOptionA('booking_incoming_unattended_minutes');
		if($unattended_minutes<=0){
			$unattended_minutes = 5;
		}		
											
		$interval_date = date("Y-m-d H:i:s", strtotime("+$unattended_minutes minutes"));
		$todays_date = date("Y-m-d");
								
		$end = date("Y-m-d H:i:s");
        $start = date("Y-m-d H:i:s", strtotime("-$unattended_minutes minutes"));

				
		$and.=" AND a.status IN ('pending')
		AND a.request_cancel='0'
		";		
		
		$and.=" AND CAST(a.date_created as DATE) BETWEEN ".q($todays_date)." AND ".q($todays_date)." ";
				
		$and.=" AND ".q($interval_date)." > a.date_created  ";
				
		$and.=" AND a.booking_id NOT IN (
		  select booking_id from
		  {{merchantapp_broadcast}}
		  where booking_id=a.booking_id
		  and 		  
		  date_created BETWEEN ".q($start)." AND ".q($end)."
		)";
		
		$tpl = CustomerNotification::getNotificationTemplate('booked_notify_merchant',$lang,'push',false);
		$push_title = isset($tpl['push_title'])?$tpl['push_title']:''; 
		$push_content = isset($tpl['push_content'])?$tpl['push_content']:'';
		
		$stmt="
		SELECT 
		a.booking_id, a.merchant_id, a.client_id, a.number_guest, a.date_booking,
		a.date_booking as date_booking_raw, a.booking_time as booking_time_raw,
		a.booking_time, a.booking_name, a.email, a.mobile, a.booking_notes,
		a.date_created,a.date_created as date_created_raw, a.status, a.status as status_raw,
		c.restaurant_name 
		
		FROM {{bookingtable}} a
		LEFT JOIN {{merchant}} c
		ON
		a.merchant_id = c.merchant_id
		
		WHERE 1
		$and
		LIMIT 0,50
		";						
		if($res = Yii::app()->db->createCommand($stmt)->queryAll()){						
			foreach ($res as $val) {				
				$data=array();
				foreach ($pattern as $pattern_val) {
					$data[$pattern_val]=isset($val[$pattern_val])?$val[$pattern_val]:'';
				}
				$push_title = FunctionsV3::replaceTags($push_title,$data);
				$push_content = FunctionsV3::replaceTags($push_content,$data);	
				
				$params = array(
				 'merchant_id'=>isset($val['merchant_id'])?(integer)$val['merchant_id']:0,
				 'merchant_name'=>isset($val['restaurant_name'])?$val['restaurant_name']:'',
				 'booking_id'=>isset($val['booking_id'])?(integer)$val['booking_id']:0,
				 'push_title'=>$push_title,
				 'push_message'=>$push_content,
				 'topics'=>CHANNEL_TOPIC.$val['merchant_id'],
				 'date_created'=>FunctionsV3::dateNow(),
				 'ip_address'=>$_SERVER['REMOTE_ADDR'],
				);								
				Yii::app()->db->createCommand()->insert("{{merchantapp_broadcast}}",$params);					
			} //end foreach
			OrderWrapper::consumeUrl(FunctionsV3::getHostURL().Yii::app()->createUrl("merchantappv2/cron/processbroadcast"));
		} 
		
		cronHelper::unlock();
        endif;	
        dump("cron end..");		
	}
	
	public static function actionclear_logs()
	{
		dump("cron start..");
        define('LOCK_SUFFIX', APP_FOLDER.'_clear_logs');		
        if(($pid = cronHelper::lock()) !== FALSE):
        
        $unattended_minutes=1;
        $interval_date = date("Y-m-d H:i:s", strtotime("+$unattended_minutes minutes"));
        
        $stmt="
        DELETE FROM {{merchantapp_broadcast}}
        WHERE date_created <= CURRENT_DATE() - INTERVAL 2 MONTH
        ";                
        Yii::app()->db->createCommand($stmt)->query();
        
        $stmt="
        DELETE FROM {{merchantapp_push_logs}}
        WHERE date_created <= CURRENT_DATE() - INTERVAL 2 MONTH
        ";                
        Yii::app()->db->createCommand($stmt)->query();
        
        $stmt="
        DELETE FROM {{merchantapp_device_reg}}
        WHERE last_login <= CURRENT_DATE() - INTERVAL 2 MONTH
        ";                
        Yii::app()->db->createCommand($stmt)->query();
        
        cronHelper::unlock();
        endif;	
        dump("cron end..");		
	}
	
}
/*end class*/