<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
use yii\helpers\Html;
use Yii;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/* @var $this yii\web\View */
$this->title = 'Upload File Data Payroll';
$this->params['breadcrumbs'][] = $this->title;
?>

    <h1><?= Html::encode($this->title) ?></h1>
    <?php echo Yii::$app->session->getFlash('status'); ?>