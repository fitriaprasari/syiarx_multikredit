<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\grid\GridView;
\yii\bootstrap\BootstrapPluginAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel app\modules\payroll\models\Payrollupload_search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Daftar Otorisasi Payroll';
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">
    .row {
        margin-right: 100px;
    }
    #option{
        width: 400px;
        length: 100px;
    }
</style>

<div class="payrollupload-index">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php echo Yii::$app->session->getFlash('status'); ?>
    <?php Pjax::begin(['id' => 'pjax-gridview']) ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['style' => "overflow: auto; height: 600px"],
        'filterModel' => $searchModel,
        'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
            'kode_parameter',
            'nama_file_upload:ntext',
            'nama_file_process:ntext',
            'inputter',
            'authoriser',
            'co_code',
            'date_upload',
            'time_upload',
            'date_exec',
            'time_exec',
//            'valid_stat',
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
                    'read'=> function($url, $model) {
                            return $model->valid_stat == 'VALID' ? Html::a(
                            $url = Html::a('Otorisasi',
                            ['/payroll/otorpayroll/detaildata?kode_parameter='
                            .$model->kode_parameter.'&nama_file_upload='
                            .$model->nama_file_upload],
                            ['class' => 'btn btn-success',
                             'data-method'=>'post',
                            ])):'';
                    },
                            
                    'delete' => function($url, $model) {
                            return Html::a(
                            $url = Html::a('Delete',
                            ['/payroll/otorpayroll/delete?kode_parameter='
                             .$model->kode_parameter.'&nama_file_upload='
                             .$model->nama_file_upload],
                            ['class' => 'btn btn-danger', 'data-method'=>'post',
                             'data'  => [ 
                                    'confirm' => 
                                    'Apakah anda yakin akan menghapus file "'
                                    .$model->nama_file_upload.'" ?',
                                    'method' => 'post',]
                            ]));
                   },
                ],
            ],
        ],
    ]);
    ?>
    
    <?php Pjax::end() ?>

</div>

