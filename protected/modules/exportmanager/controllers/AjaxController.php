<?php
/*if( !ini_get('safe_mode') ){	
    set_time_limit(900);
    ini_set("memory_limit","128M");
}*/
class AjaxController extends CController
{
	public $code=2;
	public $msg;
	public $details;
	public $data;
	public $filename_export;
	
	public function __construct()
	{
		$this->data=$_POST;
		$this->filename_export=AddonExportModule::file_export_name;
	}
	
	public function actionIndex()
	{
		
	}
	
	public function actionExport()
	{		 		 
		 $DbExt=new DbExt;  			 
		 
		 $include_item=isset($this->data['include_item'])?$this->data['include_item']:false;
		 		 		 
		 if (isset($this->data['filter_export'])){
		 	switch ($this->data['filter_export']) {
		 		case 1:
		 				
		 			$stmt="SELECT * FROM
		 			{{merchant}}
		 			ORDER BY merchant_id ASC		 			
		 			";		 			
		 			if ( $res=$DbExt->rst($stmt)){
		 				if(AddonExportModule::exportMerchant($res,$include_item)){
		 					$this->msg=$this->t("Successful");
		 					$this->code=1;	
		 					$this->details=Yii::app()->baseUrl."/exportmanager/ajax/getfiles?f=".$this->filename_export;
		 				} else $this->msg=$this->t("Something went wrong");
		 			} else $this->msg=$this->t("No records found");
		 			break;
		 	
		 		case 2:
		 			if (!isset($this->data['merchant_name'])){
		 				$this->jsonFailed("Merchant is required");
		 				return ;
		 			}		 			
		 			if (is_array($this->data['merchant_name']) && count($this->data['merchant_name'])>=1){
		 				$mtid='';
		 				foreach ($this->data['merchant_name'] as $val) {
		 					$mtid.="'$val',";
		 				}
		 				$mtid=substr($mtid,0,-1);
		 				$stmt="
		 				SELECT * FROM
		 				{{merchant}}
		 				WHERE
		 				merchant_id IN ($mtid)
		 				";
		 				if ( $res=$DbExt->rst($stmt)){
		 					if(AddonExportModule::exportMerchant($res,$include_item)){
			 					$this->msg=$this->t("Successful");
			 					$this->code=1;	
			 					$this->details=Yii::app()->baseUrl."/exportmanager/ajax/getfiles?f=".$this->filename_export;
			 				} else $this->msg=$this->t("Something went wrong");
		 				} else $this->msg=$this->t("No records found");
		 			} else {
		 				$this->jsonFailed("Merchant is required");
		 				return ;
		 			}
		 			break;	
		 		default:
		 			break;
		 	}
		 } else $this->msg=$this->t("Filter is required");
		 $this->jsonResponse();
	}
	
	public function actionGetfiles()
	{		
		$path_to_upload=Yii::getPathOfAlias('webroot')."/upload/export";
		$file=isset($_GET['f'])?$_GET['f']:'';
		if(!empty($file)){		
			$file_path=$path_to_upload."/$file";
			if (file_exists($file_path)){
				$content=file_get_contents($file_path);
				header('Content-disposition: attachment; filename='.$this->filename_export);
                header('Content-type: application/json');
                echo $content;										
				yii::app()->end();
			}
		}
	}
	
	private function jsonResponse()
	{
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
	}
	
	private function jsonFailed($msg='')
	{
		$this->msg=$this->t($msg);
		$this->jsonResponse();
	}
	
	function t($message='')
	{
		return Yii::t("default",$message);
	}
	
