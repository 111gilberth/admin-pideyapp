<?php
class AddonExportModule
{
	const file_export_name='merchant_export.json';	
	
	public static function getPhpVersion()
	{
		 $php_version=str_replace(".",'',phpversion());
		 return $php_version;
	}
	
    public static function merchantList($as_list=true,$with_select=false)
    {
    	$data='';
    	$DbExt=new DbExt;
    	$stmt="SELECT * FROM
    	{{merchant}}
    	WHERE status in ('active')
    	ORDER BY restaurant_name ASC
    	";
    	if ( $with_select){    		
    		$data[]=Yii::t("default","Please select");
    	}
    	if ($res=$DbExt->rst($stmt)){    		    		
    		if ( $as_list==TRUE){
    			foreach ($res as $val) {    				
    			    //$data[$val['merchant_id']]=ucwords($val['restaurant_name']) ." (".$val['restaurant_slug'].")"; 
    			    $data[$val['merchant_id']]=($val['restaurant_name']) ." (".$val['restaurant_slug'].")"; 
    			}    		    			
    			return $data;
    		} else return $res;    	
    	}
    	return false;
    }
    	
	public static function exportMerchant($data_resp='',$include_item='')
	{
		$food_item='';
		$category='';
		$cooking_ref='';
		$subcategory='';
		$subcategory_item='';
		$option='';
		$size='';
		$ingredients='';
		
		$filename=self::file_export_name;	
				 
		if(is_array($data_resp) && count($data_resp)>=1){
			foreach ($data_resp as $val) {
			    if ($include_item==1){
					$food_item=AddonExportModule::getRecords($val['merchant_id'],'item');
					$category=AddonExportModule::getRecords($val['merchant_id'],'category');
				    $cooking_ref=AddonExportModule::getRecords($val['merchant_id'],'cooking_ref');
				    $subcategory=AddonExportModule::getRecords($val['merchant_id'],'subcategory');
				    $subcategory_item=AddonExportModule::getRecords($val['merchant_id'],'subcategory_item');				    
				    $size=AddonExportModule::getRecords($val['merchant_id'],'size');
				    $ingredients=AddonExportModule::getRecords($val['merchant_id'],'ingredients');
 				}		 					
 				$option=AddonExportModule::getRecords($val['merchant_id'],'option');
			    $data[]=array(
			      'merchant'=>$val,
			      'item'=>$food_item,
			       'category'=>$category,
			       'cooking_ref'=>$cooking_ref,
			       'subcategory'=>$subcategory,
			       'subcategory_item'=>$subcategory_item,
			       'option'=>$option,
			       'size'=>$size,
			       'ingredients'=>$ingredients
			    );	
			}
									 
			self::createJsonFile($filename,CJSON::encode($data));
			return true;
		}
		return false;
	}
	
