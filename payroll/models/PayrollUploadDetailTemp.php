<?php
namespace app\modules\payroll\models;
use app\modules\payroll\models\PayrollUploadDetail;
use Yii;

/**
 * This is the model class for table "payroll_upload_detail_temp".
 *
 * @property string $nip
 * @property string $ccy
 * @property string $narasi
 * @property string $sign
 * @property string $id
 * @property string $kode_parameter
 * @property string $nama_file_upload
 * @property string $nama_file_process
 * @property double $payrollamt
 * @property string $acctno_cr
 * @property string $acctno_db
 * @property string $charge_amt
 * @property string $status
 */
class PayrollUploadDetailTemp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payroll_upload_detail_temp';
    }
    
    public static function primaryKey(){
        return['id_seq','nama_file_upload'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nama_file_upload', 'nama_file_process'], 'required'],
            [['nama_file_upload', 'nama_file_process'], 'string'],
            [['payrollamt'], 'number'],
            [['acctno_cr', 'acctno_db'], 'string'],
            [['charge_amt'], 'string', 'max' => 25],
            [['ccy'], 'string', 'max' => 3],
            [['narasi'], 'string', 'max' => 35],
            [['acct_title'], 'string', 'max' => 50],
            [['id'], 'string', 'max' => 4],
            [['id_seq'], 'number'],
            [['kode_parameter'], 'string', 'max' => 6],
            [['status'], 'string', 'max' => 100],
            [['narasi'],'validateNarasi','on' =>['uploadpayroll','upload']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ccy' => 'Kurs',
            'narasi' => 'Narasi',
            'id' => 'ID',
            'id_seq'=>'No.seq',
            'kode_parameter' => 'Kode Parameter',
            'nama_file_upload' => 'Nama File Upload',
            'nama_file_process' => 'Nama File Process',
            'payrollamt' => 'Nominal Gaji',
            'acctno_cr' => 'No. Rek. Kredit',
            'acctno_db' => 'No. Rek. Debit',
            'charge_amt' => 'Nominal Biaya',
            'status' => 'Status Validasi',
            'acct_title' => 'Nama Rekening',
        ];
    }
    
    public function searchSum($nama_file_upload)
    {
        $co_code = Yii::$app->user->identity->branch_cd;
        $query = PayrollUploadDetailTemp::find()->where(['nama_file_upload' => $nama_file_upload,'co_code'=>$co_code]);
        $sum = $query->sum('payrollamt');
        return $sum;
    }
    
    public function countValid($nama_file_upload)
    {
        $co_code = Yii::$app->user->identity->branch_cd;
        $count = PayrollUploadDetail::find()
                ->where(['status' => 'valid', 'nama_file_upload' => $nama_file_upload,'co_code'=>$co_code])
                ->count();
        return $count;
    }
    
        public function searchSumPayrollAmt($nama_file_upload){
        $co_code =  Yii::$app->user->identity->branch_cd;
        $query = PayrollUploadDetailTemp::find()->where(['nama_file_upload'=>$nama_file_upload,'co_code'=>$co_code]);
        $sum = $query->sum('payrollamt');
        return $sum;
    }

    public function searchOnly($nama_file_upload){
//        $co_code = Yii::$app->user->identity->branch_cd;
        $dataProvider = PayrollUploadDetailTemp::find()->where(['nama_file_upload' => $nama_file_upload])
                ->orderBy(['id' => SORT_ASC])
                ->All();
        return $dataProvider;
    }
    
    public function deleteData($model){
            $connection = Yii::$app->db;
//            $co_code = Yii::$app->user->identity->branch_cd;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "delete from payroll_upload_detail_temp "
                        . "where nama_file_upload = '".$model['nama_file_upload']."'";
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function deleteTemp($nama_file_upload,$kode_parameter){
         $connection = Yii::$app->db;
         $co_code = Yii::$app->user->identity->branch_cd;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "delete from payroll_upload_detail_temp "
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
    
     public function updateData($modeldetail,$modelresult){
        $connection = Yii::$app->db;
//        $co_code = Yii::$app->user->identity->branch_cd;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_detail_temp set status = '".$modelresult[0]."' "
                        . "where nama_file_upload = '".$modeldetail['nama_file_upload']."' and id = '".$modeldetail['id']."'";
                
                $connection->createCommand($sql)
                        ->execute();
                $transaction->commit();
                
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function updateDataError($modeldetail,$modelresult){
        $connection = Yii::$app->db;
//        $co_code = Yii::$app->user->identity->branch_cd;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_detail_temp set status = '".$modelresult[0]."' "
                        . "where nama_file_upload = '".$modeldetail['nama_file_upload']."' and id = '".$modeldetail['id']."' and id_seq='".$modeldetail['id_seq']."'";
                
                $connection->createCommand($sql)
                        ->execute();
                $transaction->commit();
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function updateDataValid($modeldetail,$modelresult){
        $connection = Yii::$app->db;
//        $co_code = Yii::$app->user->identity->branch_cd;
            $transaction = $connection->beginTransaction();
            try {
//                $sql = "update payroll_upload_detail_temp set status = '".$modelresult[0]."' "
//                        . "where nama_file_upload = '".$modeldetail['nama_file_upload']."' and id = '".$modeldetail['id']."'";
                $sql = "update payroll_upload_detail_temp set status = '".$modelresult[0]."', acct_title = '".$modelresult[1]."' "
                        . "where nama_file_upload = '".$modeldetail['nama_file_upload']."' and id = '".$modeldetail['id']."'";
                
                $connection->createCommand($sql)
                        ->execute();
                $transaction->commit();
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function searchSumChargeAmt($nama_file_upload){
        $co_code =  Yii::$app->user->identity->branch_cd;
        $query = PayrollUploadDetailTemp::find()->where(['nama_file_upload'=>$nama_file_upload,'co_code'=>$co_code]);
        $sum = $query->sum('charge_amt');
        if($sum == NULL){
            $sum = 0;
        }else{
            $sum = $sum;
        }
        return $sum;
    }
    
    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }
       
    public function validateNarasi($attribute, $params)
    {
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $this->$attribute)) {
            $this->addError($attribute, 'Tidak boleh ada karakter spesial');
        }
    }
}