	public function actionDuplicate()
	{
		
		 //dump($this->data);
		 $this->data['filter_export']=2;		 
		 $DbExt=new DbExt;  			 
		 
		 $include_item=isset($this->data['include_item'])?$this->data['include_item']:false;
		 		 		 
		 if (isset($this->data['filter_export'])){
		 	switch ($this->data['filter_export']) {		 		
		 		case 2:
		 			if (!isset($this->data['merchant_name'])){
		 				$this->jsonFailed("Merchant is required");
		 				return ;
		 			}		 			
		 			if (is_array($this->data['merchant_name']) && count($this->data['merchant_name'])>=1){
		 				$mtid='';		 				
		 				foreach ($this->data['merchant_name'] as $val) {
		 					$mtid.="'$val',";		 					
		 				}
		 				$mtid=substr($mtid,0,-1);
		 				$stmt="
		 				SELECT * FROM
		 				{{merchant}}
		 				WHERE
		 				merchant_id IN ($mtid)
		 				";
		 				//dump($stmt);
		 				if ( $res=$DbExt->rst($stmt)){
		 					if($resp=AddonExportModule::duplicateMerchant($res,$include_item)){
			 					$this->msg=$this->t("Merchant successfully replicated");
			 					$this->code=1;		
			 					$this->details=$resp;
			 				} else $this->msg=$this->t("Something went wrong");
		 				} else $this->msg=$this->t("No records found");
		 			} else {
		 				$this->jsonFailed("Merchant is required");
		 				return ;
		 			}
		 			break;	
		 		default:
		 			break;
		 	}
		 } else $this->msg=$this->t("Filter is required");
		 $this->jsonResponse();
	}
	
	public function actiondisplayReplicateMerchant()
	{
		$header[]=$this->t("Merchant Id");
		$header[]=$this->t("Merchant Name");
		$header[]=$this->t("Address");
		$header[]=$this->t("New Email address");
		$header[]=$this->t("New Username");
		$header[]=$this->t("New Password");
		
		//$this->data['merchant_list']='10';
		
		if (isset($this->data['merchant_list'])){			
			if (!empty($this->data['merchant_list'])){		
				$this->data['merchant_list']=substr($this->data['merchant_list'],0,-1);
				$DbExt=new DbExt; 
				$stmt="SELECT * FROM
				{{merchant}}
				WHERE
				merchant_id IN (".$this->data['merchant_list'].")
				";				
				if ($res=$DbExt->rst($stmt)){
					$this->details=AddonExportModule::asTable($header,$res);
					$this->code=1;
					$this->msg="OK";
				} else $this->msg=t("No records found");
			} else $this->msg=$this->t("Missing parameters");
		} else $this->msg=$this->t("Missing parameters");
		$this->jsonResponse();				
	}
	
	public function q($data='')
	{
		return Yii::app()->db->quoteValue($data);
	}
	
	public function actionImportMerchant()
	{				
		require_once('Uploader.php');
		$path_to_upload=Yii::getPathOfAlias('webroot')."/upload/export";
        $valid_extensions = array('json', 'json2' ,'jpg'); 
        if(!file_exists($path_to_upload)) {	
           if (!@mkdir($path_to_upload,0777)){           	               	
           	    $this->msg=$this->t("Error has occured cannot create upload directory");
                $this->jsonResponse();
           }		    
	    }
	    
        $Upload = new FileUpload('uploadfile');
        $ext = $Upload->getExtension(); 
        //$Upload->newFileName = mktime().".".$ext;
        $result = $Upload->handleUpload($path_to_upload, $valid_extensions);                
        if (!$result) {                    	
            $this->msg=$Upload->getErrorMsg();            
        } else {         	
        	if (file_exists($path_to_upload."/".$_GET['uploadfile'])){
        		$content=file_get_contents($path_to_upload."/".$_GET['uploadfile']);
        		if(!empty($content)){
        			$content=json_decode($content,true);
        			if ( $resp=AddonExportModule::importMerchant($content)){
        				$this->code=1;
        				$this->msg=$this->t("File successfully imported");
        				$this->details=$resp;
        				/*remove the uploaded file*/
        				@unlink($path_to_upload."/".$_GET['uploadfile']);
        			} else $this->msg=$this->t("Error: something went wrong during processing your request");
        		} else $this->msg=$this->t("File has no content");
        	} else $this->msg=$this->t("File not found");
        }
        $this->jsonResponse();
	}	
		
}/* end class*/