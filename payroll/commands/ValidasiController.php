<?php

/*
 * @author fitriana.dewi
 * Crontab jalan pada saat data payroll sudah diupload untuk divalidasi
 */

namespace app\modules\payroll\commands;

use yii\console\Controller;
use app\modules\payroll\models\PayrollUploadDetail;
use app\modules\payroll\models\PayrollUploadDetailTemp;
use app\modules\payroll\models\PayrollUploadMaster;
use app\modules\payroll\models\Payrollwsclient;

class ValidasiController extends Controller {
    
    //status validasi
    const ONPROCESS = 'ON PROCESS';
    const VALID = 'VALID';
    const ERROR = 'ERROR';
         
    public function actionProsesvalidasi(){
        
        //cek status crontab
//        echo "[" . date('Y-m-d H:i:s') . "] : Proses Validasi Data Payroll - START, Mengecek status cron payroll/validasi/prosesvalidasi \n";
//        $cs = \app\models\CronStatus::findOne('payroll/validasi/prosesvalidasi');
//        if ($cs == null)
//        {
//            echo "[" . date('Y-m-d H:i:s') . "] : Proses Validasi Data Payroll - ERROR, Parameter cron payroll/validasi/prosesvalidasi tidak ditemukan \n";
//            return;
//        }
//        else
//        {
//            if ($cs->status != 'DONE')
//            {
//                echo "[" . date('Y-m-d H:i:s') . "] : Proses Validasi Data Payroll - SKIP, Cron payroll/validasi/prosesvalidasi sedang aktif untuk file: " . $cs->file_name . "\n";
//                return;
//            }
//            else
//            {
//                $cs->start_time = date('Y-m-d H:i:s');
//                $cs->status = 'STARTING';
//                $cs->save(false);
          
        //end cek status crontab
        
        $tgl_crontab = date('Y-m-d');
        $jam_crontab = date('H:i:s');
//        echo "===================================================================================\n";
//        echo "[" . $tgl_crontab . "] [" . $jam_crontab . "] Proses Validasi Data Payroll \n";
//        echo "==================================================================================\n\n";
        
        $modelfile = PayrollUploadMaster::getDataMaster();
        $countfile = count($modelfile);
        
        if($countfile !=0){
            foreach ($modelfile as $file){
                $nama_file_upload = $file['nama_file_upload'];
                
                //update status validasi data payroll
                $status_valid = "ON PROCESS";
                PayrollUploadMaster::updateData($file,$status_valid);
                
                //get detail data master from temp
                $modeldetails = PayrollUploadDetailTemp::searchOnly($nama_file_upload);
                $countdetail = count($modeldetails);
                                
                if($countdetail != 0){
                    $countvalid = 0;
                    $i = 0;
                    foreach ($modeldetails as $modeldetail){
                       //count isi data upload
                      
                       if($i == 0){
                           $firstNumberID = $modeldetail->id;
                           $lineID = $firstNumberID;
                       }
                       $status = '';
//                       $RowNumber = (int)$modeldetail->id;
//                       $modelresult = Payrollwsclient::doValidate($modeldetail,$RowNumber,$firstNumberID);
                       $modelresult = new Payrollwsclient();
                       $modelresult = $modelresult->doValidate($modeldetail,$lineID);
                       if($modelresult[0] == 'valid'){
                           PayrollUploadDetailTemp::updateDataValid($modeldetail,$modelresult);
                           $countvalid = $countvalid+1;
                       }else{
                           PayrollUploadDetailTemp::updateDataError($modeldetail,$modelresult);
                           $countvalid;
                       }
//                     $RowNumber++;
                       $lineID = $lineID +1;
                       $i = $i+1;
                    }
                    $status_valid = $this->isValid($countvalid,$countdetail);
                    //update status master data
                    PayrollUploadMaster::updateData($modeldetail,$status_valid);
                    
                    if($status_valid == "VALID"){
                        $modeldetails = PayrollUploadDetailTemp::searchOnly($nama_file_upload);
                        foreach($modeldetails as $modeldetail){
                        $transaction_detail = PayrollUploadDetail::getDb()
                                              ->beginTransaction();
                        $model_detail = new PayrollUploadDetail();
                        $model_detail->ccy = $modeldetail['ccy'];
                        $model_detail->narasi = $modeldetail['narasi'];
                        $model_detail->id = $modeldetail['id'];
                        $model_detail->kode_parameter = $modeldetail['kode_parameter'];
                        $model_detail->nama_file_upload = $modeldetail['nama_file_upload'];
                        $model_detail->nama_file_process = $modeldetail['nama_file_process'];
                        $model_detail->payrollamt = $modeldetail['payrollamt'];
                        $model_detail->acctno_cr = $modeldetail['acctno_cr'];
                        $model_detail->acctno_db = $modeldetail['acctno_db'];
                        $model_detail->charge_amt = (string)($modeldetail['charge_amt']);
                        $model_detail->co_code = $modeldetail['co_code'];
                        $model_detail->status = $modeldetail['status'];
                        $model_detail->acct_title = $modeldetail['acct_title'];
//                       
                            if (!$model_detail->hasErrors() && $model_detail->validate())
                            {
                                $model_detail->save();
                                 //hapus dari temporary
                                PayrollUploadDetailTemp::deleteData($modeldetail);
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
                }}}}
        }
        echo "[" . $tgl_crontab . "] [" . date('H:i:s') . "] [" .$nama_file_upload. "] : ".$status_valid."\n";}
    
    }

    public function isValid($countValid,$countdetails){
        $status = '';
        if($countValid == $countdetails){
            $status = $this::VALID;
        }else{
            $status = $this::ERROR;
        }
        return $status;
    }
}
