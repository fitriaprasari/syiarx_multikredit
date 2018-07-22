<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->title = "Daftar Parameter Payroll";
?>

<div class="page-header" style="margin-top:0px!important">
    <h2><?= Html::encode($this->title) ?></h2>
</div>

<div class="daftar-parameter-payroll">
<?php 
    foreach(Yii::$app->session->getAllFlashes() as $key => $message){
        if(is_array($message)){
            foreach ($message as $item){
                echo '<div class = "alert alert-'.$key.'">'. $item. '</div>';
            }
        } else
            echo '<div class="alert alert-'.$key.'">'.$message.'</div>';
    }

?>
    
    
</div>


