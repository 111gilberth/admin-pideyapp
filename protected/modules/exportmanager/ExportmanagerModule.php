<?php
class exportmanagerModule extends CWebModule
{
	public $require_login;
	
	public function init()
	{
		
		// this method is called when the module is being created
		// you may place code here to customize the module or the application
		
		// import the module-level models and components
		$this->setImport(array(
			//'exportmanager.models.*',
			'exportmanager.components.*',
		));
		
		$ajaxurl=Yii::app()->baseUrl.'/exportmanager/ajax';
		
		Yii::app()->clientScript->scriptMap=array(
          'jquery.js'=>false,
          'jquery.min.js'=>false
        );

		$cs = Yii::app()->getClientScript();  
		$cs->registerScript(
		  'ajaxurl',
		 "var ajaxurl='$ajaxurl'",
		  CClientScript::POS_HEAD
		);
		
		/*JS FILE*/
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/exportmanager/assets/jquery-1.10.2.min.js',
		CClientScript::POS_END
		);
				
		Yii::app()->clientScript->registerScriptFile(
        '//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js',
		CClientScript::POS_END
		);
						
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/exportmanager/assets/chosen/chosen.jquery.min.js',
		CClientScript::POS_END
		);		
		
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/exportmanager/assets/SimpleAjaxUploader.min.js',
		CClientScript::POS_END
		);		
				
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/exportmanager/assets/exportmanager.js?ver=1.0',
		CClientScript::POS_END
		);		
				
		/*CSS FILE*/
		$baseUrl = Yii::app()->baseUrl."/protected/modules/exportmanager"; 
		$cs = Yii::app()->getClientScript();		
		$cs->registerCssFile("//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css");		
		$cs->registerCssFile($baseUrl."/assets/chosen/chosen.min.css");		
		$cs->registerCssFile($baseUrl."/assets/export.css?ver=1.0");
	}

	public function beforeControllerAction($controller, $action)
	{				
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here									
			return true;
		}
		else
			return false;
	}
}