	public static function duplicateMerchant($data_resp='',$include_item='')
	{
		/*echo self::getPhpVersion();
		die();*/
		
		$DbExt=new DbExt; 
		
		$food_item='';
		$category='';
		$cooking_ref='';
		$subcategory='';
		$subcategory_item='';
		$option='';
		$size='';
		$ingredients='';
		
		$filename=self::file_export_name;	
		
		/**create temporary table*/
		$DbExt->qry(TempDBStructure::Tbl_Subcategory());
		$DbExt->qry(TempDBStructure::Tbl_SubcategoryItem());
		$DbExt->qry(TempDBStructure::Tbl_Size());
		$DbExt->qry(TempDBStructure::Tbl_Ingredients());
		//$DbExt->qry(TempDBStructure::Tbl_Category());
		TempDBStructure::Tbl_Category();
		$DbExt->qry(TempDBStructure::Tbl_CookingRef());
		//$DbExt->qry(TempDBStructure::Tbl_Item());
		TempDBStructure::Tbl_Item();		
								
		if(is_array($data_resp) && count($data_resp)>=1){
			foreach ($data_resp as $val) {
			    if ($include_item==1){
					$food_item=AddonExportModule::getRecords($val['merchant_id'],'item');
					$category=AddonExportModule::getRecords($val['merchant_id'],'category');
				    $cooking_ref=AddonExportModule::getRecords($val['merchant_id'],'cooking_ref');
				    $subcategory=AddonExportModule::getRecords($val['merchant_id'],'subcategory');
				    $subcategory_item=AddonExportModule::getRecords($val['merchant_id'],'subcategory_item');				    
				    $size=AddonExportModule::getRecords($val['merchant_id'],'size');
				    $ingredients=AddonExportModule::getRecords($val['merchant_id'],'ingredients');
 				}		 					
 				$option=AddonExportModule::getRecords($val['merchant_id'],'option');
			    $data[]=array(
			       'merchant'=>$val,
			       'size'=>$size,
			       'item'=>$food_item,
			       'category'=>$category,
			       'cooking_ref'=>$cooking_ref,
			       'subcategory'=>$subcategory,
			       'subcategory_item'=>$subcategory_item,
			       'option'=>$option,			       
			       'ingredients'=>$ingredients
			    );	
			}		
							
			/*dump($data);	
			die();*/
			$debug=false;
			
			if (is_array($data) && count($data)>=1){
				
				$new_mtid_list='';
				foreach ($data as $val) {					
					$merchant_info=$val['merchant'];					
					$merchant_info['restaurant_slug']=Yii::app()->functions->createSlug($merchant_info['restaurant_name']);
					//$merchant_info['restaurant_name']=$merchant_info['restaurant_slug'];
					$merchant_info['username']=self::generateNewUserPass($merchant_info['restaurant_name']);
					$merchant_info['password']=md5($merchant_info['username']);
					$merchant_info['contact_email']=Yii::app()->functions->generateCode(5)."_".$merchant_info['contact_email'];
					
					$old_mtid=$merchant_info['merchant_id'];
					unset($merchant_info['merchant_id']);
					
					//$new_mtid=self::getTableIncrementID("merchant");					
										
					/*insert the merchant info to merchant table*/
					if (!$debug){
					   $DbExt->insertData("{{merchant}}",$merchant_info);
					   $new_mtid=Yii::app()->db->getLastInsertID();
					}
															
					$new_mtid_list.="'$new_mtid',";
										
					/*option*/
					$option_big_sql='';
					if (is_array($val['option']) && count($val['option'])>=1){
						foreach ($val['option'] as $val_opt) {							
							$option_big_sql.="(Null,".self::q($new_mtid).",".self::q($val_opt['option_name']).",".self::q($val_opt['option_value'])."),\n";
						}
						$option_big_sql="INSERT INTO
						`{{option}}`(`id`, `merchant_id`, `option_name`, `option_value`) VALUES\n".
						substr($option_big_sql,0,-2).";";						
						if (!$debug){
						   $DbExt->qry($option_big_sql);
						}
					}
					/*option*/
									
					/*SUBCATEGORY*/			
					//$DbExt->qry(TempDBStructure::Tbl_Subcategory());
					$subcat_id=self::getTableIncrementID("subcategory");				
					if (is_array($val['subcategory']) && count($val['subcategory'])>=1){
						foreach ($val['subcategory'] as $params) {
							$params['subcat_id_new']=$subcat_id++;							
							$params['merchant_id']=$new_mtid;
							$params['subcategory_name_trans']=!empty($params['subcategory_name_trans'])?$params['subcategory_name_trans']:'';
							$params['subcategory_description_trans']=!empty($params['subcategory_description_trans'])?$params['subcategory_description_trans']:'';
							//dump($params);
							$DbExt->insertData("tmp_subcategory",$params);
						}						
						//TempDBStructure::showTempData('tmp_subcategory');
						TempDBStructure::CopySubcategory($new_mtid);
					}
					
					
					/*create temporary table*/					
					//$DbExt->qry(TempDBStructure::Tbl_SubcategoryItem());
					$subcategory_item=self::getTableIncrementID("subcategory_item");
					
					/** SUBCATEGORY ITEM*/
					if (is_array($val['subcategory_item']) && count($val['subcategory_item'])>=1){
						foreach ($val['subcategory_item'] as $val_subitem) {
							$val_subitem['merchant_id']=$new_mtid;
							$val_subitem['sub_item_id_new']=$subcategory_item++;
							$category=!empty($val_subitem['category'])?json_decode($val_subitem['category'],true):false;
							$new_cat_id='';
							if(is_array($category) && count($category)>=1){
								foreach ($category as $cat_id) {									
									$new_cat_id[]=TempDBStructure::getSubcategoryNewId($cat_id);
								}								
								$val_subitem['category_new']=json_encode($new_cat_id);
							} 					
							
							$val_subitem['sub_item_name_trans']=!empty($val_subitem['sub_item_name_trans'])?$val_subitem['sub_item_name_trans']:'';
							$val_subitem['item_description_trans']=!empty($val_subitem['item_description_trans'])?$val_subitem['item_description_trans']:'';
							
							//dump($val_subitem);
							$DbExt->insertData('tpm_subcategory_item',$val_subitem);
						}
						//TempDBStructure::showTempData('tpm_subcategory_item');
						TempDBStructure::CopySubcategoryItem($new_mtid);
					}
										
					
					/*SIZE*/
					//$DbExt->qry(TempDBStructure::Tbl_Size());
					$size_id=self::getTableIncrementID("size");
															
					if (is_array($val['size']) && count($val['size'])>=1){
						foreach ($val['size'] as $val_size) {
							$val_size['size_id_new']=$size_id++;
							$val_size['merchant_id']=$new_mtid;			
							$val_size['size_name_trans']=!empty($val_size['size_name_trans'])?$val_size['size_name_trans']:'';
							$DbExt->insertData("tmp_size",$val_size);
						}
						//TempDBStructure::showTempData('tmp_size');						
						TempDBStructure::CopySize($new_mtid);
					}				
										
					/*INGREDIENTS*/
					//$DbExt->qry(TempDBStructure::Tbl_Ingredients());
					$ingredients_id=self::getTableIncrementID("ingredients");
															
					if (is_array($val['ingredients']) && count($val['ingredients'])>=1){
						foreach ($val['ingredients'] as $params) {
							$params['ingredients_id_new']=$ingredients_id++;
							$params['merchant_id']=$new_mtid;
							$params['ingredients_name_trans']=!empty($params['ingredients_name_trans'])?$params['ingredients_name_trans']:'';
							$DbExt->insertData("tmp_ingredients",$params);
						}
						//TempDBStructure::showTempData('tmp_ingredients');
						TempDBStructure::CopyIngredients($new_mtid);
					}				
					
					/*FOOD CATEGORY*/
					//$DbExt->qry(TempDBStructure::Tbl_Category());
					$next_id=self::getTableIncrementID("category");
					//dump($next_id);
					if (is_array($val['category']) && count($val['category'])>=1){
						foreach ($val['category'] as $params) {
							$params['cat_id_new']=$next_id++;
							$params['merchant_id']=$new_mtid;		
							unset($params['parent_cat_id']);			
							$params['category_name_trans']=!empty($params['category_name_trans'])?$params['category_name_trans']:'';
							$params['category_description_trans']=!empty($params['category_description_trans'])?$params['category_description_trans']:'';							
							$DbExt->insertData("tmp_category",$params);
						}
						//TempDBStructure::showTempData('tmp_category');
						TempDBStructure::CopyCategory($new_mtid);
					}
					
					/*COOKING REF*/
					//$DbExt->qry(TempDBStructure::Tbl_CookingRef());
					$next_id=self::getTableIncrementID("cooking_ref");					
					if (is_array($val['cooking_ref']) && count($val['cooking_ref'])>=1){
						foreach ($val['cooking_ref'] as $params) {
							$params['cook_id_new']=$next_id++;
							$params['merchant_id']=$new_mtid;
							$params['cooking_name_trans']=!empty($params['cooking_name_trans'])?$params['cooking_name_trans']:'';
							$DbExt->insertData("tmp_cooking_ref",$params);
						}
						//TempDBStructure::showTempData('tmp_cooking_ref');
						TempDBStructure::CopyCookingRef($new_mtid);
					}
								
					/*FOOD ITEM*/					
					$next_id=self::getTableIncrementID("item");	
								
					if (is_array($val['item']) && count($val['item'])>=1){
						foreach ($val['item'] as $params) {
							$params['item_id_new']=$next_id++;
							$params['merchant_id']=$new_mtid;							
												
							$price=!empty($params['price'])?json_decode($params['price'],true):false;
							$new_price='';
							if (self::isArray($price)){								
								foreach ($price as $size_id=>$actual_price) {									
									$i=TempDBStructure::getNewID("tmp_size",'size_id','size_id_new',$size_id);
									$new_price[$i]=$actual_price;
								}								
								$params['price']=json_encode($new_price);
							}			
							
							$new_addon_item=''; $sub_item_new='';
							$addon_item=!empty($params['addon_item'])
							?json_decode($params['addon_item'],true):false;
							
							if (self::isArray($addon_item)){									
								foreach ($addon_item as $subcat_id=> $sub_item) {
									$i=TempDBStructure::getNewID("tmp_subcategory",
									'subcat_id','subcat_id_new',$subcat_id);
									if (self::isArray($sub_item)){
										$sub_item_new='';
										foreach ($sub_item as $val_subcat_item) {
											$ii=TempDBStructure::getNewID('tpm_subcategory_item',
											'sub_item_id','sub_item_id_new',$val_subcat_item
											);											
											$sub_item_new[]=$ii;
										}
										$new_addon_item[$i]=$sub_item_new;										
									}																	
									$params['addon_item']=json_encode($new_addon_item);
								}								
							}
							
							$cooking_ref=!empty($params['cooking_ref'])?
							json_decode($params['cooking_ref']):false;
							
							$cooking_ref_new='';
							if(self::isArray($cooking_ref)){
								foreach ($cooking_ref as $val_cooking_ref) {									
									$i=TempDBStructure::getNewID('tmp_cooking_ref','cook_id','cook_id_new',
									$val_cooking_ref);
									$cooking_ref_new[]=$i;
								}					
								$params['cooking_ref']=json_encode($cooking_ref_new);
							}						
							
							$ingredients=!empty($params['ingredients'])?
							json_decode($params['ingredients']):false;
							
							$ingredients_new='';
							if(self::isArray($ingredients)){
								foreach ($ingredients as $val_ingredients) {									
									$i=TempDBStructure::getNewID('tmp_ingredients',
									'ingredients_id','ingredients_id_new',
									$val_ingredients);
									$ingredients_new[]=$i;
								}					
								$params['ingredients']=json_encode($ingredients_new);
							}													
							
							
							$category=!empty($params['category'])?
							json_decode($params['category']):false;
							
							$category_new='';
							if(self::isArray($category)){
								foreach ($category as $val_category) {									
									$i=TempDBStructure::getNewID('tmp_category',
									'cat_id','cat_id_new',
									$val_category);
									$category_new[]=$i;
								}					
								$params['category']=json_encode($category_new);
							}						
							
							
							/*fixed multi_option*/							
                             $multi_option=!empty($params['multi_option'])?json_decode($params['multi_option'],true):false;
							if (is_array($multi_option) && count($multi_option)>=1){							    
							    $new_multi_option='';
							    foreach ($multi_option as $key_multioption=>$val_multioptions) {
							    	$new_id=TempDBStructure::getSubcategoryNewId($key_multioption);	
							    	$new_multi_option[$new_id]=$val_multioptions;
							    	
							    	$params['multi_option_value']=str_replace('"'.$key_multioption.'":',
							    	'"'.$new_id.'":',$params['multi_option_value']);
							    	
							    	$params['two_flavors_position']=str_replace('"'.$key_multioption.'":',
							    	'"'.$new_id.'":',$params['two_flavors_position']);
							    }									    
							    $params['multi_option']=json_encode($new_multi_option);
							}
												
							$DbExt->insertData("tmp_item",$params);
						}						
						TempDBStructure::CopyItem($new_mtid);
					}					
										
				} /*foreach*/
				return $new_mtid_list;
			} /*is array*/
		}
		return false;
	}
	
