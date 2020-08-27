<?php $this->renderPartial('/layouts/header');?>

<body>

<a style="padding:10px;display:table;"
href="<?php echo Yii::app()->createUrl('/admin') ?>"><i class="fa fa-long-arrow-left"></i> <?php echo t("Back")?></a>

<div class="container" id="main-wrapper">
  <div class="panel panel-default">
      
     <?php echo $content?>  
    
   </div> <!--panel-->
</div> <!--container-->
</body>

<?php $this->renderPartial('/layouts/footer');?>