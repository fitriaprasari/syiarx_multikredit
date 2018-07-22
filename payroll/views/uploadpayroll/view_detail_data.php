<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\payroll\models\PayrollUploadDetail */


$this->params['breadcrumbs'][] = ['label' => 'Daftar Data Payroll', 'url' => ['uploadpayroll/detaildata','kode_parameter' => $model->kode_parameter,'nama_file_upload'=>$model->nama_file_upload]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payroll-upload-detail-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php echo Yii::$app->session->getFlash('status'); ?>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
//            'nama_file_upload',
            'id',
//            'nip',
            'ccy',
            'narasi',
            'kode_parameter',
            'payrollamt',
            'acctno_cr',

        ],
    ]) ?>

</div>