<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\time\TimePicker;

//use Yii;

/* @var $this yii\web\View */
$this->title = 'Upload File Data Payroll';
$this->params['breadcrumbs'][] = $this->title;
?>


<!--Alert Area-->
<?php

foreach (Yii::$app->session->getAllFlashes() as $key => $message)
{
    if (is_array($message))
    {
        foreach ($message as $item)
        {
            echo '<div class="alert alert-' . $key . '">' . $item . '</div>';
        }
    }
    else
    {
        echo '<div class="alert alert-' . $key . '">' . $message . '</div>';
    }
}
?>

<!--Title Area-->
<div class="page-header" style="margin-top:0px!important">
    <h3><?= Html::encode($this->title) ?></h3><br/>
</div>



<!--Upload Rule Area-->
<p><b>Ketentuan Upload File Data Payroll :</b></p>
<p>1. File Data Payroll disimpan dalam format <b>.csv</b></p>
<p>2. Format nama file adalah sebagai berikut :</p>
<p><b style="color:#bf4040">Tanggalupload</b><b style="color:#002b80">Nomorsequence.</b><b style="color:#008066">KodeParameter</b><b style="color:#000000">.csv</b></p>

<?php echo '<p>Contoh : <b style="color:#bf4040">20180104</b><b style="color:#002b80">01.</b><b style="color:#008066">PARAM</b><b style="color:#000000">.csv</b></p>';
//      echo '<p style='color:blue; border:2px red solid>CSS Styling in php</p>';
?>

<div>&nbsp;</div>

<!--Upload Form Area-->
<?php $form = ActiveForm::begin(['options' =>
                ['enctype' => 'multipart/form-data']])
?>

<?= $form->field($model_master, 'nama_file_upload')->fileInput() ?>

<?=
$form->field($model_master, 'narasi')->textInput(['maxlength' => true, 'style' => 'text-transform: uppercase', 'width:175px', 'readonly' => false, 'onblur' => ''
    . 'if(this.value != ""){'
    . 'if(/[\'^£$%&*()}{@#~?><>,|=_+¬-]/.test(this.value) == true) {'
    . 'alert("Narasi tidak boleh mengandung karakter spesial.");'
    . 'this.value = "";'
    . '}'
    . '}'
]);
?>

<!--date exec-->
<?=
        $form->field($model_master, 'date_exec')
        ->widget(\yii\jui\DatePicker::classname(), [
            //'language' => 'ru',
            'dateFormat' => 'yyyyMMdd',
        ])
?>

<!--time exec-->
<?=
        $form->field($model_master, 'time_exec')
        ->widget(TimePicker::classname(), [
//            'timeFormat'=>'his',
        ]);
?>

<?=
Html::submitButton('Upload', [
    'class' => 'btn btn-primary',
    'onclick' => 'return upload()',
    'id' => 'uploadbutton'
]);
?>
<div>&nbsp;</div>
<div>&nbsp;</div>
<div>&nbsp;</div>
<div>&nbsp;</div>
<?php ActiveForm::end() ?>


