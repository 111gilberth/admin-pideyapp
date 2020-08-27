<?php
if (!isset($_SESSION)) { session_start(); }

class IndexController extends CController
{
	public $layout='layout';	
	
	public function beforeAction($action)
	{		
		if (Yii::app()->controller->module->require_login){
			if(!Yii::app()->functions->isAdminLogin()){
			   $this->redirect(Yii::app()->createUrl('/admin/noaccess'));
			   Yii::app()->end();		
			}
		}
		
		$action_name = 'addonexport';
		$aa_access=Yii::app()->functions->AAccess();
	    $menu_list=Yii::app()->functions->AAmenuList();	  	 	    
	    array_push($menu_list,'exportmanager');	    
	    if (in_array($action_name,(array)$menu_list)){
	    	if (!in_array($action_name,(array)$aa_access)){	   	    		
	    		$this->redirect(Yii::app()->createUrl('/admin/noaccess'));
	    	}
	    }	    
	    
		return true;
	}
	
	public function actionIndex(){
		$this->pageTitle = Yii::t("default","Addon Export Manager");		
		$this->render('index');
	}		
}