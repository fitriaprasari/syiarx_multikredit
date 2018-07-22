<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\payroll\models\Parameterpayroll */

$this->title = 'Penambahan Parameter Payroll';
$this->params['breadcrumbs'][] = ['label' => 'Parameterpayrolls', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parameterpayroll-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php echo Yii::$app->session->getFlash('status');?>

    <p>
        <?= Html::a('Update', ['updateparam', 'id' => $model->kode_parameter], ['class' => 'btn btn-primary']) ?>
        <?php //Html::a('Delete', ['deleteparam', 'id' => $model->kode_parameter], [
            //'class' => 'btn btn-danger',
            //'data' => [
              //  'confirm' => 'Apakah yakin akan menghapus kode parameter '.$model->kode_parameter.' ?',
                //'method' => 'post',
            //],
        //]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'kode_parameter',
            'nama_institusi',
            'acctno',
            'acctname',
            'co_code',
//            'charge_flag',
//            'charge_code',
//            'charge_type',
//            'acctno_charge',
            'narasi',
            'tipe_transaksi',
            'branch_nm',
//            'charge_amt',
        ],
    ]) ?>
</div>