<?php
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\grid\GridView;
\yii\bootstrap\BootstrapPluginAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel app\modules\payroll\models\Payrollupload_search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Daftar Data Upload Payroll';
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">
    .row {
        margin-right: 100px;
    }
    #option{
        width: 300px;
        length: 10px;
    }
</style>

<div class="payrollupload-index">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php echo Yii::$app->session->getFlash('status'); ?>
    <?php Pjax::begin(['id' => 'pjax-gridview']) ?>
    
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['style' => "overflow: auto; height: 500px"],
        'filterModel' => $searchModel,
        'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
            'kode_parameter',
            'nama_file_upload:ntext',
//            'nama_file_process:ntext',
            'inputter',
            'inputter_name',
//            'authoriser',
            'co_code',
            'date_upload',
            'time_upload',
            'date_exec',
            'time_exec',
            'valid_stat',
            'otor_stat',
//          'status',
            'otor_date',
            'otor_tm',
//            'charge_flag',
    //            'acctno_charge',
//            'charge_amt',
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{read}{process}{delete}',
                
                'buttons' => [
                    'headerOptions' => ['style' => 'width:100%'],

                    'read'=> function($url, $model, $aksi_btn) {
                            if($model->valid_stat == 'ERROR'){
                                
                                $aksi_btn = "Edit Data";
                                return 
                                Html::a($url = Html::a($aksi_btn,
                                ['/payroll/uploadpayroll/detaildata?'
                                .'kode_parameter='.$model->kode_parameter
                                .'&nama_file_upload='.$model->nama_file_upload],
                                ['class' => 'btn btn-primary',
                                 'data-method'=>'post',]));
                                
                            }else if($model->valid_stat == 'VALID'){
                                
                                $aksi_btn = "Lihat Detail";
                                return
                                Html::a($url = Html::a($aksi_btn,
                                    ['/payroll/uploadpayroll/detaildata?'
                                    .'kode_parameter='.$model->kode_parameter
                                    .'&nama_file_upload='.$model->nama_file_upload],
                                    ['class' => 'btn btn-primary',
                                     'data-method'=>'post',]));
                            }
                            else{
                                $aksi_btn = '';
                            }
                        },
                            
                    'delete' => function($url, $model) {
//                            return Html::a(
//                            $url = Html::a('Delete',
//                            ['/payroll/uploadpayroll/delete?kode_parameter='
//                             .$model->kode_parameter.'&nama_file_upload='
//                             .$model->nama_file_upload],
//                            ['class' => 'btn btn-danger', 'data-method'=>'post',
//                             'data'  => [ 
//                                    'confirm' => 
//                                    'Apakah anda yakin akan menghapus file "'
//                                    .$model->nama_file_upload.'" ?',
//                                    'method' => 'post',]
//                            ]));
                            
                            if($model->valid_stat == 'ERROR'){
                            return Html::a(
                            $url = Html::a('Delete',
                            ['/payroll/uploadpayroll/deletetemp?kode_parameter='
                             .$model->kode_parameter.'&nama_file_upload='
                             .$model->nama_file_upload],
                            ['class' => 'btn btn-danger', 'data-method'=>'post',
                             'data'  => [ 
                                    'confirm' => 
                                    'Apakah anda yakin akan menghapus file "'
                                    .$model->nama_file_upload.'" ?',
                                    'method' => 'post',]
                            ]));
                                
                            }else if($model->valid_stat == 'VALID'){
                                
                            return Html::a(
                            $url = Html::a('Delete',
                            ['/payroll/uploadpayroll/delete?kode_parameter='
                             .$model->kode_parameter.'&nama_file_upload='
                             .$model->nama_file_upload],
                            ['class' => 'btn btn-danger', 'data-method'=>'post',
                             'data'  => [ 
                                    'confirm' => 
                                    'Apakah anda yakin akan menghapus file "'
                                    .$model->nama_file_upload.'" ?',
                                    'method' => 'post',]
                            ]));
                            }
                            else{
                                $aksi_btn = '';
                            }
                   },
                ],
            ],
        ],
    ]);
    ?>
    
    <?php Pjax::end() ?>

</div>

