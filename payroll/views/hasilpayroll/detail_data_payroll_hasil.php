<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */

use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use app\models\Terbilang;
\yii\bootstrap\BootstrapPluginAsset::register($this);


/* @var $this yii\web\View */
/* @var $searchModel app\modules\payroll\models\Payrollupload_search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $nama_file_upload;
$this->params['breadcrumbs'][] = 'Detail Data Upload Payroll';
?>

<style type="text/css">
    .row {
        margin-right: 100px;
    }
    #option{
        width   : 400px;
        length  : 30px;
    }
</style>

<div class="payrollupload-index">

    <h2><?= Html::encode($this->title) ?></h2>
    
    <div>&nbsp;</div>
    <?php Pjax::begin(); ?>
    <?php
            
              $form = ActiveForm::begin(['action' => ['downloaddetail',
                                                        'id'=>$nama_file_upload]]);
//                $form = ActiveForm::begin(['action' => ['forcedownloadpdf']]);
                echo '<div align="right">';
                    echo '<div class="button-download" >';
                        
                        echo Html::submitButton('Click here to download', [
                            'class' => 'btn btn-primary',
                            'id' => 'button-validasi-download',
                        ]);
                        
                       
                    echo '</div>';
                echo '</div>';
                ActiveForm::end();
                     
    ?>
    <?php Pjax::end(); ?>
    
    <div>&nbsp;</div>
    
    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nama_file_upload:ntext',
            'nama_file_process:ntext',
            'kode_parameter',
            [
                'attribute' => 'acctno',
                'label' => 'No.Rek Debet'
            ],
            [
                'attribute' => 'date_upload',
                'label' => 'Tgl. Upload'
            ],
            [
                'attribute' => 'date_exec',
                'label' => 'Tgl. Eksekusi'
            ],
            [
                'attribute' => 'time_exec',
                'label' => 'Jam Eksekusi'
            ],
//            'status',
            [
                'attribute' => 'co_code',
                'label' => 'Kode Cabang'
            ],
            [
                'value' => $model->inputter." | ".$model->inputter_name,
                'label' => 'Inputter'
            ],
            [
                'value' => $model->authoriser." | ".$model->authoriser_name,
                'label' => 'Authoriser'
            ],
//            [
//                'attribute' => 'charge_flag',
//                'label' => 'Flag Biaya'
//            ],
//            [
//                'attribute' => 'narasi',
//                'label' => 'Keterangan'
//            ],
//            [
//                'attribute' => 'acctno_charge',
//                'label' => 'Rekening Biaya'
//            ],
//            [
//                'value' => "Rp. ".$model->charge_amt." (".strtoupper(Terbilang::toTerbilang($model->charge_amt) . " Rupiah").")",
//                'label' => 'Nominal Biaya'
//            ],
//                                    [
//                'value' => "Rp. ".$sumChargeAmt." (".strtoupper(Terbilang::toTerbilang($sumChargeAmt) . " Rupiah").")",
//                'label' => 'Total Biaya'
//            ],
            [
                'value' => "Rp. ".number_format($sumPayrollamt,2)." (".strtoupper(Terbilang::toTerbilang($sumPayrollamt) . " Rupiah").")",
                'label' => 'Total Nominal Gaji'
            ],
            [
                'value' => "Rp. ".number_format($nominalTerdebit,2)." (".strtoupper(Terbilang::toTerbilang($nominalTerdebit) . " Rupiah").")",
                'label' => 'Total Nominal Terdebit'
            ]
        ],
        
    ])
    ?>

    <div>&nbsp;</div>
    
    <?php // Pjax::begin(['id' => 'pjax-gridview']) ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['style' => "overflow: auto; height: 600px"],
        'showPageSummary'=>true,
        'columns' => [
            'id',
            [
                'attribute' => 'acctno_cr'
            ],
            'acct_title',
            'ccy',
            [
                'attribute' => 'narasi',
                'pageSummary' => "Total Data",
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],
            [
                'attribute' => 'kode_parameter',
                'pageSummary' => $nFile_pay,
            ],
            [
                'attribute' => 'nama_file_upload',
                'pageSummary' => "Total Nominal",
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],
            [ 
                'attribute' => 'payrollamt',
                'pageSummary' => function ($summary, $data, $widget) {
                                return number_format($summary,2);
                },
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
                'format'=>['decimal',2]
            ],
            
            
    ],
        //red sign for unvalid data
        'rowOptions'=>function ($dataProvider){
            if($dataProvider->status != "valid"){
                return ['class'=>'danger'];
            }
        },
    ]);

    ?>
    
    <div>&nbsp;</div>
    
    <?php // Pjax::begin(['id' => 'pjax-gridview']) ?>


</div>

