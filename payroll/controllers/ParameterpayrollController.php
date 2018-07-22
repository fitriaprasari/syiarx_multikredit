<?php
/* 
 * ParameterpayrollController
 * Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\modules\payroll\models\Parameterpayroll;
use app\modules\payroll\models\PayrollUploadMaster;
use app\modules\payroll\models\Parameterpayroll_search;
use app\modules\payroll\models\PayrollUploadDetail;

use yii\filters\VerbFilter;

class ParameterpayrollController extends \yii\web\Controller{

    
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $session = Yii::$app->session;
        $co_code = Yii::$app->user->identity->branch_cd;
        $status = false;

        $model = new Parameterpayroll();
        $model->setScenario('create');
        $model->co_code = $co_code;

        if (($model->load($request->post()) && $model->validate()))
        {
            $model->kode_parameter = strtoupper($model->kode_parameter);
            $model->nama_institusi = strtoupper($model->nama_institusi);
            $model->narasi = strtoupper($model->narasi);
            $model->tipe_transaksi = strtoupper($model->tipe_transaksi);
            $id = $model->kode_parameter;
         
            if ($model->charge_amt == NULL)
            {
                $model->charge_amt = "";
            }
            else
            {
                if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $model->charge_amt))
                {
                    $model->charge_amt = str_replace(",", "", $model->charge_amt);
                    
                }
                else
                {
                    $model->charge_amt = $model->charge_amt;
                }
            }
            //check if parameter payroll already inserted
            $exists = Parameterpayroll::find()->where(['kode_parameter' => $id, 'co_code' => $co_code])->exists();
            if ($exists)
            {
                //it exists
                Yii::$app->session->setFlash('status', '<div class="alert alert-warning">Silahkan masukan kode parameter yang berbeda</div>');
                return $this->render('add_parameter', [
                            'model' => $model,
                ]);
            }
            else
            {
                //doesn't exist so create record
                //1. save parameter payroll data
                $model->save();
                Yii::$app->session->setFlash('status', '<div class="alert alert-warning">Parameter Payroll berhasil disimpan.</div>');
                return $this->redirect(['viewdetailparam', 'id' => $model->kode_parameter]);
            }
        }
        return $this->render('add_parameter', [
                    'model' => $model,
                    'status'=>$status
        ]);
    }

    /**
     * Displays a single Parameterpayroll model.
     * @param string $id
     * @return mixed
     */
    public function actionViewdetailparam($id){
        $title = 'Penambahan Parameter Payroll';
        $model = Parameterpayroll::find()->where(['kode_parameter'=>$id])->one();
        
        if($model == null){
            throw new NotFoundHttpException($id.' | Parameter Payroll tidak ditemukan.');
        }else{
            return $this->render('view',['model' => $model,'aksi'=>'view','title'=>$title]);
        }
        
    }
//    Yii::$app->session->setFlash('status','<div class="alert alert-warning">Parameter Payroll berhasil disimpan</div>');

    public function actionIndex(){
        $co_code = Yii::$app->user->identity->branch_cd;
        $searchModel = new \app\modules\payroll\models\Parameterpayroll_search();
        $dataProvider = $searchModel->search(Yii::$app->request->post());
  
        return $this->render('index',[
            'searchModel'=> $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Updates an existing Parameterpayroll model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateparam($id)
    {
        $title = 'Update Parameter Payroll';
        $model = $this->findModel($id);
        $id_lama = $id;
		$status = true;
        $model->setScenario('updateparam');
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->kode_parameter = strtoupper($model->kode_parameter);
            $model->nama_institusi = strtoupper($model->nama_institusi);
            $model->narasi = strtoupper($model->narasi);
            $model->tipe_transaksi = strtoupper($model->tipe_transaksi);
            if($model->charge_amt == NULL){
                $model->charge_amt = "";
            }else{
                if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $model->charge_amt))
                {
                    $model->charge_amt =str_replace(",","",$model->charge_amt);
//                    var_dump($model->charge_amt);die;
                }
                else
                {
                    $model->charge_amt =$model->charge_amt;
                }
            }

            if($model->charge_flag == "T"){
                $model->charge_amt = "";
            }
            $model->save();
            //update data masters
            PayrollUploadMaster::updateParam($model->acctno_charge,
                                             $model->charge_amt,$id_lama,
                                             $id);

            Yii::$app->session->setFlash('status', '<div class="alert alert-warning">Update Parameter Payroll Berhasil</div>');
			
            return $this->redirect(['viewdetailparam', 'id' => $model->kode_parameter]);
        } else {
            return $this->render('add_parameter', [
                'model' => $model,
                'title'=>$title,
				'status'=>$status
            ]);
        }
    }
    
     /**
     * Deletes an existing Parameterpayroll model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteparam($id){
        //delete data parameter payroll
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('status', '<div class="alert alert-warning">Data parameter berhasil dihapus.</div>');
        return $this->redirect(['index']);
    }
    
    public function actionGetbranchname(){
        $id=isset($_POST['id']) ? $_POST['id'] : '';
        $payroll = new Parameterpayroll();
        $msgresp = json_encode($payroll->getBranchname($id));
        return $msgresp;
    }
    
    public function actionGetaccount(){
        $id=isset($_POST['id']) ? $_POST['id'] :'';
        $payroll = new Parameterpayroll();
        $msgresp = json_encode($payroll->getAccount($id));
        return $msgresp;
    }
        
    public function actionGetparamby(){
        $key = strtoupper(isset($_POST['query_form'])) ? $_POST['query_form'] : '';
        $column_name = isset($_POST['parameterpayroll'])? $_POST['parameterpayroll'] : '';
        
        $searchModel = new Parameterpayroll_search();
        $dataProvider = $searchModel->searchBy($column_name, $key);
        return $this->render('index',[
            'searchModel'=> $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
     /**
     * Finds the Parameterpayroll model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Parameterpayroll the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Parameterpayroll::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
   
    
    

    


}