<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $model app\modules\payroll\models\PayrollUploadDetail */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Data Payroll';
$this->registerCss(".required label:after { content:' *';color:red; }");
?>
<div class="payroll-upload-detail-form">
    
    <div class="page-header" style="margin-top:0px!important">
        <h2><?= Html::encode($this->title) ?></h2>
    </div>
    
    <?php $form = ActiveForm::begin(); ?>
    
    <!--form ID-->
    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>
    
    <!--form NIP-->
    <?php // $form->field($model, 'nip')->textInput(['maxlength' => true]) ?>
    
    <!--form CCY-->
    <?= $form->field($model,'ccy')->dropDownList(['IDR' => 'IDR', 'USD' => 'USD'],['prompt'=>'Select Option']);?>
    
    <!--form no.rek kredit-->
    <?= $form->field($model, 'acctno_cr')->textInput(['numerical']) ?>
    
    <!--form rekening kredit-->
    <?php 
    $form->field($model,'acctno_cr',[
        'inputTemplate'=>'<div class="">{input}</div>',
         'template'=>'{label}'
        . '<div class="row">'
        . '<div class="col-sm-3">{input}{error}{hint}</div>'
        . '<span style="margin-top:8px; display:block;" id="da_sn"></span>'
        . '</div>'])->textInput(['maxlength'=>true,'readonly'=>false,'onblur'=>''
            . 'if(this.value !=""){'
            . '$("#da-sn-loader").show();'
            . '$.post("'.Url::toRoute("uploadpayroll/getaccountlokal").'",{id: this.value}).done(function(data){'
            . 'var obj = JSON.parse(data);'
            . 'if(obj.status=="NA"){'
            . 'alert("No.Rekening tidak ditemukan.");'
            . '}'
            . 'alert(obj.account_title);'
            . '$("#da-sn-loader").hide();'
            . '});'
            . '}'
            . 'else{'
            . '$("da_sn").html("");'
            . '}'
            ]);
    ?>
<div id="da-sn-loader" style="display: none">
    <img src="../../images/ajax-loader.gif"/>
    <i>Mencari Rekening..</i>
</div>
        
    <!--form nominal gaji-->
    <?= $form->field($model, 'payrollamt')->textInput(['maxlength' => true]) ?>
    
    <!--form narasi-->
    <?php // $form->field($model, 'narasi')->textInput(['maxlength' => true]) ?>
    
    <!--form sign-->
    <?php // $form->field($model, 'sign')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'kode_parameter')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'nama_file_upload')->textInput() ?>

    <?php // $form->field($model, 'ft_id')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'acctno_db')->textInput() ?>

    <?php // $form->field($model, 'charge_amt')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?php //$form->field($model, 'date_process')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>