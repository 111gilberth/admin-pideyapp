<?php
class UpdateController extends CController
{
	
	public function beforeAction($action)
	{		
		if(!Yii::app()->functions->isAdminLogin()){		   
		   Yii::app()->end();		
		}				
		return true;
	}
	
	public function actionIndex()
	{
		$table_prefix=Yii::app()->db->tablePrefix;	$loger=array();
						
		$date_default = "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";		
		if($res = Yii::app()->db->createCommand("SELECT VERSION() as mysql_version")->queryRow()){				
			$mysql_version = (float)$res['mysql_version'];
			dump("MYSQL VERSION=>$mysql_version");
			if($mysql_version<=5.5){				
				$date_default="datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
			}
		}
				
		/*NEW TABLES*/
		$loger[] = DatataseMigration::createTable("{{opening_hours}}",array(
		  'id'=>'pk',
		  'merchant_id'=>"integer(14) NOT NULL DEFAULT '0'",
		  'day'=>"varchar(20) NOT NULL DEFAULT ''",
		  'status'=>"varchar(100) NOT NULL DEFAULT 'open'",
		  'start_time'=>"varchar(14) NOT NULL DEFAULT ''",
		  'end_time'=>"varchar(14) NOT NULL DEFAULT ''",
		  'start_time_pm'=>"varchar(14) NOT NULL DEFAULT ''",
		  'end_time_pm'=>"varchar(14) NOT NULL DEFAULT ''",
		  'custom_text'=>"varchar(255) NOT NULL DEFAULT ''",
		));
		
		$loger[] = DatataseMigration::createTable("{{merchantapp_device_reg}}",array(
		  'registration_id'=>'pk',
		  'id'=>"integer(14) NOT NULL DEFAULT '0'",
		  'merchant_id'=>"integer(14) NOT NULL DEFAULT '0'",
		  'user_type'=>"varchar(100) NOT NULL DEFAULT ''",
		  'device_uiid'=>"varchar(255) NOT NULL DEFAULT ''",
		  'device_id'=>"text ",
		  'device_platform'=>"varchar(50) NOT NULL DEFAULT ''",
		  'push_enabled'=>"integer(1) NOT NULL DEFAULT '1'",
		  'subscribe_topic'=>"integer(1) NOT NULL DEFAULT '1'",
		  'status'=>"varchar(255) NOT NULL DEFAULT 'active'",
		  'code_version'=>"varchar(50) NOT NULL DEFAULT ''",
		  'date_created'=>$date_default,
		  'date_modified'=>$date_default,
		  'last_login'=>$date_default,
		  'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
		));
		
		$loger[] = DatataseMigration::createTable("{{merchantapp_push_logs}}",array(
		   'id'=>'pk',
		   'broadcast_id'=>"integer(14) NOT NULL DEFAULT '0'",
		   'push_type'=>"varchar(100) NOT NULL DEFAULT 'order'",
		   'merchant_name'=>"varchar(255) NOT NULL DEFAULT ''",
		   'device_platform'=>"varchar(100) NOT NULL DEFAULT ''",
		   'device_id'=>"text ",
		   'device_uiid'=>"varchar(255) NOT NULL DEFAULT ''",
		   'push_title'=>"varchar(255) NOT NULL DEFAULT ''",
		   'push_message'=>"varchar(255) NOT NULL DEFAULT ''",
		   'status'=>"varchar(255) NOT NULL DEFAULT 'pending'",
		   'json_response'=>"text ",
		   'date_created'=>$date_default,
		   'date_process'=>$date_default,
		   'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
		   'is_read'=>"integer(1) NOT NULL DEFAULT '0'",
		   'is_remove'=>"integer(1) NOT NULL DEFAULT '0'",
		   'date_modified'=>$date_default,
		));
		
		$loger[] = DatataseMigration::createTable("{{merchantapp_broadcast}}",array(
		   'broadcast_id'=>'pk',
		   'merchant_id'=>"integer(14) NOT NULL DEFAULT '0'",
		   'merchant_name'=>"varchar(255) NOT NULL DEFAULT ''",
		   'order_id'=>"integer(14) NOT NULL DEFAULT '0'",
		   'booking_id'=>"integer(14) NOT NULL DEFAULT '0'",
		   'push_title'=>"varchar(255) NOT NULL DEFAULT ''",
		   'push_message'=>"varchar(255) NOT NULL DEFAULT ''",
		   'topics'=>"varchar(255) NOT NULL DEFAULT ''",
		   'status'=>"varchar(255) NOT NULL DEFAULT 'pending'",
		   'date_created'=>$date_default,
		   'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
		   'date_modified'=>$date_default,
		   'fcm_response'=>"text ",
		   'is_read'=>"integer(1) NOT NULL DEFAULT '0'",
		   'is_remove'=>"integer(1) NOT NULL DEFAULT '0'",
		));
		
		$loger[] = DatataseMigration::createTable("{{merchantapp_order_trigger}}",array(
		   'trigger_id'=>'pk',
		   'trigger_type'=>"varchar(100) NOT NULL DEFAULT 'order'",
		   'order_id'=>"integer(14) NOT NULL DEFAULT '0'",
		   'order_status'=>"varchar(255) NOT NULL DEFAULT ''",
		   'remarks'=>"text ",
		   'language'=>"varchar(50) NOT NULL DEFAULT 'en'",
		   'status'=>"varchar(255) NOT NULL DEFAULT 'pending'",
		   'date_created'=>$date_default,
		   'date_process'=>$date_default,
		   'ip_address'=>"varchar(50) NOT NULL DEFAULT ''",
		));
		
		/*UPDATE TABLES*/
		try {

			$loger[] = DatataseMigration::addColumn("{{admin_user}}",array(
			  'mobile_session_token'=>"varchar(255) NOT NULL DEFAULT ''", 
			  'pin'=>"integer(4) NOT NULL DEFAULT '0'",
			  'contact_number'=>"varchar(50) NOT NULL DEFAULT ''",
			  'status'=>"varchar(100) NOT NULL DEFAULT 'active'"
			));	
						
			$loger[] = DatataseMigration::addColumn("{{merchant}}",array(
			  'mobile_session_token'=>"varchar(255) NOT NULL DEFAULT ''", 
			  'pin'=>"integer(4) NOT NULL DEFAULT '0'",
			  'contact_phone'=>"varchar(100) NOT NULL DEFAULT ''"			  
			));	
			
			$loger[] = DatataseMigration::addColumn("{{merchant_user}}",array(
			  'mobile_session_token'=>"varchar(255) NOT NULL DEFAULT ''", 
			  'pin'=>"integer(4) NOT NULL DEFAULT '0'",
			  'contact_number'=>"varchar(50) NOT NULL DEFAULT ''"			  
			));	
			
			$loger[] = DatataseMigration::addColumn("{{order_delivery_address}}",array(
			  'estimated_time'=>"integer(14) NOT NULL DEFAULT '0'", 
			  'estimated_date_time'=>$date_default
			));		
			
			$loger[] = DatataseMigration::addColumn("{{opening_hours}}",array(
			  'custom_text'=>"varchar(255) NOT NULL DEFAULT ''",
			));	
			
			$loger[] = DatataseMigration::addColumn("{{bookingtable}}",array(
			  'remarks'=>"text ",
			));	
			
		} catch (Exception $e) {
			$loger[]  = $e->getMessage();
		}					
		
		/*ADD INDEX*/	
		$loger[] = DatataseMigration::createIndex("{{opening_hours}}",array(
		  'status'=>"status",
		  'start_time'=>"start_time",
		  'end_time'=>"end_time",
		  'start_time_pm'=>"start_time_pm",
		  'end_time_pm'=>"end_time_pm",
		));
		
		$loger[] = DatataseMigration::createIndex("{{merchantapp_device_reg}}",array(
		  'id'=>"id",
		  'merchant_id'=>"merchant_id",
		  'user_type'=>"user_type",
		  'device_uiid'=>"device_uiid",
		  'device_id'=>"device_id",
		  'push_enabled'=>"push_enabled",
		  'subscribe_topic'=>"subscribe_topic",
		  'status'=>"status",
		));
		$loger[] = DatataseMigration::createIndex("{{merchantapp_push_logs}}",array(
		  'broadcast_id'=>"broadcast_id",
		  'push_type'=>"push_type",
		  'merchant_name'=>"merchant_name",
		  'device_platform'=>"device_platform",
		  'device_id'=>"device_id",
		  'device_uiid'=>"device_uiid",
		  'push_title'=>"push_title",
		));
		
		$loger[] = DatataseMigration::createIndex("{{merchantapp_broadcast}}",array(
		  'merchant_id'=>"merchant_id",
		  'merchant_name'=>"merchant_name",
		  'order_id'=>"order_id",
		  'booking_id'=>"booking_id",
		  'push_title'=>"push_title",
		  'topics'=>"topics",
		  'status'=>"status",
		  'is_read'=>"is_read",
		  'is_remove'=>"is_remove",
		));
		
		$loger[] = DatataseMigration::createIndex("{{merchantapp_order_trigger}}",array(
		  'trigger_type'=>"trigger_type",
		  'order_id'=>"order_id",
		  'order_status'=>"order_status",
		));
		
		/*INSTAL DEFAULT DATA*/
		$is_install = (integer) getOptionA('merchantapp_install');		
		if($is_install<=0){
			dump("INSTALL DEFAULT DATA");
			require_once 'default_data.php';			
			foreach ($data as $data_val) {				
				Yii::app()->functions->updateOptionAdmin($data_val['option_name'],$data_val['option_value']);
			}
			Yii::app()->functions->updateOptionAdmin('merchantapp_install',1);
			
			/*ADD NEW ORDER STATUS*/
			DatataseMigration::addOrderStatus(array(
			  'food is ready','delayed'
			));
			
		}		
		
		/*VIEW TABLES*/
		$stmt="
		create OR REPLACE VIEW ".$table_prefix."view_user_master as
		select 
		a.admin_id as id,
		'merchant_id',
		'user_type',
		a.email_address,
		a.contact_number,			
		a.username,
		a.password,
		a.mobile_session_token as session_token,
		a.status,
		a.user_access,
		a.pin,
		a.lost_password_code
		from ".$table_prefix."admin_user a
		
		UNION ALL
		
		select 
		b.merchant_user_id as id,
		b.merchant_id,
		'merchant_user',
		b.contact_email as email_address,
		b.contact_number,			
		b.username,
		b.password,
		b.mobile_session_token as session_token,
		b.status,
		b.user_access,
		b.pin,
		b.lost_password_code
		from ".$table_prefix."merchant_user b
		
		
		UNION ALL
		select
		c.merchant_id as id,
		c.merchant_id,
		'merchant',
		c.contact_email as email_address,
		c. contact_phone as contact_number,			
		c.username,
		c.password,
		c.mobile_session_token as session_token,
		c.status,
		c.user_access,
		c.pin,
		c.lost_password_code
		from  ".$table_prefix."merchant c
		";					
		if (Yii::app()->db->createCommand($stmt)->query()){
			$loger[] = "Create table {{user_master_list}} done";
		} else $loger[] = "Create table {{user_master_list}} failed";
		
		
		$stmt="
		create OR REPLACE VIEW ".$table_prefix."view_merchantapp_device as
		SELECT
		(
		select username
		from ".$table_prefix."view_user_master
		where
		id=a.id
		and 
		merchant_id=a.merchant_id
		and
		user_type=a.user_type
		limit 0,1
		) as name,
		a.registration_id,
		a.device_platform,
		a.device_uiid,
		a.device_id,
		a.push_enabled,
		a.subscribe_topic,
		a.date_created,
		a.last_login,
		a.status		
		
		FROM
		".$table_prefix."merchantapp_device_reg a
		";
		if (Yii::app()->db->createCommand($stmt)->query()){
			$loger[] = "Create table {{view_merchantapp_device}} done";
		} else $loger[] = "Create table {{view_merchantapp_device}} failed";
		
		
		$stmt="
		create OR REPLACE VIEW ".$table_prefix."view_order as
		SELECT 
		a.order_id,
		a.client_id,
		concat(b.first_name,' ',b.last_name) as customer_name,
		concat(c.first_name,' ',c.last_name) as profile_customer_name,
		a.merchant_id,
		d.restaurant_name,
		a.trans_type,
		a.payment_type,
		a.total_w_tax as total_amount,
		a.status,
		a.delivery_date,
		a.delivery_time,
		a.delivery_asap,
		a.delivery_instruction,
		a.date_created,
		a.request_cancel
		
		FROM  ".$table_prefix."order a
		
		LEFT JOIN ".$table_prefix."order_delivery_address b
		ON
		a.order_id = b.order_id
		
		LEFT JOIN ".$table_prefix."client c
		ON
		a.client_id = c.client_id
		
		LEFT JOIN ".$table_prefix."merchant d
		ON
		a.merchant_id = d.merchant_id";
		if (Yii::app()->db->createCommand($stmt)->query()){
			$loger[] = "Create table {{view_order}} done";
		} else $loger[] = "Create table {{view_order}} failed";
		
		
		$stmt="
		create OR REPLACE VIEW ".$table_prefix."merchantapp_view_notification as
		select
		id,
		'push_logs',
		device_uiid as key_id,
		push_title,
		push_message,
		date_created,
		is_read,
		is_remove
		from
		".$table_prefix."merchantapp_push_logs
		
		UNION ALL
		select
		broadcast_id as id,
		'broadcast_logs',
		merchant_id as key_id,
		push_title,
		push_message,
		date_created,
		is_read,
		is_remove
		from
		".$table_prefix."merchantapp_broadcast
		";
		if (Yii::app()->db->createCommand($stmt)->query()){
			$loger[] = "Create table {{merchantapp_view_notification}} done";
		} else $loger[] = "Create table {{merchantapp_view_notification}} failed";
		
		
		dump($loger);
		dump("FINISH");
		
		?>
		<br/>
		<a href="<?php echo Yii::app()->createUrl(APP_FOLDER)?>">
		 <?php echo translate("Update done click here to go back")?>
		</a>
		<?php
	    
	}
	
}
/*end class*/