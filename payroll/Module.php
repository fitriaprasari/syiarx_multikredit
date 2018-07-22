<?php
namespace app\modules\payroll;

use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;

class Module extends BaseModule implements BootstrapInterface {
    
    public $controllerNamespace = 'app\modules\payroll\controllers';

    public function init() {
        parent::init();
    }

    public function bootstrap($app) {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\payroll\commands';
        }
    }

}