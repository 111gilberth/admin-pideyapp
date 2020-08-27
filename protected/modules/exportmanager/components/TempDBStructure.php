<?php
class TempDBStructure
{
	
	public static function Tbl_Subcategory()
	{
		return "CREATE TEMPORARY TABLE `tmp_subcategory` (
		  `subcat_id` int(14) NOT NULL AUTO_INCREMENT,
		  `merchant_id` int(14) NOT NULL,
		  `subcategory_name` varchar(255) NOT NULL,
		  `subcategory_description` text NOT NULL,
		  `discount` varchar(20) NOT NULL,
		  `sequence` int(14) NOT NULL,
		  `date_created` datetime NOT NULL,
		  `date_modified` datetime NOT NULL,
		  `ip_address` varchar(50) NOT NULL,
		  `status` varchar(100) NOT NULL DEFAULT 'publish',
		  `subcategory_name_trans` text NOT NULL,
		  `subcategory_description_trans` text NOT NULL,
		  `subcat_id_new` int(14) NOT NULL,
		   PRIMARY KEY (`subcat_id`)		  
		)  ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";					
	}
	
	public static function showTempData($tablename='')
	{
		$DbExt=new DbExt; 
		$stmt="SELECT * FROM 
		$tablename
		";
		if($res=$DbExt->rst($stmt)){
		   dump($res);
		} //else dump("No records");		
	}
	
	public static function getSubcategoryNewId($subcat_id='')
	{
		$DbExt=new DbExt; 
		$stmt="
		SELECT * FROM
		tmp_subcategory
		WHERE
		subcat_id='".$subcat_id."'
		LIMIT 0,1
		";
		if($res=$DbExt->rst($stmt)){
		   return $res[0]['subcat_id_new'];
		}
		return false;
	}
	
	public static function getNewID($tablename='',$old_field='',$field_new='',$field_val='')
	{
		$DbExt=new DbExt; 
		$stmt="
		SELECT * FROM
		$tablename
		WHERE
		$old_field='".$field_val."'
		LIMIT 0,1
		";		
		if($res=$DbExt->rst($stmt)){				
		   //dump($res);
		   return $res[0][$field_new];
		}
		return false;
	}
	
	public static function Tbl_SubcategoryItem()
	{
		return "CREATE TEMPORARY TABLE tpm_subcategory_item(
		  `sub_item_id` int(14) NOT NULL AUTO_INCREMENT,
		  `merchant_id` int(14) NOT NULL,
		  `sub_item_name` varchar(255) NOT NULL,
		  `item_description` text NOT NULL,
		  `category` varchar(255) NOT NULL,
		  `price` varchar(15) NOT NULL,
		  `photo` varchar(255) NOT NULL,
		  `sequence` int(14) NOT NULL,
		  `status` varchar(50) NOT NULL,
		  `date_created` datetime NOT NULL,
		  `date_modified` datetime NOT NULL,
		  `ip_address` varchar(50) NOT NULL,
		  `sub_item_name_trans` text NOT NULL,
		  `item_description_trans` text NOT NULL,
		  `sub_item_id_new` int(14) NOT NULL,
		  `category_new` varchar(255) NOT NULL,
		   PRIMARY KEY (`sub_item_id`) 
		)  ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 
		";
	}
	
	public static function Tbl_Size()
	{
		return "CREATE TEMPORARY TABLE tmp_size(
		
		`size_id` int(14) NOT NULL AUTO_INCREMENT,
	    `merchant_id` int(14) NOT NULL,
	    `size_name` varchar(255) NOT NULL,
	    `sequence` int(14) NOT NULL,
	    `status` varchar(50) NOT NULL DEFAULT 'published',
	    `date_created` datetime NOT NULL,
	    `date_modified` datetime NOT NULL,
	    `ip_address` varchar(50) NOT NULL,
	    `size_name_trans` text NOT NULL,
	    `size_id_new` int(14) NOT NULL,
	     PRIMARY KEY (`size_id`)		
		)  ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 
		";
	}
	
	public static function Tbl_Ingredients()
	{
		return "CREATE TEMPORARY TABLE tmp_ingredients (
		  `ingredients_id` int(14) NOT NULL AUTO_INCREMENT,
		  `merchant_id` int(14) NOT NULL,
		  `ingredients_name` varchar(255) NOT NULL,
		  `sequence` int(14) NOT NULL,
		  `date_created` datetime NOT NULL,
		  `date_modified` datetime NOT NULL,
		  `status` varchar(50) NOT NULL DEFAULT 'published',
		  `ip_address` varchar(50) NOT NULL,
		  `ingredients_name_trans` text NOT NULL,
		  `ingredients_id_new` int(14) NOT NULL,
		   PRIMARY KEY (`ingredients_id`)
		)  ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 
		";
	}
	
	public static function Tbl_Category()
	{
		/*return "CREATE TEMPORARY TABLE tmp_category(
		  `cat_id` int(14) NOT NULL AUTO_INCREMENT,
		  `merchant_id` int(14) NOT NULL,
		  `category_name` varchar(255) NOT NULL,
		  `category_description` text NOT NULL,
		  `photo` varchar(255) NOT NULL,
		  `status` varchar(100) NOT NULL,
		  `sequence` int(14) NOT NULL,
		  `date_created` varchar(50) NOT NULL,
		  `date_modified` varchar(50) NOT NULL,
		  `ip_address` varchar(50) NOT NULL,
		  `spicydish` int(2) NOT NULL DEFAULT '1',
		  `spicydish_notes` text NOT NULL,
		  `dish` text NOT NULL,
		  `category_name_trans` text NOT NULL,
		  `category_description_trans` text NOT NULL,
		  `cat_id_new` int(14) NOT NULL,
		  PRIMARY KEY (`cat_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 
		";*/
		$DbExt=new DbExt;
		$stmt="CREATE TEMPORARY TABLE tmp_category LIKE {{category}}";
		if ( $res=$DbExt->qry($stmt)){			
			$stmt_alter="ALTER TABLE tmp_category
			ADD COLUMN cat_id_new int(14) NOT NULL
			 ";
			$DbExt->qry($stmt_alter);			
		} 
		unset($DbExt);
	}
	
	public static function Tbl_CookingRef()
	{
		return "
			CREATE TEMPORARY TABLE `tmp_cooking_ref` (
			  `cook_id` int(14) NOT NULL AUTO_INCREMENT,
			  `merchant_id` int(14) NOT NULL,
			  `cooking_name` varchar(255) NOT NULL,
			  `sequence` int(14) NOT NULL,
			  `date_created` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  `status` varchar(50) NOT NULL DEFAULT 'published',
			  `ip_address` varchar(50) NOT NULL,
			  `cooking_name_trans` text NOT NULL,
			  `cook_id_new` int(14) NOT NULL,
			  PRIMARY KEY (`cook_id`)
			)  ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 		
		";
	}
	
	public static function Tbl_Item()
	{				
		$DbExt=new DbExt;
		$stmt="CREATE TEMPORARY TABLE tmp_item LIKE {{item}}";
		if ( $res=$DbExt->qry($stmt)){			
			$stmt_alter="ALTER TABLE tmp_item
			ADD COLUMN item_id_new int(14) NOT NULL
			 ";
			$DbExt->qry($stmt_alter);			
		} 
	}	
	
	public static function CopySize($mtid='')
	{
		$DbExt=new DbExt;
		$stmt="
		  INSERT INTO {{size}}(
			  size_id,
			  merchant_id,
			  size_name,
			  sequence,
			  status,
			  date_created,
			  date_modified,
			  ip_address,
			  size_name_trans
		  )
		  		  
		  SELECT 
		  size_id_new,
		  merchant_id,
		  size_name,
		  sequence,
		  status,
		  date_created,
		  date_modified,
		  ip_address,
		  size_name_trans
		  FROM tmp_size
		  WHERE
		  merchant_id='".$mtid."'
		";		
		$DbExt->qry($stmt);
	}
	
	public static function CopyIngredients($mtid='')
	{
		$DbExt=new DbExt;
		$stmt="
		  INSERT INTO {{ingredients}}(
			  ingredients_id,
			  merchant_id,
			  ingredients_name,
			  sequence,
			  date_created,
			  date_modified,
			  status,
			  ip_address,
			  ingredients_name_trans
		  )
		  		  
		  SELECT 
		  ingredients_id_new,
		  merchant_id,
		  ingredients_name,
		  sequence,
		  date_created,
		  date_modified,
		  status,
		  ip_address,
		  ingredients_name_trans
		  FROM tmp_ingredients
		  WHERE merchant_id='".$mtid."'
		";		
		$DbExt->qry($stmt);
	}
	
    public static function CopyCookingRef($mtid='')
	{
		$DbExt=new DbExt;
		$stmt="
		  INSERT INTO {{cooking_ref}}(
		    cook_id,
		    merchant_id,
		    cooking_name,
		    sequence,
		    date_created,
		    date_modified,
		    status,
		    ip_address,
		    cooking_name_trans
		  )
		  		  
		  SELECT 
		  cook_id_new,
		  merchant_id,
		  cooking_name,
		  sequence,
		  date_created,
		  date_modified,
		  status,
		  ip_address,
		  cooking_name_trans
		  FROM tmp_cooking_ref
		  WHERE merchant_id='".$mtid."'
		";		
		$DbExt->qry($stmt);
   }	
			
   public static function CopySubcategory($mtid='')
   {
		$DbExt=new DbExt;
		$stmt="
		  INSERT INTO {{subcategory}}(
		    subcat_id,
		    merchant_id,
		    subcategory_name,
		    subcategory_description,
		    discount,
		    sequence,
		    date_created,
		    date_modified,
		    ip_address,
		    status,
		    subcategory_name_trans,
		    subcategory_description_trans
		  )
		  		  
		  SELECT 
		    subcat_id_new,
		    merchant_id,
		    subcategory_name,
		    subcategory_description,
		    discount,
		    sequence,
		    date_created,
		    date_modified,
		    ip_address,
		    status,
		    subcategory_name_trans,
		    subcategory_description_trans
		  FROM tmp_subcategory
		  WHERE merchant_id='".$mtid."'
		";		
		$DbExt->qry($stmt);
   }	
	
   public static function CopySubcategoryItem($mtid='')
   {
		$DbExt=new DbExt;
		$stmt="
		  INSERT INTO {{subcategory_item}}(
		    sub_item_id,
		    merchant_id,
		    sub_item_name,
		    item_description,
		    category,
		    price,
		    photo,
		    sequence,
		    status,
		    date_created,
		    date_modified,
		    ip_address,
		    sub_item_name_trans,
		    item_description_trans
		  )
		  		  
		  SELECT 
		    sub_item_id_new,
		    merchant_id,
		    sub_item_name,
		    item_description,
		    category_new,
		    price,
		    photo,
		    sequence,
		    status,
		    date_created,
		    date_modified,
		    ip_address,
		    sub_item_name_trans,
		    item_description_trans
		  FROM tpm_subcategory_item
		  WHERE merchant_id='".$mtid."'
		";		
		$DbExt->qry($stmt);
	}		
	
   public static function CopyCategory($mtid='')
   {
		$DbExt=new DbExt;
		$stmt="
		  INSERT INTO {{category}}(
		    cat_id,
		    merchant_id,
		    category_name,
		    category_description,
		    photo,
		    status,
		    sequence,
		    date_created,
		    date_modified,
		    ip_address,
		    spicydish,
		    spicydish_notes,
		    dish,
		    category_name_trans,
		    category_description_trans
		  )
		  		  
		  SELECT 
		    cat_id_new,
		    merchant_id,
		    category_name,
		    category_description,
		    photo,
		    status,
		    sequence,
		    date_created,
		    date_modified,
		    ip_address,
		    spicydish,
		    spicydish_notes,
		    dish,
		    category_name_trans,
		    category_description_trans
		  FROM tmp_category
		  WHERE merchant_id='".$mtid."'
		";		
		$DbExt->qry($stmt);
	}		
	
    public static function CopyItemOld($mtid='')
	{
		$DbExt=new DbExt;
		$stmt="
		  INSERT INTO {{item}}(
		    item_id,
		    merchant_id,
		    item_name,
		    item_description,
		    status,
		    category,
		    price,
		    addon_item,
		    cooking_ref,
		    discount,
		    multi_option,
		    multi_option_value,
		    photo,
		    sequence,
		    is_featured,
		    date_created,
		    date_modified,
		    ip_address,
		    ingredients,
		    spicydish,
		    two_flavors,
		    two_flavors_position,
		    require_addon,
		    dish,
		    item_name_trans,
		    item_description_trans,
		    non_taxable,
		    not_available
		  )
		  		  
		  SELECT 
		    item_id_new,
		    merchant_id,
		    item_name,
		    item_description,
		    status,
		    category,
		    price,
		    addon_item,
		    cooking_ref,
		    discount,
		    multi_option,
		    multi_option_value,
		    photo,
		    sequence,
		    is_featured,
		    date_created,
		    date_modified,
		    ip_address,
		    ingredients,
		    spicydish,
		    two_flavors,
		    two_flavors_position,
		    require_addon,
		    dish,
		    item_name_trans,
		    item_description_trans,
		    non_taxable,
		    not_available
		  FROM tmp_item
		  WHERE merchant_id='".$mtid."'
		";				
		$DbExt->qry($stmt);
	}	

	public static function CopyItem($mtid='')
	{
		$DbExt=new DbExt;
		if ($fields=self::getTableStructuredFields('item')){
			$fields2=$fields;
			$fields2=str_replace("item_id",'item_id_new',$fields2);
			$stmt="
			INSERT INTO {{item}}(
			  $fields
			)			
			SELECT
			$fields2
			FROM tmp_item
		    WHERE merchant_id='".$mtid."'
			";			
			if ($DbExt->qry($stmt)){
				return true;
			}
		}		
		return false;			
	}
	
	public static function getTableStructuredFields($table_name='')
	{
		$DbExt=new DbExt;
		$stmt="DESCRIBE {{{$table_name}}}";
		$field_list='';
		if ($res=$DbExt->rst($stmt)){			
			foreach ($res as $val) {				
				$field_list.="$val[Field],\n";
			}
			$field_list=substr($field_list,0,-2);
			return $field_list;
		}
		return false;
	}
		
} /*end class*/