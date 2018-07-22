<?php
/* 
 * OtorpayrollController
 * Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\controllers;
use Yii;
use app\modules\payroll\models\PayrollUploadMaster;
use app\modules\payroll\models\PayrollUploadDetail;
use app\modules\payroll\models\PayrollUploadMaster_search;
use app\modules\payroll\models\Payrollwsclient;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;

/**
 * PayrollotorController implements the CRUD actions for PayrollUploadMaster model.
 */
class OtorpayrollController extends Controller
{
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

    /**
     * Lists all PayrollUploadMaster models.
     * @return mixed
     */
    public function actionIndexotor(){
        $co_code = Yii::$app->user->identity->branch_cd;
        $searchModel = new PayrollUploadMaster_search();
        $dataProvider = $searchModel->searchNau(Yii::$app->request->queryParams,$co_code);

        return $this->render('daftar_otor_payroll', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PayrollUploadMaster model.
     * @param string $kode_parameter
     * @param string $nama_file_upload
     * @return mixed
     */
    public function actionView($kode_parameter, $nama_file_upload)
    {
        return $this->render('view', [
            'model' => $this->findModel($kode_parameter, $nama_file_upload),
        ]);
    }

    /**
     * Creates a new PayrollUploadMaster model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PayrollUploadMaster();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'kode_parameter' => $model->kode_parameter, 'nama_file_upload' => $model->nama_file_upload]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionDetaildata($kode_parameter,$nama_file_upload){
        $status_bal = '';
        $model = (new \yii\db\Query())
        ->select(['payroll_parameter.*','payroll_upload_master.*'])
        ->from(['payroll_parameter','payroll_upload_master'])
        ->where(['payroll_parameter.kode_parameter'=>$kode_parameter,
                 'payroll_upload_master.nama_file_upload'=>$nama_file_upload])
        ->one();
        
        $now = date_create('Y-m-d'); // or your date as well
        $date_now = date_create(".$now.");
        $date_exec = date_create($model['date_exec']);
        $diff=date_diff($date_now,$date_exec);
        
        $searchModel = new PayrollUploadDetail();
        $dataProvider = new ArrayDataProvider([
            'allModels'=> $searchModel->searchOnly($nama_file_upload),
            'pagination' => array('pageSize' => 1000),
            'sort' => [
                        'attributes' => ['id'],
                      ],
        ]);
        
        $model_detail = new PayrollUploadDetail();
        $nFile_pay = $model_detail->find()->where(['nama_file_upload'=>$nama_file_upload])->count();
        $sumPayrollamt = $model_detail->searchSumPayrollAmt($nama_file_upload);
//        var_dump($sumPayrollamt);die;
        
        $validData = $model_detail->countValid($nama_file_upload);
        $co_code = Yii::$app->user->identity->branch_cd;
        $actor = "supervisor";
        $model = ((object)$model);
       
        if ($model->narasi == "BIAYA DIBEBANKAN KE REKENING PERUSAHAAN")
        {
            $sumChargeAmt = $model_detail->searchSumChargeAmt($nama_file_upload);
            $sumAll = $model_detail->sumAll($sumPayrollamt, $sumChargeAmt);
        }
        else
        {
            $sumAll = $sumPayrollamt;
            $sumChargeAmt = "";
        }


        $objreq = array(
            "userid" => Yii::$app->user->identity->t24_login_name,
            "password" => Yii::$app->user->identity->gett24pass(),
            "cocode" => Yii::$app->user->identity->branch_cd,
            "accno" => $model->acctno);
        //Inquiry Saldo
        $syiarws_bal = new Payrollwsclient();
        
        $msgresp = $syiarws_bal->balance($objreq);
        $saldo_akhir_rek = str_replace(",", "", $msgresp['c7']);
        
        if ($saldo_akhir_rek < $sumAll){
            $status_bal = "SALDO REKENING DEBET TIDAK MENCUKUPI";
            $allow_otor = false;
        }else{
            $status_bal = "SALDO REKENING DEBET MENCUKUPI";
            $allow_otor = true;
        }
        
        //selection of button
//        $otor_stat = $model['otor_stat'];
        return $this->render('detail_data_otor',[
            //untuk info detail parameter payroll
            'validData'=> $validData,
            'nama_file_upload' => $nama_file_upload,
            'nFile_pay' => $nFile_pay,
            'actor'=> $actor,
            'sumPayrollamt' => $sumPayrollamt,
//            'sumChargeAmt'=> $sumChargeAmt,
            'sumAll'=> $sumAll,
            'actor'=> $actor,
            'co_code'=> $co_code,
            'model'=> $model,
            'allow_otor' => $allow_otor,
            'status_bal' => $status_bal,
            'saldo_akhir_rek' => $saldo_akhir_rek,
            'diff'=> $diff,
            'inputter'=> $model->inputter.' | '.$model->inputter_name,
            
            //untuk memunculkan isi file upload payroll
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        
    }

    /**
     * Updates an existing PayrollUploadMaster model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $kode_parameter
     * @param string $nama_file_upload
     * @return mixed
     */
    public function actionUpdate($kode_parameter, $nama_file_upload)
    {
        $model = $this->findModel($kode_parameter, $nama_file_upload);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'kode_parameter' => $model->kode_parameter, 'nama_file_upload' => $model->nama_file_upload]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionOtor($nama_file_upload,$co_code){
        //update data master
        $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            $otor_date = date('Y-m-d');
            $otor_tm = date('H:i:s');
            $otor_stat = "AUTHORIZED";
            $authoriser = Yii::$app->user->identity->id;
            $authoriser_name = PayrollUploadMaster::getInputter();
            $exec_stat = "WAITING";
            
        try {
            $sql = "update payroll_upload_master set "
                    . "otor_stat = '" . $otor_stat . 
                    "',otor_date = '" . $otor_date . 
                    "',otor_tm = '" . $otor_tm .
                    "',authoriser= '". $authoriser .
                    "',authoriser_name= '". $authoriser_name .
                    "',exec_stat= '".$exec_stat.
                    "' where nama_file_upload = '" . $nama_file_upload .
                    "' and co_code ='" . $co_code . "'";
                  
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
                
                //set status
                $message = "Data ".$nama_file_upload." berhasil diotorisasi.";
                Yii::$app->session->setFlash('status','<div class="alert alert-success">' .$message.'</div>');
                
            } catch (Exception $e) {
                $message = "Proses validasi ulang tidak berhasil.";
                Yii::$app->session->setFlash('status', '<div class="alert alert-error">'.$message.'</div>');
            }
             return $this->redirect(['indexotor']);
    }

    /**
     * Deletes an existing PayrollUploadMaster model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $kode_parameter
     * @param string $nama_file_upload
     * @return mixed
     */
    public function actionDelete($kode_parameter, $nama_file_upload)
    {
        $this->findModel($kode_parameter, $nama_file_upload)->delete();

        return $this->redirect(['indexotor']);
    }
     
    /**
     * Finds the PayrollUploadMaster model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $kode_parameter
     * @param string $nama_file_upload
     * @return PayrollUploadMaster the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($kode_parameter, $nama_file_upload)
    {
        if (($model = PayrollUploadMaster::findOne(['kode_parameter' => $kode_parameter, 'nama_file_upload' => $nama_file_upload])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
