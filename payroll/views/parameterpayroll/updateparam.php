 <?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\payroll\models\Parameterpayroll */

$this->title = 'Update Parameterpayroll: ' . ' ' . $model->kode_parameter;
$this->params['breadcrumbs'][] = ['label' => 'Parameterpayrolls', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->kode_parameter, 'url' => ['view', 'id' => $model->kode_parameter]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="parameterpayroll-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
