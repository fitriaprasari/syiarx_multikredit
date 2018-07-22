<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\models\AccountOfficer;
use yii\bootstrap\ActiveForm;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use yii\widgets\MaskedInputAsset;
use yii\models\Parameterpayroll;
use app\models\Terbilang;
use app\assets\TerbilangAsset;


/* @var $this yii\web\View */
/* @var $model app\modules\payroll\models\Parameterpayroll */

$this->title = 'Formulir Penambahan Parameter';
$this->registerCss(".required label:after { content:' *';color:red; }");
$this->params['breadcrumbs'][] = ['label' => 'Parameterpayrolls', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parameterpayroll-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php echo Yii::$app->session->getFlash('status'); ?>
    <div>&nbsp;</div>
    
    <div class ="form">
        <?php $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
                    'offset' => 'col-sm-offset-3',
                    'wrapper' => 'col-sm-8',
                    'error' => '',
                    'hint' => '',
                    'input' => 'input sm'
                ],
            ],
            'enableAjaxValidation' => false,
            'enableClientValidation' => false
        ]);
        ?>
        
        <!--kode parameter-->
        <?= $form->field($model, 'kode_parameter',['template' => '<div style="float:left;">{label}</div>{input}{error}{hint}'])
                 ->textInput(['maxlength'=>true,'style'=>'text-transform: uppercase' ])?>
        
        
    
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        
        <?php ActiveForm::end(); ?>
    
    
    </div>


</div>