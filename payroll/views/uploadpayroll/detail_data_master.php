<?php

use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
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
            if($actor == "backoffice"){
                //Tombol Validasi Ulang Data
                $form = ActiveForm::begin(['action' => ['validasiulang',
                                                 'nama_file_upload'=>$nama_file_upload
                                                 ]]);
                echo '<div align="right">';
                    echo '<div class="button-validasi-ulang" >';
                        
                        if($validData != $nFile_pay){
                        echo Html::submitButton('Validasi Ulang Data', [
                            'class' => 'btn btn-success',
                            'id' => 'button-validasi-ulang',
                        ]);
                        }
                       
                    echo '</div>';
                echo '</div>';
                ActiveForm::end();
            }
            else {
            $form = ActiveForm::begin(['action' => ['otor',
                            'nama_file_upload' => $nama_file_upload,
                            'new_status' => "ON_PROCESS"]]);
            
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
    ?>
        <?php Pjax::end(); ?>
    <!--End of Tombol Buat Parameter Baru-->
    
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
            [
                'attribute' => 'charge_flag',
                'label' => 'Flag Biaya'
            ],
//                        [
//                'value' => "Rp. ".$model->charge_amt." (".strtoupper(Terbilang::toTerbilang($model->charge_amt) . " Rupiah").")",
//                'label' => 'Nominal Biaya'
//            ],
//                                    [
//                'value' => "Rp. ".$sumChargeAmt." (".strtoupper(Terbilang::toTerbilang($sumChargeAmt) . " Rupiah").")",
//                'label' => 'Total Biaya'
//            ],
//            [
//                'attribute' =>'narasi',
//                'label' => 'Keterangan'
//            ],
//            [
//                'attribute' => 'acctno_charge',
//                'label' => 'Rekening Biaya'
//            ],
        ],
    ])
    ?>

     <div>&nbsp;</div>
    
   
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['style' => "overflow: auto; height: 300px"],
        'showPageSummary'=>true,
        'columns' => [
            
            ['attribute'=> 'id','label'=>'No. Baris'],
//            ['attribute'=> 'nip','label'=>'NIP'],
            ['attribute'=> 'ccy','label'=>'Kurs'],
            [
                'attribute'=>'acctno_cr',
                'label'=> 'No.Rek. Kredit'
            ],
            [
                'attribute'=>'acct_title',
                'label'=> 'Nama Rekening'
            ],
            [
                'attribute' => 'narasi',
                'pageSummary' => "Total Data",
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],
            ['attribute'=> 'kode_parameter',
                'pageSummary'=> $nFile_pay
            ],
            [
                'attribute' => 'nama_file_upload',
//                'pageSummary' => "Total Nominal",
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],
            [ 
                'attribute' => 'payrollamt',
                'label'=> 'Nominal Gaji',
//                'pageSummary' => function ($summary, $data, $widget) {
//                                return number_format($summary,2);
//                },
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
                'format'=>['decimal',2]
            ],
            'status',
                        
            ['class' => 'kartik\grid\ActionColumn',
                'template' => '{read}',
                
                'buttons' => [
                    'headerOptions' => ['style' => 'width:100%'],
                    'read'=> function($url, $model) {
                    if($model->status != 'valid'){
                                return
                                Html::a($url = Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                                    ['/payroll/uploadpayroll/updatedetaildata?'
                                    .'id_seq='.$model->id_seq
                                    .'& nama_file_upload='.$model->nama_file_upload],
                                    ['class' => 'btn btn-primary',
                                     'data-method'=>'post',]));
                            }
                            else{
                                $aksi_btn = '';
                            }
                    },
                ],
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
