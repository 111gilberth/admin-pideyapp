<ul class="nav nav-tabs" role="tablist">

    <li role="presentation" class="active">
	<a href="#duplicate" aria-controls="duplicate" role="tab" data-toggle="tab">
	  <?php echo t("Duplicate Merchant")?>
	</a>
	</li>	
		
	<li role="presentation">
	  <a href="#export" aria-controls="export" role="tab" data-toggle="tab">
	   <?php echo t("Export Merchant")?>
	  </a>
	</li>
	<li role="presentation">
	<a href="#import" aria-controls="import" role="tab" data-toggle="tab">
	  <?php echo t("Import Merchant")?>
	</a>
	</li>	
		
	<li role="presentation">
	<a href="<?php echo Yii::app()->createUrl('admin')?>/merchant"><?php echo ("Admin Merchant List")?></a>
	</li>
	
</ul>

<div class="tab-content">

   <div role="tabpanel" class="tab-pane active" id="duplicate">
	   <div class="panel-body">
	   <?php $this->renderPartial('/index/duplicate');?>
	   </div> <!--body-->
	</div><!-- duplicate-->	 
	
	<div role="tabpanel" class="tab-pane" id="export">
	  <div class="panel-body">
	   <?php $this->renderPartial('/index/export');?>
	  </div> <!--body-->
	</div> <!--export-->
	
	<div role="tabpanel" class="tab-pane" id="import">
	   <div class="panel-body">
	   <?php $this->renderPartial('/index/import');?>
	   </div> <!--body-->
	</div><!-- import-->	 
		
	
</div> <!--tab-content-->