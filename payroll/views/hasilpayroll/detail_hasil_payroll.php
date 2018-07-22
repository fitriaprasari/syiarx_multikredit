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
$this->params['breadcrumbs'][] = 'Hasil Eksekusi Payroll';
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

    <h2><?= $title = str_replace(".csv","",$this->title);
            Html::encode($title) ?></h2>
    <?php echo Yii::$app->session->getFlash('status'); ?>
    <div>&nbsp;</div>
    <?php Pjax::begin(); ?>
    <?php
       $form = ActiveForm::begin(['action' => ['downloadhasil',
                                                'id'=>$nama_file_upload,
                                                //summary hasil payroll
                                                'summary'=>$summary,],
                                  'options'=>['target'=>'_blank']
                                  ]);
//    $form = ActiveForm::begin(['action' => ['downloadhasil']]);
                echo '<div align="right">';
                    echo '<div class="button-download" >';
                        echo '<div>&nbsp;</div>';
                        echo Html::submitButton('Cetak Hasil Laporan', [
                            'class' => 'btn btn-primary',
                            'id' => 'button-validasi-download',
                        ]);
                    echo '</div>';
                echo '</div>';
                ActiveForm::end();
    ?>
    
    <?php Pjax::end(); ?>
    <!--End of Tombol Buat Parameter Baru-->
    <div style="float:right">
        <?php
//        echo Html::a('Cetak Hasil Laporan', ['#'], ['class' => 'btn btn-success', 'onclick' => 'alert("Cetakan ini menggunakan kertas A4 landscape \\ndan tidak direkomendasikan dicetak di printer olivetti."); printa(); return false']);
        ?> 
    </div>
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
                'value' => "Rp. ".number_format($sumAll,2)." (".strtoupper(Terbilang::toTerbilang($sumAll) . " Rupiah").")",
                'label' => 'Total Nominal Debet'
            ],
          
        ],
        
    ])
    ?>

    <div>&nbsp;</div>
    
    <?=
    GridView::widget([
        'dataProvider' => $datas,
        'options' => ['style' => "overflow: auto; height: 400px"],
        'showPageSummary'=>true,
        'columns' => [
            'id',
            'ft_id',
//            'nip',
            'acctno_cr',
            'acct_title',
            'ccy',
            [
                'attribute' => 'narasi',
                'pageSummary' => "Total Data",
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],  
//            [
//                'attribute' => 'sign',                        
//                'pageSummary' => function ($summary, $data, $widget) {
//                                return count($data);
//                                },
//                'headerOptions' => ['class' => 'kv-sticky-column'],
//                'contentOptions' => ['class' => 'kv-sticky-column'],
//            ],
            [
                'attribute'=>'kode_parameter',
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
                'pageSummary' => function ($summary,$data, $widget) {
                                return number_format($summary,2);
                },
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
                'format'=>['decimal',2]
            ],
            'ft_stat',
            [
                'attribute' => 'ft_msg',
                'contentOptions' => [ 'style' => 'width: 200px' ],
            ],
            ['class' => 'kartik\grid\ActionColumn',
                'template' => '{deleteinao}{otor}',
                
                'buttons' => [
                    'headerOptions' => ['style' => 'width:100%'],

                            
                    'deleteinao' => function($url, $model, $aksi_btn) {
                            if($model->ft_stat == "FT Pending. Status INAO"){
                            $aksi_btn="Hapus";
                            $id = $model->ft_id;
                            return Html::a(
                            $url = Html::a($aksi_btn,
                            ['/payroll/hasilpayroll/deleteinao?'
                                .'id='.$id],
                            ['onClick' => 'return confirm("Anda yakin akan menghapus FT ?")',
                             'class'=>'pay btn btn-danger','data-pjax' => '0',
                            ]));
                        }
                   },
//                            
//                    'otor'=> function($url, $model, $aksi_btn) {
//                            if($model->ft_stat == "FT Pending. Status INAO"){
//                                $aksi_btn = "Otor";
//                                $id = $model->ft_id;
//                                return 
//                                Html::a($url = Html::a($aksi_btn,
//                                ['/payroll/hasilpayroll/otorinao?'
//                                .'id='.$id],
//                                ['onClick' => 'return confirm("Anda yakin akan otorisasi FT ?")',
//                                 'class'=>'pay btn btn-primary','data-pjax' => '0',
//                                ]));
//                            }else{
//                                $aksi_btn = '';
//                            }
//                    },
                ],
            ],
    ],  //red sign for unvalid data
        'rowOptions'=>function ($datas){
            if($datas->ft_stat == "FT Gagal"){
                return ['class'=>'danger'];
            }
        },
    ]);
        echo "<table style='width:60%'>";
        echo "<tr style= 'text-align:left'>";
            echo '<th>Total Data Berhasil    :</th>';
            echo '<th>'.$summary['jumlah_transaksi_sukses'].'</th>';
            echo '<th>Total Nominal Berhasil :</th>';
            echo '<th>Rp.'.number_format($summary['jumlah_nominal_transaksi_sukses'],2).'</th>';
        echo '</tr>';
        echo "<tr style= 'text-align:left'>";
            echo '<th>Total Data Gagal       :</th>';
            echo '<th>'.$summary['jumlah_transaksi_gagal'].'</th>';
            echo '<th>Total Nominal Gagal    :'.'</th>';
            echo '<th>Rp.'.number_format($summary['jumlah_nominal_transaksi_gagal'],2).'</th>';
            echo '</tr>';
        echo "<tr style= 'text-align:left'>";
            echo '<th>Total Data             :</th>';
            echo '<th>'.$nFile_pay.' data</th>';
            echo '<th>Total Nominal:</th>';
            echo '<th>Rp.'.number_format($summary['jumlah_nominal'],2).'</th>';
        echo '</tr>';
                
?>
    <div>&nbsp;</div>
</div>