	public static function isArray($data='')
	{
		if (is_array($data) && count($data)>=1){
			return true;
		}
		return false;
	}
	
	public static function generateNewUserPass($merchant_name='')
	{
		return $merchant_name."_".Yii::app()->functions->generateCode(5);
	}

	public static function q($data='')
	{
		return Yii::app()->db->quoteValue($data);
	}	
	
	public static function getRecords($merchant_id='',$tablename='')
	{
		$DbExt=new DbExt; 	
		$stmt="
		SELECT * FROM
		{{{$tablename}}}
		WHERE
		merchant_id =".Yii::app()->functions->q($merchant_id)."		
		";			
		if ( $res=$DbExt->rst($stmt)){
			return $res;
		}
		return false;
	}
	
	public static function createJsonFile($filename='',$content='')
	{			
		$path_to_upload=Yii::getPathOfAlias('webroot')."/upload/export";
	    if(!file_exists($path_to_upload)) {	
           if (!@mkdir($path_to_upload,0777)){           	    
           	    return ;
           }		    
	    }	   
	    $myFile=$path_to_upload;	    
	    $myFile.= "/$filename";
	    if ( file_exists($myFile)){
	    	@unlink($myFile);
	    }
	    $fh = @fopen($myFile, 'a');
	    $stringData=$content;
	    fwrite($fh, $stringData);                         
	    fclose($fh); 
	}
	
