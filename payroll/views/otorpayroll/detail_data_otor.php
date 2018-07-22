<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\helpers\Url;
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
//                'value' => $model->narasi,
//                'label' => 'Keterangan'
//            ],
//            [
//                'attribute' => 'acctno_charge',
//                'label' => 'Rekening Biaya'
//            ],
//                        [
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
                'value' => "Rp. ".number_format($sumAll,2)." (".strtoupper(Terbilang::toTerbilang($sumAll) . " Rupiah").")",
                'label' => 'Total Nominal Debet'
            ],
            [
                'value' => "Rp. ".number_format($saldo_akhir_rek,2) . " (".strtoupper(Terbilang::toTerbilang($saldo_akhir_rek) . (" Rupiah)")),
                'label' => 'Saldo Rek. Debet'
            ],
            [
                'value' => $status_bal,
                'label' => 'Status :'
            ],
                        [
                'value' => 'DATA AKAN DIEKSEKUSI '.$diff->format("%R%a HARI").' LAGI',
                'label' => 'Pesan'
            ],
        ],
        
    ])
    ?>

     <div>&nbsp;</div>
    <?php Pjax::begin(); ?>
    <?php
            if($actor == "backoffice"){
                //Tombol Validasi Ulang Data
                $form = ActiveForm::begin(['action' => ['validasiulang',
                                                 'nama_file_upload'=>$nama_file_upload,
                                                 'new_status'=>"ON_PROCESS"]]);
                echo '<div align="right">';
                    echo '<div class="button-validasi-ulang" >';
                        
                        if($validData != $nFile_pay){
                        echo Html::submitButton('Validasi Ulang Data', [
                            'class' => 'btn btn-primary',
                            'id' => 'button-validasi-ulang',
                        ]);
                        }
                       
                    echo '</div>';
                echo '</div>';
                ActiveForm::end();
            }
            elseif($actor == "supervisor" && $allow_otor == true) {
            $form = ActiveForm::begin(['action' => ['otor',
                            'nama_file_upload' => $nama_file_upload,
                            'co_code'=>$co_code]]);
            
            echo '<div align="right">';
                echo '<div class="button-validasi-ulang" >';
                    echo Html::submitButton('Otorisasi', [
                            'class' => 'btn btn-primary',
                            'id' => 'button-validasi-ulang',
                        ]);
                echo '</div>'; 
            echo '</div>';
            ActiveForm::end();
            }
            else{echo '<div align="right">';
                echo '<div class="button-validasi-ulang" >';
                    echo Html::submitButton('Otorisasi', [
                            'class' => 'btn btn-primary',
                            'id' => 'button-validasi-ulang',
                            'disabled' => 'disabled',
                        ]);
                echo '</div>'; 
            echo '</div>';}
    ?>
    <?php Pjax::end(); ?>
    <!--End of Tombol Buat Parameter Baru-->
    
    <div>&nbsp;</div>
    
    <?php // Pjax::begin(['id' => 'pjax-gridview']) ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['style' => "overflow: auto; height: 600px"],
        'showPageSummary'=>true,
        'columns' => [
            'id',
            'acctno_cr',
            'acct_title',
            'ccy',
            [
                'attribute' => 'narasi',
                'pageSummary' => "Total Data",
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],  
            ['attribute'=>'kode_parameter',
             'pageSummary'=> $nFile_pay
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
            'status',
                        
            
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
