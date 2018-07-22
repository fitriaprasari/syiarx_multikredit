<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
\yii\bootstrap\BootstrapPluginAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel app\modules\payroll\models\Payrollupload_search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Daftar Hasil Payroll';
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">
    .row {
        margin-right: 100px;
    }
    #option{
        width: 400px;
        length: 50px;
    }
</style>

<div class="payrollupload-index">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php echo Yii::$app->session->getFlash('status'); ?>
    <?php Pjax::begin(['id' => 'pjax-gridview']) ?>
    
        <div class="form">
            <!--Fitur Pencarian-->
            <?php $form = ActiveForm::begin(['layout'=>'inline',
                                             'action' => ['gethasil'],
                                             'method'=>'post']); ?>
            
           <?php
            echo '<br>';
            echo 'Masukkan Tanggal Pencarian :';
            echo '<br>';
            echo $form->field($model, 'date_exec')
                    ->widget(\yii\jui\DatePicker::classname(), [
                        //'language' => 'ru',
                        'dateFormat' => 'yyyyMMdd'
                    ]);
            
            ?>
            
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>

            <?php ActiveForm::end(); ?>
            <!--End of Fitur Pencarian-->
                        
        </div>
    <?php // } else {?>
                    
    <br>
        
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['style' => "overflow: auto; height: 600px"],
        'filterModel' => $searchModel,
        'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
            'kode_parameter',
            'nama_file_upload:ntext',
//            'nama_file_process:ntext',
            'inputter',
            'inputter_name',
            'authoriser',
            'authoriser_name',
//            'co_code',
//            'date_upload',
//            'time_upload',
            'otor_date',
            'otor_tm',
            'date_exec',
            'time_exec',
            'date_process',
            'exec_stat',
            
            ['class' => 'yii\grid\ActionColumn',
//                'template' => '{read}{process}{delete}',
                'template' => '{read}{process}',
                'buttons' => [
                    'headerOptions' => ['style' => 'width:100%'],
                    'read'=> function($url, $model, $aksi_btn) {
                            if(($model->exec_stat == 'Eksekusi selesai. Terdapat gagal FT')||
                                    ($model->exec_stat == 'Eksekusi selesai. Seluruh FT berhasil')){
                                $aksi_btn = "Inquiry Hasil";
                                return
                                Html::a($url = Html::a($aksi_btn,
                                ['/payroll/hasilpayroll/inquiryhasil?'
                                . 'kode_parameter='.$model->kode_parameter
                                . '&nama_file_upload='.$model->nama_file_upload],
                                [ 'class' => 'btn btn-primary',
                                  'data-method'=>'post',]));
                            }else{
                                $aksi_btn='';
                            }
                        },
                                    
                    'process' => function($url, $model) {
                            return Html::a(
                            $url = Html::a('Lihat Detail',
                            ['/payroll/hasilpayroll/detaildata?kode_parameter='
                             .$model->kode_parameter.'&nama_file_upload='
                             .$model->nama_file_upload],
                            ['class' => 'btn btn-secondary', 'data-method'=>'post',
                             'data-method' => 'post',
                            ]));
                   },
                ],
            ],
        ],
        
    ]);
    ?>
        
    <?php // } ?>
       
    <?php Pjax::end() ?>

    