	public static function asTable($header=array(),$data='')
	{
		$head=''; $body='';
		if(is_array($header) && count($header)>=1){
			foreach ($header as $val) {				
				$head.="<th>".$val."</th>";				
			}		
			
			if (is_array($data) && count($data)>=1){
				foreach ($data as $val) {					
					$body.="<tr>";
					$body.="<td>".$val['merchant_id']."</td>";
					$body.="<td>".$val['restaurant_name']."</td>";
					$body.="<td>".$val['street']."</td>";
					$body.="<td>".$val['contact_email']."</td>";
					$body.="<td>".$val['username']."</td>";
					$body.="<td>".$val['username']."</td>";
					$body.="</tr>";
				}
			}
			
			$tables="
			<table class=\"table table-hover\">
			<thead><tr>$head</tr></thead>
			<tbody>$body</tbody>
			</table>
			";
			return $tables;
		}
	}
	
	public static function getTableIncrementID($tablename='')
	{
		$DbExt=new DbExt; 
		$stmt="
		SHOW TABLE STATUS WHERE name='{{{$tablename}}}'
		";		
		if ($res=$DbExt->rst($stmt)){
			return $res[0]['Auto_increment'];
		}
		return false;
	}
		
	public static function importMerchant($data='')
	{		
		$DbExt=new DbExt; 
		
		$food_item='';
		$category='';
		$cooking_ref='';
		$subcategory='';
		$subcategory_item='';
		$option='';
		$size='';
		$ingredients='';
		
		$filename=self::file_export_name;	
		
		/**create temporary table*/
		$DbExt->qry(TempDBStructure::Tbl_Subcategory());
		$DbExt->qry(TempDBStructure::Tbl_SubcategoryItem());
		$DbExt->qry(TempDBStructure::Tbl_Size());
		$DbExt->qry(TempDBStructure::Tbl_Ingredients());
		$DbExt->qry(TempDBStructure::Tbl_Category());
		$DbExt->qry(TempDBStructure::Tbl_CookingRef());
		//$DbExt->qry(TempDBStructure::Tbl_Item());
		TempDBStructure::Tbl_Item();
										
		if(is_array($data) && count($data)>=1){
						
			$debug=false;
			
			if (is_array($data) && count($data)>=1){
				
				$new_mtid_list='';
				foreach ($data as $val) {					
					$merchant_info=$val['merchant'];
															
					if ( self::hasMerchantRecords()){
					   $merchant_info['restaurant_slug']=Yii::app()->functions->createSlug(
					   $merchant_info['restaurant_name']);				
					   $merchant_info['username']=self::generateNewUserPass($merchant_info['restaurant_name']);
					   $merchant_info['password']=md5($merchant_info['username']);
					   $merchant_info['contact_email']=Yii::app()->functions->generateCode(5)."_".$merchant_info['contact_email'];
					} 
					
					$old_mtid=$merchant_info['merchant_id'];
					unset($merchant_info['merchant_id']);
					
					//$new_mtid=self::getTableIncrementID("merchant");					
										
					/*insert the merchant info to merchant table*/
					if (!$debug){
					   $DbExt->insertData("{{merchant}}",$merchant_info);
					   $new_mtid=Yii::app()->db->getLastInsertID();
					}
															
					$new_mtid_list.="'$new_mtid',";
										
					/*option*/
					$option_big_sql='';
					if (is_array($val['option']) && count($val['option'])>=1){
						foreach ($val['option'] as $val_opt) {							
							$option_big_sql.="(Null,".self::q($new_mtid).",".self::q($val_opt['option_name']).",".self::q($val_opt['option_value'])."),\n";
						}
						$option_big_sql="INSERT INTO
						`{{option}}`(`id`, `merchant_id`, `option_name`, `option_value`) VALUES\n".
						substr($option_big_sql,0,-2).";";						
						if (!$debug){
						   $DbExt->qry($option_big_sql);
						}
					}
					/*option*/
									
					/*SUBCATEGORY*/			
					//$DbExt->qry(TempDBStructure::Tbl_Subcategory());
					$subcat_id=self::getTableIncrementID("subcategory");				
					if (is_array($val['subcategory']) && count($val['subcategory'])>=1){
						foreach ($val['subcategory'] as $params) {
							$params['subcat_id_new']=$subcat_id++;							
							$params['merchant_id']=$new_mtid;
							$params['subcategory_name_trans']=!empty($params['subcategory_name_trans'])?$params['subcategory_name_trans']:'';							
							$params['subcategory_description_trans']=!empty($params['subcategory_description_trans'])?$params['subcategory_description_trans']:'';
							$DbExt->insertData("tmp_subcategory",$params);							
						}						
						//TempDBStructure::showTempData('tmp_subcategory');
						TempDBStructure::CopySubcategory($new_mtid);
					}
					
					
					/*create temporary table*/					
					//$DbExt->qry(TempDBStructure::Tbl_SubcategoryItem());
					$subcategory_item=self::getTableIncrementID("subcategory_item");
					
					/** SUBCATEGORY ITEM*/
					if (is_array($val['subcategory_item']) && count($val['subcategory_item'])>=1){
						foreach ($val['subcategory_item'] as $val_subitem) {
							$val_subitem['merchant_id']=$new_mtid;
							$val_subitem['sub_item_id_new']=$subcategory_item++;
							$category=!empty($val_subitem['category'])?json_decode($val_subitem['category'],true):false;
							$new_cat_id='';
							if(is_array($category) && count($category)>=1){
								foreach ($category as $cat_id) {									
									$new_cat_id[]=TempDBStructure::getSubcategoryNewId($cat_id);
								}								
								$val_subitem['category_new']=json_encode($new_cat_id);
							} 					
							//dump($val_subitem);
							
							$val_subitem['sub_item_name_trans']=!empty($val_subitem['sub_item_name_trans'])?$val_subitem['sub_item_name_trans']:'';
							$val_subitem['item_description_trans']=!empty($val_subitem['item_description_trans'])?$val_subitem['item_description_trans']:'';
							
							$DbExt->insertData('tpm_subcategory_item',$val_subitem);
						}
						//TempDBStructure::showTempData('tpm_subcategory_item');
						TempDBStructure::CopySubcategoryItem($new_mtid);
					}
										
					
					/*SIZE*/
					//$DbExt->qry(TempDBStructure::Tbl_Size());
					$size_id=self::getTableIncrementID("size");
															
					if (is_array($val['size']) && count($val['size'])>=1){
						foreach ($val['size'] as $val_size) {
							$val_size['size_id_new']=$size_id++;
							$val_size['merchant_id']=$new_mtid;				
							$val_size['size_name_trans']=!empty($val_size['size_name_trans'])?$val_size['size_name_trans']:'';
							$DbExt->insertData("tmp_size",$val_size);
						}
						//TempDBStructure::showTempData('tmp_size');						
						TempDBStructure::CopySize($new_mtid);
					}				
										
					/*INGREDIENTS*/
					//$DbExt->qry(TempDBStructure::Tbl_Ingredients());
					$ingredients_id=self::getTableIncrementID("ingredients");
															
					if (is_array($val['ingredients']) && count($val['ingredients'])>=1){
						foreach ($val['ingredients'] as $params) {
							$params['ingredients_id_new']=$ingredients_id++;
							$params['merchant_id']=$new_mtid;
							$params['ingredients_name_trans']=!empty($params['ingredients_name_trans'])?$params['ingredients_name_trans']:'';
							$DbExt->insertData("tmp_ingredients",$params);
						}
						//TempDBStructure::showTempData('tmp_ingredients');
						TempDBStructure::CopyIngredients($new_mtid);
					}				
					
					/*FOOD CATEGORY*/
					//$DbExt->qry(TempDBStructure::Tbl_Category());
					$next_id=self::getTableIncrementID("category");
					//dump($next_id);
					if (is_array($val['category']) && count($val['category'])>=1){
						foreach ($val['category'] as $params) {
							$params['cat_id_new']=$next_id++;
							$params['merchant_id']=$new_mtid;		
							unset($params['parent_cat_id']);
							$params['category_name_trans']=!empty($params['category_name_trans'])?$params['category_name_trans']:'';
							$params['category_description_trans']=!empty($params['category_description_trans'])?$params['category_description_trans']:'';
							$DbExt->insertData("tmp_category",$params);
						}
						//TempDBStructure::showTempData('tmp_category');
						TempDBStructure::CopyCategory($new_mtid);
					}
					
					/*COOKING REF*/
					//$DbExt->qry(TempDBStructure::Tbl_CookingRef());
					$next_id=self::getTableIncrementID("cooking_ref");					
					if (is_array($val['cooking_ref']) && count($val['cooking_ref'])>=1){
						foreach ($val['cooking_ref'] as $params) {
							$params['cook_id_new']=$next_id++;
							$params['merchant_id']=$new_mtid;
							$params['cooking_name_trans']=!empty($params['cooking_name_trans'])?$params['cooking_name_trans']:'';
							$DbExt->insertData("tmp_cooking_ref",$params);
						}
						//TempDBStructure::showTempData('tmp_cooking_ref');
						TempDBStructure::CopyCookingRef($new_mtid);
					}
								
					/*FOOD ITEM*/					
					$next_id=self::getTableIncrementID("item");	
								
					if (is_array($val['item']) && count($val['item'])>=1){
						foreach ($val['item'] as $params) {
							$params['item_id_new']=$next_id++;
							$params['merchant_id']=$new_mtid;							
												
							$price=!empty($params['price'])?json_decode($params['price'],true):false;
							$new_price='';
							if (self::isArray($price)){								
								foreach ($price as $size_id=>$actual_price) {									
									$i=TempDBStructure::getNewID("tmp_size",'size_id','size_id_new',$size_id);
									$new_price[$i]=$actual_price;
								}								
								$params['price']=json_encode($new_price);
							}			
							
							$new_addon_item=''; $sub_item_new='';
							$addon_item=!empty($params['addon_item'])
							?json_decode($params['addon_item'],true):false;
							
							if (self::isArray($addon_item)){									
								foreach ($addon_item as $subcat_id=> $sub_item) {
									$i=TempDBStructure::getNewID("tmp_subcategory",
									'subcat_id','subcat_id_new',$subcat_id);
									if (self::isArray($sub_item)){
										$sub_item_new='';
										foreach ($sub_item as $val_subcat_item) {
											$ii=TempDBStructure::getNewID('tpm_subcategory_item',
											'sub_item_id','sub_item_id_new',$val_subcat_item
											);											
											$sub_item_new[]=$ii;
										}
										$new_addon_item[$i]=$sub_item_new;										
									}																	
									$params['addon_item']=json_encode($new_addon_item);
								}								
							}
							
							$cooking_ref=!empty($params['cooking_ref'])?
							json_decode($params['cooking_ref']):false;
							
							$cooking_ref_new='';
							if(self::isArray($cooking_ref)){
								foreach ($cooking_ref as $val_cooking_ref) {									
									$i=TempDBStructure::getNewID('tmp_cooking_ref','cook_id','cook_id_new',
									$val_cooking_ref);
									$cooking_ref_new[]=$i;
								}					
								$params['cooking_ref']=json_encode($cooking_ref_new);
							}						
							
							$ingredients=!empty($params['ingredients'])?
							json_decode($params['ingredients']):false;
							
							$ingredients_new='';
							if(self::isArray($ingredients)){
								foreach ($ingredients as $val_ingredients) {									
									$i=TempDBStructure::getNewID('tmp_ingredients',
									'ingredients_id','ingredients_id_new',
									$val_ingredients);
									$ingredients_new[]=$i;
								}					
								$params['ingredients']=json_encode($ingredients_new);
							}													
							
							
							$category=!empty($params['category'])?
							json_decode($params['category']):false;
							
							$category_new='';
							if(self::isArray($category)){
								foreach ($category as $val_category) {									
									$i=TempDBStructure::getNewID('tmp_category',
									'cat_id','cat_id_new',
									$val_category);
									$category_new[]=$i;
								}					
								$params['category']=json_encode($category_new);
							}
							
							/*fixed multi_option*/							
                            $multi_option=!empty($params['multi_option'])?json_decode($params['multi_option'],true):false;
							if (is_array($multi_option) && count($multi_option)>=1){							    
							    $new_multi_option='';
							    foreach ($multi_option as $key_multioption=>$val_multioptions) {
							    	$new_id=TempDBStructure::getSubcategoryNewId($key_multioption);	
							    	$new_multi_option[$new_id]=$val_multioptions;
							    	
							    	$params['multi_option_value']=str_replace('"'.$key_multioption.'":',
							    	'"'.$new_id.'":',$params['multi_option_value']);
							    	
							    	$params['two_flavors_position']=str_replace('"'.$key_multioption.'":',
							    	'"'.$new_id.'":',$params['two_flavors_position']);
							    }									    
							    $params['multi_option']=json_encode($new_multi_option);
							}
							
							//dump($params);	
													
							$DbExt->insertData("tmp_item",$params);
						}						
						TempDBStructure::CopyItem($new_mtid);
					}
										
				} /*foreach*/
				return $new_mtid_list;
			} /*is array*/
		}
		return false;
	}	
	
	public static function hasMerchantRecords()
	{
		$DbExt=new DbExt;
		$stmt="SELECT * FROM
		{{merchant}}
		LIMIT 0,1
		";
		if ( $res=$DbExt->rst($stmt)){
			return $res;
		}
		return false;
	}
	
}/* end class*/