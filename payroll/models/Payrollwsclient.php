<?php

/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */

namespace app\modules\payroll\models;

use Yii;
use \app\models\WsclientLog;
use app\models\User;
use app\common\helpers\Crypto;
use app\models\ConstFtStatus;
use app\models\Ftresponse;
use app\modules\payroll\models\PayrollUploadDetail;
use app\modules\payroll\models\PayrollUploadMaster;
use \app\modules\payroll\models\Parameterpayroll;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Payrollwsclient extends \yii\db\ActiveRecord {

    public $error_msg = "";

    public function hapusInao($id)
    {
        $wsdl = Yii::$app->params['wsdlt24'];
        $msgreq = json_encode(array(
            "userid" => Yii::$app->user->identity->t24_login_name,
            "password" => Yii::$app->user->identity->gett24pass(),
            "cocode" => Yii::$app->user->identity->branch_cd,
            "ft_id" => $id,
        ));
        try {

            $client = new \SoapClient($wsdl);
            $response = $client->hapusInao(array('params' => $msgreq));
            $objresp = json_decode($response->return, true);
            if ($objresp['status'] <> "00")
            {
                //tidak berhasil
                $ft_stat = $id . " Gagal Dihapus";
            }
            else
            {
                //berhasil
                $ft_stat = $id . " Telah Dihapus.";
            }

            $msg = array(
                "ft_stat" => $ft_stat,
                "ft_id" => $id,
            );
        } catch (\Exception $e) {
            $ft_msg = $e->getMessage();
        }
        return $msg;
    }

    public function otorInao($id)
    {
        $wsdl = Yii::$app->params['wsdlt24'];
        $msgreq = json_encode(array(
            "userid" => Yii::$app->user->identity->t24_login_name,
            "password" => Yii::$app->user->identity->gett24pass(),
            "cocode" => Yii::$app->user->identity->branch_cd,
            "ft_id" => $id,
        ));
//        try {

        $client = new \SoapClient($wsdl);
        $response = $client->otorInao(array('params' => $msgreq));
        $objresp = json_decode($response->return, true);

        if ($objresp['status'] <> "00")
        {
            $ft_stat = "FT Gagal";
            $ft_msg = $objresp['statusMsg'];
            $ft_id = 'null';
            $date_process = 'null';
        }
        else if (isset($objresp['params']['RECORD.STATUS:1:1']) && $objresp['params']['RECORD.STATUS:1:1'] == "INAO")
        {
//                $log->status = WsclientLog::STATERR;
//                $log->error_msg = $objresp['params']['OVERRIDE:1:1'];
            $ft_stat = "FT Pending. Status INAO";
            $ft_msg = $objresp['params']['OVERRIDE:1:1'];
            $ft_id = $objresp['params']['refno'];
            $date_process = $objresp['params']['DEBIT.VALUE.DATE:1:1'];

            $tahun = substr($date_process, 0, 4);
            $bulan = substr($date_process, 4, 2);
            $tgl = substr($date_process, 6, 2);
            $date_process = "'" . $tahun . "-" . $bulan . "-" . $tgl . "'";
        }
        else
        {
            $ft_stat = "FT Berhasil : Accept Override";
            $ft_id = $objresp['params']['refno'];

            if (isset($objresp['params']['CHARGES.ACCT.NO:1:1']))
            {
                if (($objresp['params']['CHARGES.ACCT.NO:1:1'] == $objresp['params']['DEBIT.ACCT.NO:1:1']))
                {
                    $ft_msg = "Rek. Debit dan Rek. Biaya Sama";
                }
                else if (($objresp['params']['CHARGES.ACCT.NO:1:1'] != $objresp['params']['DEBIT.ACCT.NO:1:1']))
                {
                    $ft_msg = "Rek. Debit berbeda dengan Rek. Biaya";
                }
            }
            else
            {
                $ft_msg = "Tidak Ada Biaya";
            }
            $date_process = $objresp['params']['DEBIT.VALUE.DATE:1:1'];
            $tahun = substr($date_process, 0, 4);
            $bulan = substr($date_process, 4, 2);
            $tgl = substr($date_process, 6, 2);
            $date_process = "'" . $tahun . "-" . $bulan . "-" . $tgl . "'";
        }

        $msg = array(
            'ft_id' => $ft_id, //ft_id baru
            'ft_stat' => $ft_stat,
            'ft_msg' => $ft_msg,
            'date_process' => $date_process,
        );
        return $msg;
    }

    public function bacaRek($msgreq)
    {
        $logmodel = new WsclientLog();
        $wsdl = Yii::$app->params['wsdlt24'];

        $logmodel->func = "bacaRek";
        $logmodel->request = $msgreq;
        $logmodel->inputter = $msgreq['id'];
        $data = array();
        $client = new \SoapClient($wsdl);
 
        $response = $client->bacaRek(['params' => json_encode($msgreq)]);
        $objresp = json_decode($response->return);
        return $objresp;
    }

    //dieksekusi di dalam foreach
    public function pindahBukuPayroll($model)
    {

        $ft_stat = '';
        $ft_msg = '';
        $ft_id = '';
        $prefix_charge = '';

        $modelMaster = PayrollUploadMaster::find()
                ->Where(['nama_file_upload' => $model['nama_file_upload']])
                ->one();

        $modelParam = Parameterpayroll::find()
                ->Where(['kode_parameter' => $modelMaster['kode_parameter']])
                ->one();

        //biaya dibebankan rek.kredit
        if ($modelMaster['charge_flag'] == "Y" && $modelMaster['acctno_charge'] == "")
        {
            $acctno_charge = $model['acctno_cr'];
            $prefix_charge = "IDR";
        }
        //biaya dibebankan rek.debet
        else if ($modelMaster['charge_flag'] == "Y" && $modelMaster['acctno_charge'] != "")
        {
            $acctno_charge = $modelParam['acctno_charge'];
            $prefix_charge = "IDR";
        }
        //tanpa biaya
        else if ($modelMaster['charge_flag'] == "T")
        {
            $acctno_charge = "";
            $prefix_charge = null;
        }

//        $storcv1 = User::findOne($modelMaster->inputter);
        $storcv1 = User::findOne($modelMaster->authoriser);

        $tgl_upload = str_replace("-", "", $modelMaster->date_upload);
//        $parameter = $modelMaster->kode_parameter;
//        $no_baris = $model->id;
        $narasi = substr($model->narasi,0,4);
        $acctno_cr = $model->acctno_cr;
        $revno = $tgl_upload . "." . $acctno_cr . "." . $narasi;

        $retval = array(
            "userid" => Yii::$app->params['payrolluser'],
            "password" => trim(Crypto::Decrypt(Yii::$app->params['payrollpass'])),
            "cocode" => $storcv1->branch_cd,
            "creditacc" => $model->acctno_cr,
            "creditcurr" => "IDR",
            "amount" => (string) $model->payrollamt,
            "debitacc" => $modelParam->acctno,
            "debitcurr" => "IDR",
            "ordercust" => $model->nama_file_upload,
            "details" => $model->narasi,
            "debit_their_ref" => $modelMaster->kode_parameter . '.' . $model->id,
            "credit_their_ref" => $modelMaster->kode_parameter . '.' . $model->id,
            "retrieve_rev_no" => substr($revno, 0, 30),
//            "commissiontype" => $modelParam->charge_type,
//            "commissionamt" => $prefix_charge . $modelParam->charge_amt,
//            "commissioncode" => $modelParam->charge_code,
//            "acctno_charge" => $acctno_charge,
        );

        $msgreq = json_encode($retval);
        $log = new WsclientLog();
        $log->func = "pindahBukuPayroll";
        $log->request = $msgreq;
        $log->inputter = $storcv1;

//        try{
        $client = new \SoapClient(YII::$app->params['wsdlt24']);
        $response = $client->pindahBukuPayroll(array('params' => $msgreq));

        $log->response = $response->return;
        $objresp = json_decode($response->return, true);

        if ($objresp['status'] <> "00")
        {
            $log->status = \app\models\WsclientLog::STATERR;
            $log->error_msg = $objresp['statusMsg'];
            $ft_stat = "FT Gagal";
            $ft_msg = $objresp['statusMsg'];
            if (strpos($ft_msg, 'ALT KEY ALREADY ASSIGNED')){
                $ft_msg = substr ($ft_msg, 38);
                $msg_remove = substr ($ft_msg, -20 );
                $ft_msg = str_replace ($msg_remove,"",$ft_msg);
                $ft_msg = "Nomor rekening sudah digunakan untuk ".$ft_msg;
            }
            $ft_id = 'null';
            $date_process = 'null';
        }
        else if (isset($objresp['params']['RECORD.STATUS:1:1']) && $objresp['params']['RECORD.STATUS:1:1'] == "INAO")
        {
            $log->status = WsclientLog::STATERR;
            $log->error_msg = $objresp['params']['OVERRIDE:1:1'];
            $ft_stat = "FT Pending. Status INAO";
            $ft_msg = $objresp['params']['OVERRIDE:1:1'];
            $ft_id = $objresp['params']['refno'];
            $date_process = $objresp['params']['DEBIT.VALUE.DATE:1:1'];

            $tahun = substr($date_process, 0, 4);
            $bulan = substr($date_process, 4, 2);
            $tgl = substr($date_process, 6, 2);
            $date_process = "'" . $tahun . "-" . $bulan . "-" . $tgl . "'";
        }
        else
        {
            $log->status = WsclientLog::STATOK;
            $ft_stat = "FT Berhasil";
            $ft_id = $objresp['params']['refno'];
            if (isset($objresp['params']['COMMISSION.TYPE:1:1']))
            {
                if (isset($objresp['params']['CHARGES.ACCT.NO:1:1']))
                {
                    if (($objresp['params']['CHARGES.ACCT.NO:1:1'] == $objresp['params']['DEBIT.ACCT.NO:1:1']))
                    {
                        $ft_msg = "Rek. Debit dan Rek. Biaya Sama";
                    }
                    else if (($objresp['params']['CHARGES.ACCT.NO:1:1'] != $objresp['params']['DEBIT.ACCT.NO:1:1']))
                    {
                        $ft_msg = "Rek. Debit berbeda dengan Rek. Biaya";
                    }
                }
            }
            else
            {
//                $ft_msg = "Tidak Ada Biaya";
                $ft_msg = $objresp['params']['DEBIT.ACCT.NO:1:1'];
            }
            $date_process = $objresp['params']['DEBIT.VALUE.DATE:1:1'];
            $tahun = substr($date_process, 0, 4);
            $bulan = substr($date_process, 4, 2);
            $tgl = substr($date_process, 6, 2);
            $date_process = "'" . $tahun . "-" . $bulan . "-" . $tgl . "'";
        }

        $msg = array(
            "nama_file" => $model->nama_file_process,
            "id" => $model->id,
            "ft_id" => $ft_id,
            "ft_stat" => $ft_stat,
            "ft_msg" => $ft_msg,
            "date_process" => $date_process
        );

        return $msg;
    }

    public function doValidate($dataupload,$lineID)
    {

        $row = count($dataupload); //7
//        $message = '';
        $message = [];
        $message [0] = "";
        $message [1] = "";
        $valid_stat = '';

        $model = PayrollUploadMaster::find()
                        ->where(['nama_file_upload' => $dataupload['nama_file_upload']])->one();
        $user = User::find()->where(['id' => $model['inputter']])->one();

        $id_user = $user['id'];
        $username = $user['t24_login_name'];
        $password = $user['t24_login_password'];
        $accno = $dataupload['acctno_cr'];

        $msgreq = array(
            'id' => $id_user,
            'userid' => $username,
            'password' => trim(Crypto::Decrypt($password)),
            'cocode' => $model['co_code'],
            'accno' => $accno
        );

        //validation row number
        $rowNumberFile = (int) $dataupload['id'];
        

        //check reccurence ID
        if ($rowNumberFile == '')
        {
            $message[0] = "No.baris harus diisi";
            $valid_stat = false;
        }
        else if (!is_numeric($rowNumberFile))
        {
            $message[0] = "No.baris harus berupa angka sebanyak 4 digit";
            $valid_stat = false;
        }
        else
        {
            //cek apakah 
            $num = strlen($dataupload['id']);
            if ($num > 4 || $num < 4)
            {
                $message[0] = "No.baris harus 4 digit";
                $valid_stat = false;
            }
            else if ($rowNumberFile != $lineID)
            {
                $message[0] = "No.baris tidak urut";
                $valid_stat = false;
            }
            else
            {
                $valid_stat = true;
            }
        }

        if ($valid_stat == true)
        {
            //validation CCY
            $ccy = $dataupload['ccy'];
            if ($ccy != 'IDR' || $ccy == '')
            {
                $message[0] = "Isi kurs dengan IDR";
                $valid_stat = false;
            }
            else
            {
                $valid_stat = true;
            }
            if ($valid_stat == true)
            {
                //validation acctno
                $acctno_cr = $dataupload['acctno_cr'];
                $num_acctno_cr = count($acctno_cr);
                if ($acctno_cr == "")
                {
                    $message[0] = "No. Rek. belum diisi";
                    $valid_stat = false;
                }
                else if (is_numeric($acctno_cr) == false)
                {
                    $message[0] = "No. Rek. harus berupa angka";
                    $valid_stat = false;
                }
                else
                {
                    //check number of digit acctno
                    $n_acctno = strlen($acctno_cr);
                    if (($n_acctno < 10))
                    {
                        $message[0] = "No. Rek. kurang dari 10 digit angka";
                        $valid_stat = false;
                    }
                    else
                    {
                        //check availability of acctno
                        $exist_la = \app\models\LookupAccount::findOne($acctno_cr);
                        $message[1]= $exist_la['acct_title'];
                        if ($exist_la == null)
                        {
                            $exist_a = \app\models\Account::findOne($acctno_cr);
                            $message[1]= $exist_a['account_title'];
                            if ($exist_a == null)
                            {
                                $valid_stat = false;
                                //check account in t24
                                $t24ws = new Payrollwsclient();
                                $msgresp = $t24ws->bacaRek($msgreq);
                                if ($msgresp->statusMsg == "Reading account done successfully")
                                {
                                    $valid_stat = true;
                                    $message[1]=$msgresp->params->accinfo->contents->c1; //getnamanasabah
                                }
                                else
                                {
                                    $valid_stat = false;
                                    $message[0] = "No. Rek. tidak ditemukan";
                                    $message[1]= "";
                                }
                            }
                            else
                            {
                                $valid_stat = true;
                            }
                        }
                        else
                        {
                            $valid_stat = true;
                        }
                    }
                }

                if ($valid_stat == true)
                {
                    //validation payrollamt
                    $payrollamt = $dataupload['payrollamt'];
                    if ($payrollamt == '')
                    {
                        $message[0] = "nominal gaji harus diisi";
                        $valid_stat = false;
                    }
                    else if ((preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $payrollamt)) || (is_numeric($payrollamt) == false))
                    {
                        $message[0] = "nominal gaji harus berupa angka dan lebih dari 0";
                        $valid_stat = false;
                    }
                    else
                    {
                        if ($payrollamt <= 0)
                        {
                            $message[0] = "nominal gaji tidak boleh kurang dari sama dengan 0 atau kosong";
                            $valid_stat = false;
                        }
                        else
                        {
                            $valid_stat = true;
                        }
                    }

                    if ($valid_stat == true)
                    {
                        //validation narasi
                        $narasi = $dataupload['narasi'];
                        if ($narasi == '')
                        {
                            $message[0] = "narasi";
                            $valid_stat = false;
                        }
                        else
                        {
                            $valid_stat = true;
                            $count = strlen($narasi);
                            if ($count > 35)
                            {
                                $message[0] = "narasi tidak boleh lebih dari 35 karakter";
                                $valid_stat = false;
                            }
                            else
                            {
                                $valid_stat = true;
                                $message[0] = "valid";
                                return $message;
                            }
                            if ($valid_stat == false)
                            {
                                return $message;
                            }
                        }
                    }
                    else
                    {
                        return $message;
                    }
                }
                else
                {
                    return $message;
                }
            }
            else
            {
                return $message;
            }
        }
        else
        {
            return $message;
        }
    }

    public function balance($objreq)
    {
//        $logmodel = new WsclientLog();

        $data = array();

        $wsdl = Yii::$app->params['wsdlt24'];

        $msgreq = json_encode(
                array(
                    "userid" => $objreq['userid'],
                    "password" => $objreq['password'],
                    "cocode" => $objreq['cocode'],
                    "accno" => $objreq['accno'],
                )
        );

        $client = new \SoapClient($wsdl);
//        $client = new \SoapClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE) );
//        $functions = $client->__getFunctions ();
//        echo '<pre>';
//        var_dump ($functions);
//        echo '<pre>';
//        die;
        $response = $client->enqSaldoRekNasAccNo(array('params' => $msgreq));
        $objresp = json_decode($response->return, true);

        $data = $objresp['params']['DATA']['CONTENT'];

        return $data;
    }

}
