<?php

/*
 * HasilpayrollController
 * Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */

namespace app\modules\payroll\controllers;

use Yii;
use app\modules\payroll\models\PayrollUploadMaster;
use app\modules\payroll\models\PayrollUploadDetail;
use app\modules\payroll\models\Parameterpayroll;
use app\modules\payroll\models\PayrollUploadMaster_search;
use app\modules\payroll\models\Payrollwsclient;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use kartik\mpdf\Pdf;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class HasilpayrollController extends \yii\web\Controller {
    /* Lists all PayrollUploadMaster models
     * @return mixed
     */

    public function actionGethasil()
    {
        $request = Yii::$app->request;
        $session = Yii::$app->session;
        
        $model = new PayrollUploadMaster(['scenario'=>'carihasil']);
        $co_code = Yii::$app->user->identity->branch_cd;
        $searchModel = new PayrollUploadMaster_search();
       
        $model->date_exec = date('Ymd');
        if($model->load($request->post())){
            
            $data = $searchModel->searchByDate(date("Y-m-d",strtotime($model->date_exec)),$co_code);
//            var_dump($data);die;
            $dataProvider = new ActiveDataProvider(['query'=>$data,
                'pagination' => ['pageSize' => 10],
                'sort'=>['defaultOrder'=>['time_exec'=>SORT_ASC]]]);
            
        }
        
        return $this->render('daftar_upload_payroll_hasil', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'model'=>$model
        ]);
        
    }
    
    public function actionIndexhasil()
    {
        $co_code = Yii::$app->user->identity->branch_cd;
        $searchModel = new PayrollUploadMaster_search();
        $dataProvider = $searchModel->searchAuthorized(Yii::$app->request->queryParams, $co_code);
        $model = new PayrollUploadMaster();
                
        return $this->render('daftar_upload_payroll_hasil', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'model'=>$model
        ]);
    }

    public function actionDetaildata($kode_parameter, $nama_file_upload)
    {
        $cocode = Yii::$app->user->identity->branch_cd;
        $model = (new \yii\db\Query())
                ->select(['payroll_parameter.*', 'payroll_upload_master.*'])
                ->from(['payroll_parameter', 'payroll_upload_master'])
                ->where(['payroll_parameter.kode_parameter' => $kode_parameter,
                    'payroll_upload_master.nama_file_upload' => $nama_file_upload, 'payroll_upload_master.co_code' => $cocode])
                ->one();
        $model = ((object) $model);

        $searchModel = new PayrollUploadDetail();
        $dataProvider = new ArrayDataProvider([
            'allModels' => $searchModel->searchOnly($nama_file_upload),
            'pagination' => array('pageSize' => 1000),
            'sort' => [
                'attributes' => ['id'],
            ],
        ]);

        $model_detail = new PayrollUploadDetail();

        $data = PayrollUploadDetail::find()
                ->Where(['nama_file_upload' => $nama_file_upload])
                ->all();

        $nFile_pay = $model_detail->find()
                ->where(['nama_file_upload' => $nama_file_upload])
                ->count();
        $sumPayrollamt = $model_detail->searchSumPayrollAmt($nama_file_upload);

        $validData = $model_detail->countValid($nama_file_upload);

        if ($model->narasi == "BIAYA DIBEBANKAN KE REKENING PERUSAHAAN")
        {
            $sumChargeAmt = $model_detail->searchSumChargeAmt($nama_file_upload);
            $nominalTerdebit = $sumChargeAmt + $sumPayrollamt;
        }
        else
        {
            $sumChargeAmt = "";
            $nominalTerdebit = $sumPayrollamt;
        }

        //selection of button
//        $otor_stat = $model['otor_stat'];
        $otor_stat = $model->otor_stat;

        return $this->render('detail_data_payroll_hasil', [
                    //untuk info detail parameter payroll
                    'validData' => $validData,
                    'nama_file_upload' => $nama_file_upload,
                    'nFile_pay' => $nFile_pay,
                    'sumPayrollamt' => $sumPayrollamt,
                    'sumChargeAmt' => $sumChargeAmt,
                    'nominalTerdebit' => $nominalTerdebit,
                    'model' => $model,
                    'data' => $data,
                    //untuk memunculkan isi file upload payroll
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionInquiryhasil($kode_parameter, $nama_file_upload)
    {
        $h2 = "Hasil Payroll";
        $cocode = Yii::$app->user->identity->branch_cd;
        
        $model = (new \yii\db\Query())
                ->select(['payroll_parameter.*', 'payroll_upload_master.*'])
                ->from(['payroll_parameter', 'payroll_upload_master'])
                ->where(['payroll_parameter.kode_parameter' => $kode_parameter,
                    'payroll_upload_master.nama_file_upload' => $nama_file_upload, 'payroll_upload_master.co_code' => $cocode])
                ->one();
        
        $model = ((object) $model);
        $nama_file_process = $model->nama_file_process;
        $searchModel = new PayrollUploadDetail();

        $data = PayrollUploadDetail::find()
                ->Where(['nama_file_upload' => $nama_file_upload])
                ->orderBy('id') // apply sort
                ->all();
        $datas = array();

        foreach ($data as $row)
        {
            $datas[] = $row;
        }

        $data_print = $datas;
        $datas = new ArrayDataProvider([
            'allModels' => $datas,
            'pagination' => array('pageSize' => 1000),
            'sort' => [
                'attributes' => ['id'],
            ],
        ]);
        $model_detail = new PayrollUploadDetail();
        $sumPayrollamt = $model_detail->searchSumPayrollAmt($nama_file_upload);
        $nFile_pay = $model_detail->find()
                ->where(['nama_file_upload' => $nama_file_upload])
                ->count();
        $sumChargeAmt = $model_detail->searchSumChargeAmt($nama_file_upload);

        $numberFTSuccess = $model_detail->countFTStatus($nama_file_process, 'FT Berhasil');
        $numberFTFailed = $model_detail->countFTStatus($nama_file_process, 'FT Gagal');

        $nominalFTSuccess = $model_detail->searchSumPayrollIf($nama_file_upload, 'FT Berhasil');
        $nominalFTFailed = $model_detail->searchSumPayrollIf($nama_file_upload, 'FT Gagal');
        $nominalChargeSuccess = $model_detail->searchSumChargeAmtIf($nama_file_upload, 'FT Berhasil');
        $nominalChargeFailed = $model_detail->searchSumChargeAmtIf($nama_file_upload, 'FT Gagal');

//        var_dump('transaksi sukses :'.$numberFTSuccess.', transaksi gagal :'. $numberFTFailed.', total nominal gaji berhasil :'. $nominalFTSuccess.', total nominal gaji gagal :'. $nominalFTFailed.', total biaya sukses :'. $nominalChargeSuccess.', total biaya gagal :'. $nominalChargeFailed);die;
        if (isset($model->narasi))
        {
            $keterangan = $model->narasi;
            if ($keterangan == "BIAYA DIBEBANKAN KE REKENING PERUSAHAAN")
            {
                $sumChargeAmt = $model_detail->searchSumChargeAmt($nama_file_upload);
                $sumAll = $model_detail->sumAll($sumPayrollamt, $sumChargeAmt);
            }
            else
            {
                $sumChargeAmt = "";
                $sumAll = $sumPayrollamt;
            }
        }
        else
        {
            $sumChargeAmt = "";
            $sumAll = $sumPayrollamt;
        }

        $validData = $model_detail->countValid($nama_file_upload);
        $nominalTerdebit = $nominalChargeSuccess + $nominalFTSuccess;

        $summary = array(
            'jumlah_transaksi_sukses' => $numberFTSuccess,
            'jumlah_transaksi_gagal' => $numberFTFailed,
            'jumlah_nominal_transaksi_sukses' => $nominalFTSuccess,
            'jumlah_nominal_transaksi_gagal' => $nominalFTFailed,
            'jumlah_nominal_terdebit' => $nominalTerdebit,
//            'jumlah_nominal_biaya_sukses' => $nominalChargeSuccess,
//            'jumlah_nominal_biaya_gagal' => $nominalChargeFailed,
            'jumlah_nominal' => $sumPayrollamt,
            'nFile_pay' => $nFile_pay
        );

        return $this->render('detail_hasil_payroll', [
                    //untuk info detail parameter payroll
                    'validData' => $validData,
                    'nama_file_upload' => $nama_file_upload,
                    'nFile_pay' => $nFile_pay,
                    'sumPayrollamt' => $sumPayrollamt,
                    'sumChargeAmt' => $sumChargeAmt,
                    'model' => $model,
                    'data_print' => $data_print,
                    'h2' => $h2,
                    'sumAll' => $sumAll,
                    //summary hasil payroll
                    'summary' => $summary,
                    //untuk memunculkan isi file upload payroll
                    'searchModel' => $searchModel,
                    'datas' => $datas,
                    'data' => $data
        ]);
    }

    public function actionDownloadhasil($id, array $summary)
    {
        $nama_file = str_replace(".csv", "", $id);
        $nama_file = $nama_file . "." . date('dmY') . "." . date('his');
        //data master
        $modelMaster = PayrollUploadMaster::find()->where(['nama_file_upload' => $id])
                ->one();
        $modelParam = Parameterpayroll::find()->where(['kode_parameter' => $modelMaster['kode_parameter']])->one();
        $modelDetail = PayrollUploadDetail::find()->where(['nama_file_upload' => $id])
                ->orderBy('id') // apply sort
                ->all();

        $model_detail = new PayrollUploadDetail();

        $nFile_pay = $model_detail->find()
                ->where(['nama_file_upload' => $id])
                ->count();

        $sumPayrollamt = $model_detail->searchSumPayrollAmt($id);
        $html = "";
        // set response header
//        \yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
//        \yii::$app->response->headers->add('Content-Type', 'application/pdf');
//        $content = $this->renderPartial('cetakpdf_1',['modelDetail'=>$modelDetail,
//                                                    'modelParam'=>$modelParam,
//                                                    'modelMaster'=>$modelMaster,
//                                                    'summary'=>$summary,
//                                                    'nama_file'=>$nama_file,
//                                                    'nFile_pay'=>$nFile_pay,
//                                                    'html'=>$html,
//                                                    'sumPayrollamt'=>$sumPayrollamt]);
        $content = $this->renderPartial('cetakpdf',['content'=>$this->PdfContent($nama_file, $modelMaster, $modelParam, $modelDetail, $model_detail,$nFile_pay,$sumPayrollamt,$summary)]);
        $pdf = new Pdf([
//                'mode' => Pdf::MODE_CORE, // leaner size using standard fonts
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'format' => 'A4-L',
            'content' => $content,
            'destination' => Pdf::DEST_BROWSER,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            // call mPDF methods on the fly
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'filename' => "PAY.RESULT." . $nama_file,
            'options' => ['title' => 'Hasil Payroll Multikredit'],
            'methods' => [
                'SetHeader' => ["PT. BANK BRI SYARIAH||Generated On: " . date("r")],
                'SetFooter' => ["|Page {PAGENO}|"],
            ]
        ]);

        //setup kartik\mpdf\Pdf component
        // http response
        //Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        //$headers = Yii::$app->response->headers;
        //$headers->add('Content-Type', 'application/pdf');
        return $pdf->Output($content, $nama_file);
    }

    public function actionReport()
    {
//        $pdf = new Pdf([
//            'mode' => Pdf::MODE_CORE, // leaner size using standard fonts
//            'content' => $this->renderPartial('cetakpdf'),
//            'destination' => Pdf::DEST_DOWNLOAD,
//            'options' => [
//                'title' => 'Privacy Policy - Krajee.com',
//                'subject' => 'Generating PDF files via yii2-mpdf extension has never been easy'
//            ],
//            'methods' => [
//                'SetHeader' => ['Generated By: Krajee Pdf Component||Generated On: ' . date("r")],
//                'SetFooter' => ['|Page {PAGENO}|'],
//            ]
//        ]);
        return $this->render('index');
    }

    public function actionDownloaddetail($id)
    {

        $nama_file = str_replace(".csv", "", $id);
        $nama_file = "PAY.DETAIL." . $nama_file . "." . date('dmY') . "." . date('his') . ".csv";
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $nama_file);

        //data master
        $modelMaster = PayrollUploadMaster::find()->where(['nama_file_upload' => $id])
                ->one();
        $modelParam = Parameterpayroll::find()->where(['kode_parameter' => $modelMaster['kode_parameter']])->one();

        print "Nama File Upload  :" . $modelMaster['nama_file_upload'] . "\n";
        print "Nama File Proses  :" . $modelMaster['nama_file_process'] . "\n";
        print "Parameter         :" . $modelMaster['kode_parameter'] . "\n";
        print "No. Rek Debit     :" . $modelParam['acctno'] . "\n";
        print "Cabang            :" . $modelMaster['co_code'] . "\n\n";
//        print "Ada Biaya         :".$modelMaster['charge_flag']."\n";
//        print "Nominal Biaya     :".$modelMaster['charge_amt']."\n";

        if (($modelParam['charge_flag'] == 'Y') && ($modelMaster['acctno_charge'] != ''))
        {
            $norekBiaya = $modelMaster['acctno_charge'];
        }
        else if (($modelParam['charge_flag'] == 'Y') && ($modelMaster['acctno_charge'] == ''))
        {
            $norekBiaya = "Dibebankan Ke Masing-Masing No.Rekening Kredit";
        }
        else
        {
            $norekBiaya = "Tanpa Biaya";
        }

//        print "No. Rek Biaya     :".$norekBiaya."\n\n";
        //data detail master
        $modelDetail = PayrollUploadDetail::find()->where(['nama_file_upload' => $id])
                ->orderBy('id') // apply sort
                ->all();
        $header = " ID" . " " . "|Kurs" . " " . "|No. Rek. Kredit" . " " . "|Nama Rekening" . " " . "|Nominal" . " " . "|Narasi" . " " . "\n";

        print $header;

        foreach ($modelDetail as $data)
        {
            $data = $data['id'] . "|" .
//                    $data['nip']."|".
                    $data['ccy'] . "|" .
                    $data['acctno_cr'] . "|" .
                    $data['acct_title'] . "|" .
                    number_format($data['payrollamt'], 2) . "|" .
                    $data['narasi'] .
//                    $data["sign"].
                    "\r\n";
            print $data;
        }
       
        exit();
        return $this->redirect(['detaildata',
                    'kode_parameter' => $modelMaster->kode_parameter,
                    'nama_file_upload' => $modelMaster->nama_file_upload]);
    }

    public function actionDeleteinao($id)
    {
        $msgresp = Payrollwsclient::hapusinao($id);

        //update keterangan FT lama
        PayrollUploadDetail::UpdateINAO($msgresp);

        //setflash
        $message = $msgresp['ft_stat'];
        Yii::$app->session->setFlash('status', '<div class="alert alert-warning">' . $message . '</div>');
        return $this->render('view_status');
    }

    public function actionOtorinao($id)
    {
        $msgresp = Payrollwsclient::otorinao($id);

        //update keterangan FT lama
        PayrollUploadDetail::UpdateINAO($msgresp);

        //setflash
        $message = $msgresp['ft_stat'];
        Yii::$app->session->setFlash('status', '<div class="alert alert-warning">' . $message . '</div>');
        return $this->render('view_status');
    }

//    public function actionReport() {
// 
//        // get your HTML raw content without any layouts or scripts
//        $content = $this->renderPartial('cetakpdf');
//        // setup kartik\mpdf\Pdf component
//        $pdf = new Pdf([
//            // set to use core fonts only
//            'mode' => Pdf::MODE_CORE,
//            // A4 paper format
//            'format' => Pdf::FORMAT_A4,
//            // portrait orientation
//            'orientation' => Pdf::ORIENT_PORTRAIT,
//            // stream to browser inline
//            'destination' => Pdf::DEST_BROWSER,
//            // your html content input
//            'content' => $content, 
//            // format content from your own css file if needed or use the
//            // enhanced bootstrap css built by Krajee for mPDF formatting
//            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
//             // call mPDF methods on the fly
//            'methods' => [
//                'SetHeader'=>['THIS IS REPORT'],
//                'SetFooter'=>['{PAGENO}'],
//            ]
//        ]);
// 
//        // http response
//        $response = Yii::$app->response;
//        $response->format = \yii\web\Response::FORMAT_RAW;
//        $headers = Yii::$app->response->headers;
//        $headers->add('Content-Type', 'application/pdf');
// 
//        // return the pdf output as per the destination setting
//        return $pdf->render();
//    }

    public function PdfContent($nama_file, $modelMaster, $modelParam, $modelDetail, $model_detail,$nFile_pay,$sumPayrollamt,$summary)
    {
        $crow = count($modelDetail);
        $num_per_page = 60;
        $page = ceil($crow / $num_per_page);
        $x = 1;

        $th = "<tr><th width=5%>#</th>"
                . "<th width=15%>FT ID</th>"
                . "<th width=15%>No.Rek.Kredit</th>"
                . "<th width=15%>Nama Rekening</th>"
                . "<th width=15%>Tgl. Proses</th>"
                . "<th width=15%>No.Rek.Debet</th>"
                . "<th width=15%>Nominal</th>"
                . "<th width=15%>Status</th>"
                . "<th width=15%>Keterangan</th>"
                . "</tr>";
        
       

        $html = "
                <HTML>
                <HEAD>
                <TITLE></TITLE>
                </HEAD>";
        $html .= "  <BODY margin='0'>
                <p style='font:10pt Arial; font-weight:bold'>
                <p style='font:14pt Arial; font-weight:bold'>HASIL PAYROLL<br>";
        
        $html .="
                <table style='width:100%'>
                <tr style= 'text-align:left'>
                <th>Nama File Upload :</th>
                <th style= 'text-align:right'>" . $modelMaster['nama_file_upload'] . "</th>
                </tr>
                <tr style= 'text-align:left'>
                <th>Nama File Proses :</th>
                <th style= 'text-align:right'>" . $modelMaster['nama_file_process'] . "</th>
                </tr>
                <tr style= 'text-align:left'>
                <th>Kode Parameter :</th>
                <th style= 'text-align:right'>" . $modelMaster['kode_parameter'] . "</th>
                </tr>
                <tr style= 'text-align:left'>
                <th>No. Rek Debit :</th>
                <th style= 'text-align:right'>" . $modelParam['acctno'] . "</th>
                </tr>
                <tr style= 'text-align:left'>
                <th>Cabang :</th>
                <th style= 'text-align:right'>" . $modelMaster['co_code'] . "</th>
                </tr>
                <tr style= 'text-align:left'>
                <th>Tanggal Eksekusi :</th>
                <th style= 'text-align:right'>" . $modelMaster['date_exec'] . "</th>
                </tr>
                <tr style= 'text-align:left'>
                <th>Jam Eksekusi :</th>
                <th style= 'text-align:right'>" . $modelMaster['time_exec'] . "</th>
                </tr>
                
                </table><br>";
        
        $html .="
                <table style='width:100%'>
                <tr style= 'text-align:left'>
                <th>Total Data Berhasil    :</th>
                <th>" . $summary['jumlah_transaksi_sukses'] . "</th>
                <th>Total Nominal Berhasil :</th>
                <th>Rp." . number_format($summary['jumlah_nominal_transaksi_sukses'], 2) . "</th>
                </tr>
                <tr style= 'text-align:left'>
                    <th>Total Data Gagal       :</th>
                    <th>" . $summary['jumlah_transaksi_gagal'] . "</th>
                    <th>Total Nominal Gagal    :</th>
                    <th>Rp." . number_format($summary['jumlah_nominal_transaksi_gagal'], 2) . "</th>
                </tr>
                <tr style= 'text-align:left'>
                    <th>Total Data             :</th>
                    <th>" . $nFile_pay . " data</th>
                    <th>Total Nominal :</th>
                    <th>Rp." . number_format($summary['jumlah_nominal'], 2) . "</th>
                </tr>
                </table><br><br>";
                
        $html .= "<table border=0 width=100% >";
        $html .= $th;
        $html .= "</table>";
        $no = 1;
//        
        foreach ($modelDetail as $data)
        {
        $html .= "<table border=0 width=100%>
                    <tr>
                    <td width=5%>" . $data['id'] . "</td>
                    <td width=15%>" . $data['ft_id'] . "</td>
                    <td width=15%>" . $data['acctno_cr'] . "</td>
                    <td width=15%>" . $data['acct_title'] . "</td>
                    <td width=15%>" . $data['date_process'] . "</td>
                    <td width=15%>" . $data['acctno_db'] . "</td>
                    <td width=15%>" . number_format($data['payrollamt'], 2) . "</td>
                    <td width=15%>" . $data["ft_stat"] . "</td>
                    <td width=15%>" . $data["ft_msg"] . "</td>
                    </tr>
                </table>
                ";
////            if ($no % $num_per_page == false && $num_per_page != $crow)
////            {
////                $x++;
////
//                $html .= "</table>";
////                    <div style='page-break-after:always'></div>
////                    </table>
////                    <table style='float:right'>
////                    <tr><td>Total Data</td><td>:</td><td>.$crow.</td></tr>
////                    <tr><td>Halaman</td><td>:</td><td>.$x.'/'.$page.</td></tr>
////                    </table>
////                    <div style='clear:both'></div>                
////                    <table border=1 width=100%>.$th";
////            }
//            $no++;
        }
        
        $html .= "<br>
                  <br>
                  <br>
                  <center>
                  <table border=0 width=100%>
                    <tr>
                    <td width=5% align ='center'><b>Inputter</b></td>
                    <td width=5% align ='center'><b>Authoriser</b></td>
                    </tr>
                  </table>";
        
        $html .= "<br>
                  <br>
                  <br>
                  <table border=0 width=100%>
                    <tr>
                   <td width=5% align ='center'><b>"."(".$modelMaster['inputter'].") ".$modelMaster['inputter_name']."</b></td>
                    <td width=5% align ='center'><b>"."(".$modelMaster['authoriser'].") ".$modelMaster['authoriser_name']."</b></td>
                    </tr>
                  </table>
                  </center>";
                        
        $html .="</BODY></HTML>";
//                
//                </table>";
//
//        
//
//        $html .= "<br><br>";
//
//        $html .= "<table border=1 width=100%>";
//
//        $html .= $th;
//        $no = 1;
//        foreach ($modelDetail as $data)
//        {
//            $html .= "
//                    <tr>
//                    <td>" . $data['id'] . "</td>
//                    <td>" . $data['ft_id'] . "</td>
//                    <td>" . $data['acctno_cr'] . "</td>
//                    <td>" . $data['acct_title'] . "</td>
//                    <td>" . $data['date_process'] . "</td>
//                    <td>" . $data['acctno_db'] . "</td>
//                    <td>" . number_format($data['payrollamt'], 2) . "</td>
//                    <td>" . $data["ft_stat"] . "</td>
//                    <td>" . $data["ft_msg"] . "</td>
//                    </tr>";
//            if ($no % $num_per_page == false && $num_per_page != $crow)
//            {
//                $x++;
//
//                $html .= "</table>
//                    <div style='page-break-after:always'></div>
//                    </table>
//                    <table style='float:right'>
//                    <tr><td>Total Data</td><td>:</td><td>.$crow.</td></tr>
//                    <tr><td>Halaman</td><td>:</td><td>.$x.'/'.$page.</td></tr>
//                    </table>
//                    <div style='clear:both'></div>                
//                    <table border=1 width=100%>.$th";
//            }
//            $no++;
//        }
//        $html .= "</table></BODY></HTML>";
        
        return $html;
    }

}?>

