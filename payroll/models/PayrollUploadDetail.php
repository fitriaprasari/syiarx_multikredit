<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\models;
use app\modules\payroll\models\Parameterpayroll;
use Yii;

/**
 * This is the model class for table "payroll_upload_detail".
 *
 * @property string $nip
 * @property string $ccy
 * @property string $narasi
 * @property string $sign
 * @property string $id
 * @property string $kode_parameter
 * @property string $nama_file_upload
 * @property double $payrollamt
 * @property string $acctno_cr
 * @property string $ft_id
 * @property string $acctno_db
 * @property string $charge_amt
 * @property string $status
 * @property string $date_process
 * @property string $co_code
 */
class PayrollUploadDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payroll_upload_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','payrollamt','ccy', 'acctno_cr','narasi'], 'required'],
            [['nama_file_upload','nama_file_process'], 'string'],
            [['payrollamt','acctno_db'], 'number'],
            [['acctno_cr'], 'string'],
            [['date_process'], 'safe'],
            [['charge_amt'], 'string', 'max' => 25],
            [['acct_title'], 'string', 'max' => 50],
            [['ccy'], 'string', 'max' => 3],
            [['narasi'], 'string', 'max' => 35],
            [['id'], 'string', 'max' => 4],
            [['kode_parameter'], 'string', 'max' => 6],
            [['ft_id','ft_stat','ft_msg'], 'string', 'max' => 13],
            [['status','co_code'], 'string', 'max' => 20],
            [['date_process'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
//            'nip' => 'NIP',
            'ccy' => 'Kurs',
            'narasi' => 'Narasi',
            //'sign' => 'Sign',
            'id' => 'No.Baris',
            'kode_parameter' => 'Kode Parameter',
            'nama_file_upload' => 'Nama File Upload',
            'nama_file_process' => 'Nama File Proses',
            'payrollamt' => 'Nominal Gaji',
            'acctno_cr' => 'No. Rek. Kredit',
            'ft_id' => 'FT ID',
            'ft_stat' => 'FT Status',
            'ft_msg'=>'Keterangan',
            'acctno_db' => 'No. Rek. Debet',
            'charge_amt' => 'Nominal Biaya',
            'date_process' => 'Date Process',
            'acct_title' => 'Nama Rekening'
            
        ];
    }
    
    //delete by 2 param
        public function deleteUploadFile($kode_parameter,$nama_file_upload) {
//        $models = PayrollUploadDetail::find()
//                ->where(['kode_parameter' => $kode_parameter])
//                ->andWhere(['nama_file_upload'=>$nama_file_upload])
//                ->all();
//        
//        foreach ($models as $model){
//            $model->delete();
//        }
        
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            $co_code = Yii::$app->user->identity->branch_cd;
            try {
                $sql = "delete from payroll_upload_detail "
                        . "where nama_file_upload = '".$nama_file_upload."'"
                        . "and kode_parameter ='".$kode_parameter."'"
                        . "and co_code ='".$co_code."'";
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function deleteFile($kode_parameter) {
        $co_code = Yii::$app->user->identity->branch_cd;
        $models = PayrollUploadDetail::find()->where(['kode_parameter' => $kode_parameter,'co_code'=>$co_code])->all();
        foreach ($models as $model){
            $model->delete();
        }
    }
    
    public function searchSumPayrollAmt($nama_file_upload){
        $co_code =  Yii::$app->user->identity->branch_cd;
        $query = PayrollUploadDetail::find()->where(['nama_file_upload'=>$nama_file_upload,'co_code'=>$co_code]);
        $sum = $query->sum('payrollamt');
        if($sum == NULL){
            $sum = 0;
        }else{
            $sum = $sum;
        }
        return $sum;
    }
    
    public function searchSumChargeAmt($nama_file_upload){
        $co_code =  Yii::$app->user->identity->branch_cd;
        $query = PayrollUploadDetail::find()->where(['nama_file_upload'=>$nama_file_upload,'co_code'=>$co_code]);
        $sum = $query->sum('charge_amt');
        if($sum == NULL){
            $sum = 0;
        }else{
            $sum = $sum;
        }
        return $sum;
    }
    
    public function searchSumPayrollIf($nama_file_upload,$ft_status){
        $co_code = Yii::$app->user->identity->branch_cd;
        $query = PayrollUploadDetail::find()->where(['nama_file_upload'=>$nama_file_upload,'co_code'=>$co_code,'ft_stat'=>$ft_status]);
        $sum = $query->sum('payrollamt');
        if($sum == NULL){
            $sum = 0;
        }else{
            $sum = $sum;
        }
        return $sum;
    }
    
    public function searchSumChargeAmtIf($nama_file_upload,$ft_status){
        $co_code = Yii::$app->user->identity->branch_cd;
        $query = PayrollUploadDetail::find()->where(['nama_file_upload'=>$nama_file_upload,'co_code'=>$co_code,'ft_stat'=>$ft_status]);
        $sum = $query->sum('charge_amt');
         if($sum == NULL){
            $sum = 0;
        }else{
            $sum = $sum;
        }
        return $sum;
    }
    
    public function sumAll($sumPayroll,$sumBiaya){
        $sumAll = $sumPayroll+$sumBiaya;
        return $sumAll;
    }
   
    public function searchOnly($nama_file_upload)
    {
//        $co_code = Yii::$app->user->identity->branch_cd;
        $dataProvider = PayrollUploadDetail::find()->where(['nama_file_upload' => $nama_file_upload])
                                                   ->orderBy(['id' => SORT_ASC])
                                                   ->All();
        return $dataProvider;
    }
    
    public function updateData($modeldetail,$new_status){
        $connection = Yii::$app->db;
        $co_code = Yii::$app->user->identity->branch_cd;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_detail set status = '".$new_status."'"
                        . " where nama_file_upload = '".$modeldetail['nama_file_upload']."' and id = '".$modeldetail['id']."'"
                        . " and where co_code ='".$co_code."'";
                
                $connection->createCommand($sql)
                        ->execute();
                $transaction->commit();
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    
    public function countValid($nama_file_upload){
        $co_code = Yii::$app->user->identity->branch_cd;
        $count = PayrollUploadDetail::find()
                ->where(['status' => 'valid','nama_file_upload'=>$nama_file_upload,'co_code'=>$co_code])
                ->count();
        return $count;
    }
    
    public static function getAccount($id){
        $nama = '';
        $status = 'NA';
        
        //cari di lookup account by acct_no / rekening
        $la = \app\models\LookupAccount::findOne($id);
        if($la != null) {
            $nama = $la->acct_title;
            $status = 'OK';
        }
        else {
            //cari di account by id
            $a = \app\models\Account::findOne($id);
            if($a != null){
                $nama = $a->account_title;
                $status = 'OK';
            }
            
        }
        return ['status' => $status,'account_title' => $nama];
    }
    
    public function FTRespond($msg, $modeldetail){
        $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                
            $sql = "update payroll_upload_detail set "
                 . " ft_id = '".$msg['ft_id']."'"
                 .", ft_stat = '".$msg['ft_stat']."'"
                 .", ft_msg = '".$msg['ft_msg']."'"
                 .", date_process = ".$msg['date_process']
                 ."  where nama_file_process = '".$modeldetail['nama_file_process']."' and id = '".$modeldetail['id']."' and co_code='".$modeldetail['co_code']."'";
                //."  where nama_file_process = '".$modeldetail['nama_file_process']."' and nip = '".$modeldetail['nip']."' and co_code='".$modeldetail['co_code']."'";
                
        $connection->createCommand($sql)
                   ->execute();
        $transaction->commit();
                
                
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function UpdateINAO($msg){
        $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                
            $sql = "update payroll_upload_detail set "
                 ." ft_stat = '".$msg['ft_stat']."'"
                 ." where ft_id = '".$msg['ft_id']."'";
                
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
                
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function sumPay($nama_file_process){
//    $co_code = Yii::$app->user->identity->branch_cd;
//    $query = PayrollUploadDetail::find()->where(['nama_file_process'=>$nama_file_process,'co_code'=>$co_code]);
    $query = PayrollUploadDetail::find()->where(['nama_file_process'=>$nama_file_process]);
    $sum = $query->sum('payrollamt');
    return $sum;
    }
    
    public function countFTStatus($nama_file_process,$ft_stat){
        $query = PayrollUploadDetail::find()->where(['nama_file_process'=>$nama_file_process,'ft_stat'=>$ft_stat])->count();
        return $query; 
        
    }
    
    
}