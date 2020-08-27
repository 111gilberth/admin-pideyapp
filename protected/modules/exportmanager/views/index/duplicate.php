
<?php echo CHtml::beginForm(); ?>
<div class="row">

<div class="col-md-5">

<h3><?php echo t("Filter merchant")?></h3>
 <!--<div class="radio">
    <label>
      <?php echo CHtml::radioButton('filter_export',false,array(
       'value'=>1,
       'class'=>"filter_export",
       'checked'=>"checked"
      ))?><?php echo t("All Merchant")?>
    </label>
  </div>-->
  
  <!--<div class="radio">
    <label>
      <?php echo CHtml::radioButton('filter_export',false,array(
       'value'=>2,
       'class'=>"filter_export",
       'checked'=>"checked"       
      ))?><?php echo t("Select Merchant")?>
    </label>
  </div>-->
  
 <div class="form-group merchant-selection-wrapx chosen-fixed-width" id="chosen-field">
    <label><?php echo t("Merchant Name")?></label>    
    <?php echo CHtml::dropDownList('merchant_name[]',
    '',
   (array)AddonExportModule::merchantList(true,false),
   array(
    'class'=>'form-control chosen',
    'multiple'=>true
  ))?>
  </div>  
  
</div> <!--col-->
     
<div class="col-md-5">

<h3><?php echo t("Filter options")?></h3>  

<div class="checkbox">
    <label>
      <?php echo CHtml::checkbox('include_item',false,array(
       'value'=>1,
       'class'=>"include_item",       
      ))?><?php echo t("Include Food item")?>
    </label>
  </div>

</div> <!--col-->
  
     
</div> <!--row-->

<div class="display-merchant-wrap"></div> 

<!--<h3><?php echo t("Required Options")?></h3>

<div class="form-group">
<label for="exampleInputEmail1"></label>
<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
</div>-->

<hr/>

<?php
echo CHtml::ajaxSubmitButton(
	'Duplicate',
	array('ajax/duplicate'),
	array(
		'type'=>'POST',
		'dataType'=>'json',
		'beforeSend'=>'js:function(){
		                 busy(true); 
		                 $(".btn-duplicate").prop("disabled", true);
		                 $(".display-merchant-wrap").html("");
		              }
		',
		'complete'=>'js:function(){
		                 busy(false); 
		                 $(".btn-duplicate").prop("disabled", false);
		              }',
		'success'=>'js:function(data){
		               if(data.code==1){
		                  alert(data.msg);
		                  displayReplicateMerchant(data.details);
		               } else {
		                  alert(data.msg);
		               }
		            }
		'
	),array(
	  'class'=>'btn btn-default btn-duplicate'
	)
);
?>
<?php echo CHtml::endForm(); ?>