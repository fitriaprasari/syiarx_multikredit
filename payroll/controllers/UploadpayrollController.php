<?php
/* 
 * UploadpayrollController
 * Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\controllers;

use Yii;
use yii\web\Controller;
use app\modules\payroll\models\PayrollUploadMaster;
use app\modules\payroll\models\PayrollUploadDetail;
use app\modules\payroll\models\Parameterpayroll;
use app\modules\payroll\models\PayrollUploadMaster_search;
use app\modules\payroll\models\PayrollUploadDetailTemp;
use yii\web\UploadedFile;
use yii\data\ArrayDataProvider;


class UploadpayrollController extends \yii\web\Controller {
    
    public function actionUpload()
    {
        //get data inputter
        $co_code = Yii::$app->user->identity->branch_cd;
        $inputter = Yii::$app->user->identity->id;
        $inputter_name = PayrollUploadMaster::getInputter();
        $vdate_inputter = date("Ymd");
        $vhour_inputter = date("his");
        $cabang = Yii::$app->session->get('branchnm');
        $narasi ="";

        $model_master = new PayrollUploadMaster();
        if ($model_master->load(Yii::$app->request->post()))
        {
            //validasi date exec
            
            $tm_exec = strtotime($model_master->date_exec . ' ' . $model_master->time_exec);
            $tm_now = strtotime(date('Y-m-d H:i:s'));
            
            $tm_exec_24format = date("H:i:s", $tm_exec);
                        
            if (!(($tm_exec_24format > date('06:00:00')) && ($tm_exec_24format < date('20:00:00'))))
            {
                $message = "Jam eksekusi harus di antara pukul 06.00 sampai 20.00 WIB";
                Yii::$app->session->setFlash('danger', $message);
            }
            else if (($tm_exec) <= $tm_now)
            {
                $message = "Tanggal eksekusi harus lebih dari sama dengan hari ini.";
                Yii::$app->session->setFlash('danger', $message);
            }         
            else if(($tm_exec-$tm_now) > 2591940){
                $message = "Tanggal eksekusi tidak boleh melebihi satu bulan.";
                Yii::$app->session->setFlash('danger', $message);
            }
            else
            {
                
                //validasi format namafile master
                $fullname_master = UploadedFile::getInstance(
                                $model_master, 'nama_file_upload');

                $ext = $fullname_master->extension;

                $hasilValidasi = $model_master->validasi_master(
                $fullname_master->name, $ext,$co_code);
                
                $status = '';

                if ($hasilValidasi == true)
                {
                    //baca detail data payroll
                    $bacaFile = $this->readFile($fullname_master->tempName);
                    if ($bacaFile == false)
                    {
                        $message = "Data upload tidak valid. Isi data upload melebihi 1000 data atau periksa struktur file (jumlah kolom tidak sesuai atau terdapat baris yang kosong)."
                                . " Silahkan periksa kembali.";
                        Yii::$app->session->setFlash('danger', $message);
                    }
                    else
                    {
                        $row = count($bacaFile);

                        //baca data parameter
                        $nama_file = substr($fullname_master->name, 0, -4);
                        
                        $countDate = substr($nama_file,0,11);
                        $param = str_replace($countDate,"",$nama_file);
                        
                        $model_param = Parameterpayroll::find()->where([
                                    'kode_parameter' => $param])->one();

                        $count = (PayrollUploadMaster::countData($co_code)) + 1;
                        $namaFileProses = "PAY." . $co_code . ".U" .
                                $inputter . "." . $vdate_inputter .
                                $vhour_inputter . "." .
                                $param . "." . $count;
                        
                        //simpan master data payroll di tabel payroll_upload_master
                        $transaction_master = PayrollUploadMaster::getDb()
                                              ->beginTransaction();
                        
                        $model_master->kode_parameter = $param;
                        $model_master->nama_file_upload = $fullname_master->name;
                        $model_master->nama_file_process = $namaFileProses;
                        $model_master->date_upload = substr($nama_file,0,8);
                        $model_master->valid_stat = "ON_PROCESS"; //to avoid crontab
                        $model_master->co_code = $co_code;
                        $model_master->inputter = $inputter;
                        $model_master->inputter_name = $inputter_name;
                        $model_master->charge_flag = $model_param['charge_flag'];
                        $model_master->acctno_charge = $model_param['acctno_charge'];
                        $model_master->charge_amt = str_replace(",", "", $model_param['charge_amt']);
                        $model_master->time_upload = date('his');
                        $model_master->otor_stat = 'INAU';
                      
                        if (!$model_master->hasErrors() && $model_master->validate())
                        {

                            $model_master->save();
                            //save .csv di folder
                            $model_master->nama_file_upload = UploadedFile::
                                    getInstance($model_master, 'nama_file_upload');
                            $folder = Yii::$app->params['payroll']['upload'];
                            $full_path = $folder . $fullname_master->name;
                                                    $namafile = $fullname_master->name;
                            $model_master->nama_file_upload->saveAs($full_path);

                            $message = "Data Payroll berhasil diupload.";

                            Yii::$app->session->setFlash('success', $message);
                            $transaction_master->commit();
                            $narasi = $model_master->narasi;
                        }
                        else
                        {
//                        $status = 'error';
                            $transaction_master->rollBack();
                        }
                        for($i=0;$i < $row; $i++){
                            //simpan detail data di temporary tabel
                            $transaction_detail = PayrollUploadDetailTemp::getDb()
                                                  ->beginTransaction();
                            $model_detail_temp = new PayrollUploadDetailTemp();
                            $model_detail_temp->narasi = strtoupper($narasi);
                            $model_detail_temp->id = $bacaFile[$i]['id'];
                            $model_detail_temp->id_seq = $i+1;
                            $model_detail_temp->ccy = "IDR";
                            $model_detail_temp->kode_parameter = $param;
                            $model_detail_temp->nama_file_upload = $fullname_master->name;
                            $model_detail_temp->nama_file_process = $namaFileProses;
                            $model_detail_temp->payrollamt = $bacaFile[$i]['payrollamt'];
                            $model_detail_temp->acctno_cr = $bacaFile[$i]['acctno'];
                            $model_detail_temp->acctno_db = $model_param['acctno'];
                            $model_detail_temp->charge_amt = str_replace(",", "", $model_param['charge_amt']);
                            $model_detail_temp->co_code = $co_code;
//                          $model_detail_temp->status = 'ON_PROCESS';
                            if (!$model_detail_temp->hasErrors() && $model_detail_temp->validate())
                            {
                                $model_detail_temp->save();
                            }
                            else
                            {
                                $status = 'error';                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               
                            }

                            if ($status == 'error')
                            {
                                $transaction_detail->rollBack();
                            }
                            else
                            {
                                $transaction_detail->commit();
                            }
                            }
                        }
                        
                        //update status master
                        $new_status = "WAITING";
                        $model = PayrollUploadMaster::updateData($model_master,$new_status);
                    }
                }
            }
        
        return $this->render('uploadpayroll', ['model_master' => $model_master,'cabang' => $cabang]);
    }

//    public function actionUpload()
//    {
//
//        //get data inputter
//        $co_code = Yii::$app->user->identity->branch_cd;
//        $inputter = Yii::$app->user->identity->id;
//        $vdate_inputter = date("Ymd");
//        $vhour_inputter = date("his");
//        $cabang = Yii::$app->session->get('branchnm');
//
//        $model_master = new PayrollUploadMaster();
//        if ($model_master->load(Yii::$app->request->post()))
//        {
//            //validasi date exec
//            $tm_exec = strtotime($model_master->date_exec . ' ' . $model_master->time_exec);
//            $tm_now = strtotime(date('Y-m-d H:i:s'));
////            var_dump("exec:".$tm_exec." now :".$tm_now);var_dump(($tm_exec < $tm_now));die;
//            if (($tm_exec) < $tm_now)
//            {
//                $message = "Tanggal eksekusi harus lebih dari sama dengan hari ini.";
//                Yii::$app->session->setFlash('danger', $message);
//            }
//            else
//            {
//                //validasi format namafile master
//                $fullname_master = UploadedFile::getInstance(
//                                $model_master, 'nama_file_upload');
//
//                $ext = $fullname_master->extension;
//
//                $hasilValidasi = $model_master->validasi_master(
//                $fullname_master->name, $ext,$co_code);
//                $status = '';
//
//                if ($hasilValidasi == true)
//                {
//                    //baca detail data payroll
//                    $bacaFile = $this->readFile($fullname_master->tempName);
//                    if ($bacaFile == false)
//                    {
//                        $message = "Struktur file salah : Jumlah kolom tidak sesuai atau terdapat baris "
//                                . "yang kosong. Silahkan periksa kembali.";
//                        Yii::$app->session->setFlash('danger', $message);
//                    }
//                    else
//                    {
//                        $row = count($bacaFile);
//
//                        //baca data parameter
//                        $nama_file = substr($fullname_master->name, 0, -4);
//                        $kode_param = preg_replace("/[^A-Z]/", "", $nama_file);
//                        $model_param = Parameterpayroll::find()->where([
//                                    'kode_parameter' => $kode_param])->one();
//
//                        $count = (PayrollUploadMaster::countData($co_code)) + 1;
//                        $namaFileProses = "PAY." . $co_code . ".U" .
//                                $inputter . "." . $vdate_inputter .
//                                $vhour_inputter . "." .
//                                $kode_param . "." . $count;
//                        
//                        //simpan master data payroll di tabel payroll_upload_master
//                        $transaction_master = PayrollUploadMaster::getDb()
//                                ->beginTransaction();
//
//                        $model_master->kode_parameter = $kode_param;
//                        $model_master->nama_file_upload = $fullname_master->name;
//                        $model_master->nama_file_process = $namaFileProses;
//                        $model_master->date_upload = $vdate_inputter;
//                        $model_master->valid_stat = "ON_PROCESS"; //to avoid crontab
//                        $model_master->co_code = $co_code;
//                        $model_master->inputter = $inputter;
//                        $model_master->charge_flag = $model_param['charge_flag'];
//                        $model_master->acctno_charge = $model_param['acctno_charge'];
//                        $model_master->charge_amt = str_replace(",", "", $model_param['charge_amt']);
//                        $model_master->time_upload = date('his');
//                        $model_master->otor_stat = 'INAU';
//
//                        if (!$model_master->hasErrors() && $model_master->validate())
//                        {
//
//                            $model_master->save();
//                            //save .csv di folder
//                            $model_master->nama_file_upload = UploadedFile::
//                                    getInstance($model_master, 'nama_file_upload');
//                            $folder = Yii::$app->params['payroll']['upload'];
//                            $full_path = $folder . $fullname_master->name;
//                            $namafile = $fullname_master->name;
//                            $model_master->nama_file_upload->saveAs($full_path);
//
//                            $message = "Data Payroll berhasil diupload.";
//
//                            Yii::$app->session->setFlash('success', $message);
//                            $transaction_master->commit();
//                        }
//                        else
//                        {
////                        $status = 'error';
//                            $transaction_master->rollBack();
//                        }
//                        
//                        for($i=0;$i < $row; $i++){
//                            //simpan detail data di temporary tabel
//                            $transaction_detail = PayrollUploadDetailTemp::getDb()
//                                                  ->beginTransaction();
//                            $model_detail_temp = new PayrollUploadDetailTemp();
//                            $model_detail_temp->nip = $bacaFile[$i]['nip'];
//                            $model_detail_temp->ccy = $bacaFile[$i]['ccy'];
//                            $model_detail_temp->narasi = $bacaFile[$i]['narasi'];
//                            $model_detail_temp->sign = $bacaFile[$i]['sign'];
//                            $model_detail_temp->id = $bacaFile[$i]['id'];
//                            $model_detail_temp->kode_parameter = $kode_param;
//                            $model_detail_temp->nama_file_upload = $fullname_master->name;
//                            $model_detail_temp->nama_file_process = $namaFileProses;
//                            $model_detail_temp->payrollamt = $bacaFile[$i]['payrollamt'];
//                            $model_detail_temp->acctno_cr = $bacaFile[$i]['acctno'];
//                            $model_detail_temp->acctno_db = $model_param['acctno'];
//                            $model_detail_temp->charge_amt = $model_param['charge_amt'];
//                            $model_detail_temp->charge_amt = str_replace(",", "", $model_param['charge_amt']);
////                          $model_detail_temp->status = 'ON_PROCESS';
//                            
//                            if (!$model_detail_temp->hasErrors() && $model_detail_temp->validate())
//                            {
//                                $model_detail_temp->save();
//                            }
//                            else
//                            {
//                                $status = 'error';
//                            }
//
//                            if ($status == 'error')
//                            {
//                                $transaction_detail->rollBack();
//                            }
//                            else
//                            {
//                                $transaction_detail->commit();
//                            }
//                            
//                        }
//                        
//                        //update status master
//                        $new_status = "WAITING";
//                        $model = PayrollUploadMaster::updateData($model_master,$new_status);
//                    }
//                }
//            }
//        }
//        return $this->render('uploadpayroll', ['model_master' => $model_master,
//                    'cabang' => $cabang,]);
//    }

    public function actionDaftarupload(){
        $aksi_btn='';
        $co_code = Yii::$app->user->identity->branch_cd;
        $searchModel = new PayrollUploadMaster_search();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$co_code);
//        
//        $model = PayrollUploadMaster::find()
//                ->where(['co_code'=>$co_code])->all();
        
        $model = PayrollUploadMaster::find()->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
        ->where(['payroll_upload_master.otor_stat' => 'INAU'])
        ->andWhere(['payroll_upload_master.co_code' => $co_code])
        ->andWhere(['payroll_parameter.tipe_transaksi' => 'MULTIKREDIT'])
        ->all();
        return $this->render('daftar_upload_payroll', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'model'=>$model,
        ]);
    }
    
    public function actionDetaildata($kode_parameter, $nama_file_upload){
        $model = (new \yii\db\Query())
        ->select(['payroll_parameter.*','payroll_upload_master.*'])
        ->from(['payroll_parameter','payroll_upload_master'])
        ->where(['payroll_parameter.kode_parameter'=>$kode_parameter,
                 'payroll_upload_master.nama_file_upload'=>$nama_file_upload])
        ->one();

        if ($model['valid_stat'] != "ERROR")
        {
            $searchModel = new PayrollUploadDetail();
            $dataProvider = new ArrayDataProvider([
                'allModels' => $searchModel->searchOnly($nama_file_upload),
                'pagination' => array('pageSize' => 1000),
                'sort' => [
                    'attributes' => ['id'],
                ],
            ]);

            $model_detail = new PayrollUploadDetail();
//            $sumPayrollamt = $model_detail->searchSumPayrollAmt($nama_file_upload);
//            var_dump($sumPayrollamt);die;

            $nFile_pay = $model_detail->find()->where(['nama_file_upload' => $nama_file_upload])->count();
            $validData = $model_detail->countValid($nama_file_upload);
            $actor = "backoffice";
        }
        else
        {
            $searchModel = new PayrollUploadDetailTemp();
            $dataProvider = new ArrayDataProvider([
                'allModels' => $searchModel->searchOnly($nama_file_upload),
                'pagination' => array('pageSize' => 1000),
                'sort' => [
                    'attributes' => ['id'],
                ],
            ]);

            $model_detail = new PayrollUploadDetailTemp();
//          $sumPayrollamt = $model_detail->searchSumPayrollAmt($nama_file_upload);

            $nFile_pay = $model_detail->find()->where(['nama_file_upload' => $nama_file_upload])->count();
            $validData = $model_detail->countValid($nama_file_upload);
            $actor = "backoffice";
        }
        $model = (object)$model;

        if($model->narasi == "BIAYA DIBEBANKAN KE REKENING PERUSAHAAN"){//            $sumChargeAmt = $nFile_pay * $model_detail->charge_amt;
            $sumChargeAmt = $model_detail->searchSumChargeAmt($nama_file_upload);
        }else{
            $sumChargeAmt = "0";
        }
        
        return $this->render('detail_data_master',[
            //untuk info detail parameter payroll
            'validData'=>$validData,
            'nama_file_upload' => $nama_file_upload,
            'nFile_pay' => $nFile_pay,
            'actor'=>$actor,
//            'sumPayrollamt' => $sumPayrollamt,
//            'sumChargeAmt' => $sumChargeAmt,
            
            //untuk memunculkan isi file upload payroll
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model'=>$model
        ]);

    }
    
    public function actionValidasiulang($nama_file_upload){
        $new_status = "WAITING";
        //update data master
        $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_master set valid_stat = '".$new_status."' where nama_file_upload = '".$nama_file_upload."'";
                
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
                
                //set status
                $message = "Data dalam proses validasi ulang.";
                Yii::$app->session->setFlash('success',$message);
                
            } catch (Exception $e) {
                $message = "Proses validasi ulang tidak berhasil.";
                Yii::$app->session->setFlash('error',$message);
            }
             return $this->redirect(['daftarupload']);
            }
    
    public function actionDelete($kode_parameter, $nama_file_upload)
    {
//        $this->findModel($kode_parameter, $nama_file_upload)->delete();
        $co_code = Yii::$app->user->identity->branch_cd;
        $model_detail = new PayrollUploadDetail();
        $model_master = new PayrollUploadMaster();
        $exist = $model_detail->find()->where(['kode_parameter' => $kode_parameter]);
        if ($exist != null)
        {
//            $model_detail->deleteUploadFile($kode_parameter, $nama_file_upload);
            $model_master->deleteMaster($nama_file_upload, $co_code);
            $folder = Yii::$app->params['payroll']['upload'];
            $filepath = $folder .$nama_file_upload;
            unlink($filepath);
        }

        return $this->redirect(['daftarupload']);
    }
    
    public function actionDeletetemp($kode_parameter, $nama_file_upload)
    {
//        $this->findModel($kode_parameter, $nama_file_upload)->delete();
        $co_code = Yii::$app->user->identity->branch_cd;
        $model_detail = new PayrollUploadDetailTemp();
        $model_master = new PayrollUploadMaster();
        $exist = $model_detail->find()->where(['kode_parameter' => $kode_parameter]);
        if ($exist != null)
        {
//            $model_detail->deleteData($nama_file_upload);
            $model_master->deleteMaster($nama_file_upload,$co_code);
            $folder = Yii::$app->params['payroll']['upload'];
            $filepath = $folder .$nama_file_upload;
            unlink($filepath);
        }
        return $this->redirect(['daftarupload']);
    }
    
/**
     * Updates an existing PayrollUploadDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
   
        public function actionUpdatedetaildata($id_seq, $nama_file_upload)
    {
        $model = $this->findModelDetailTemp($id_seq, $nama_file_upload);
        $model->status = "menunggu validasi ulang";
        $status = '';
        $model->charge_amt = (string)$model->charge_amt;
        
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            //update status detail data kembali menjadi belum divalidasi
            $status = "Data payroll berhasil diupdate";
            $model->save();
            Yii::$app->session->setFlash('status', '<div class="alert alert-success">' . $status . '</div>');
            return $this->redirect(['viewdetaildata', 'id' => $model->id_seq, 'nama_file_upload' => $model->nama_file_upload, 'status'=>$status]);
        }
        else
        {
            return $this->render('form_detail_data', [
                                'model' => $model
            ]);
        }
    }

    public function actionViewdetaildata($id,$nama_file_upload,$status){
        return $this->render('view_detail_data', [
            'model' => $this->findModelDetail($id,$nama_file_upload),
            'status'=> $status,
        ]);
    }
    
    public function actionViewdetaildatatemp($id,$nama_file_upload){
        return $this->render('view_detail_data', [
            'model' => $this->findModelDetail($id,$nama_file_upload),
        ]);
    }
    
//        public function actionViewdetaildata($id,$nama_file_upload){
//        return $this->render('view_detail_data', [
//            'model' => $this->findModelDetailTemp($id,$nama_file_upload),
//        ]);
//    }
//    
    public function actionGetaccountlokal(){
            $id=isset($_POST['id'])?$_POST['id'] :'';
            $model=new PayrollUploadDetail();
            $msgresp = json_encode($model->getAccount($id));
            return $msgresp;
    }

    public function readFile($file_path)
    {
        $CSVfp = fopen($file_path,"r");
        $result = '';
        
        if ($CSVfp !== FALSE)
        {
            $fp = file($file_path);
            $row = count($fp);

            if ($row > 1000)
            {
                $result = false;
                return $result;
            }
            
            $i = 0;
            while (!feof($CSVfp))
            {
                $data = fgetcsv($CSVfp, 1000, "~");
                $nField = sizeof($data);
                $data[$nField-1] = trim($data[$nField-1]); //prevent whitespace in eof
                $nfield = count($data);
                $row++;
                if($nfield !== 3){
                    $result = false;
                    return $result;
                }
                else
                {
                list($id, $acctno, $payrollamt) 
                        = $data;
                $result[$i] = array(
                    "id" => $id,
                    "acctno" => $acctno,
                    "payrollamt" => $payrollamt
                );
                $i = $i + 1;
            }
            }
           
        }
        fclose($CSVfp);
        return $result;
    }
    
    
        /**
     * Finds the Payrollupload model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $kode_parameter
     * @param string $nama_file_upload
     * @return Payrollupload the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($kode_parameter, $nama_file_upload)
    {
        if (($model = PayrollUploadMaster::findOne(['kode_parameter' => $kode_parameter, 'nama_file_upload' => $nama_file_upload])) !== null)
        {
            return $model;
        }
        else
        {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
        /**
     * Finds the PayrollUploadDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PayrollUploadDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

    
    protected function findModelDetailTemp($id_seq, $nama_file_upload)
    {
        $model = PayrollUploadDetailTemp::findOne(['nama_file_upload' => $nama_file_upload,'id_seq' => $id_seq]);
        
        if ($model !== null)
        {
            return $model;
        }
        else
        {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findModelDetail($id, $nama_file_upload)
    {
        if (($model = PayrollUploadDetailTemp::findOne(['id_seq' => $id, 'nama_file_upload' => $nama_file_upload])) !== null)
        {
            return $model;
        }
        else
        {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}