<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\models;

use app\modules\payroll\models\Parameterpayroll;
use app\modules\payroll\models\PayrollUploadMaster;
use app\models\User;
use Yii;

/**
 * This is the model class for table "payroll_upload_master".
 *
 * @property string $kode_parameter
 * @property string $nama_file_upload
 * @property string $nama_file_process
 * @property string $date_upload
 * @property string $date_exec
 * @property string $valid_stat
 * @property string $co_code
 * @property integer $inputter
 * @property integer $authoriser
 * @property string $charge_flag
 * @property string $acctno_charge
 * @property string $charge_amt
 * @property string $time_upload
 * @property string $time_exec
 * @property string $otor_stat
 */
class PayrollUploadMaster extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    
    public $narasi;
    
    public static function tableName()
    {
        return 'payroll_upload_master';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kode_parameter', 'nama_file_upload','narasi','date_exec','time_exec'], 'required'],
            [['nama_file_upload', 'nama_file_process'], 'string'],
            [['date_upload','date_process', 'date_exec',
              'time_upload', 'time_exec'], 'safe'],
            [['date_exec'],'required','on'=>['carihasil']],
            [['inputter', 'authoriser'], 'integer'],
            [['inputter_name', 'authoriser_name'], 'string','max'=>50],
            [['kode_parameter'], 'string', 'max' => 6],
            [['narasi'], 'string', 'max' => 30],
            [['valid_stat', 'co_code', 'otor_stat'], 'string', 'max' => 20],
            [['charge_flag'], 'string', 'max' => 1],
            [['acctno_charge'], 'string', 'max' => 10],
            [['charge_amt'], 'string', 'max' => 25],
//            [['narasi'],'validateNarasi','on' =>['uploadpayroll','upload']],
//            [['narasi'],'validateNarasi'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'No. Baris',
            'kode_parameter' => 'Kode Parameter',
            'nama_file_upload' => 'Nama File Upload',
            'nama_file_process' => 'Nama File Process',
            'date_upload' => 'Tanggal Upload',
            'date_exec' => 'Tanggal Eksekusi',
            'date_process' => 'Tanggal Proses',
            'valid_stat' => 'Status Validasi',
            'co_code' => 'Kode Cabang',
            'inputter' => 'Inputter',
            'authoriser' => 'Authoriser',
            'charge_flag' => 'Charge Flag',
            'acctno_charge' => 'Rek. Biaya',
            'charge_amt' => 'Nominal Biaya',
            'time_upload' => 'Jam Upload',
            'time_exec' => 'Jam Eksekusi',
            'otor_stat' => 'Status Otorisasi',
            'otor_date' => 'Tanggal Otor',
            'otor_tm' => 'Jam Otor',
            'exec_stat' => 'Status Eksekusi',
            'narasi'=>'Narasi',
];
        
    }
    
    public function validasi_master($fullname_master,$extension,$co_code){
              
        if($extension <> "csv"){
            $message = "Format file salah. Harus berformat .csv";
        }else{
        //validasi format namafile master
        //check whether already in appropiate format
        //YYYY(nospace)DD(nospace)MM(nospace)NumberSequential.NAMA_PARAMETER
        //count date part
//            $countDate = preg_match_all("/[0-9]/", $namaFile);
//            $countNamaFile = strlen($namaFile);
            $namaFile = substr($fullname_master, 0, -4);
            $dot_num = substr_count($namaFile, ".");
            $countDate = substr($namaFile,0,11);
//            $param = str_replace($countDate,"",$namaFile);
            $beforeParam = strtok($namaFile, '.');
            $param = str_replace($beforeParam . ".", "", $namaFile);
            //check parameter exist
            $param_exist = Parameterpayroll::cekParam($param);
            
            $master_exist = PayrollUploadMaster::find()
                            ->where(['nama_file_upload'=>$fullname_master])
                            ->andWhere(['co_code' => $co_code])
                            ->exists();
           
            if($param_exist==false){
                $message = "PERINGATAN : Parameter payroll belum terdaftar,
                    silahkan buat parameter payroll baru.";
                
            }
            
            //check whether Datepart number must be equal to 10 character
            //or filename number must be equal to 17 character
            //else if (($countDate > 10 || $countDate < 10) || ($dot_num > 1))
            else if (($dot_num > 1))
            {
                 $message = "PERINGATAN : Format nama file '" . $namaFile .
                        "' salah. Silahkan periksa kembali sesuai format.";
                
            }
            else if ($param_exist==true && $master_exist==false){
                    return true;
            }
            else if($master_exist == true){
                $message = "PERINGATAN : Data payroll sudah terdaftar";
            }
            }
            Yii::$app->session->setFlash('danger', $message);
    }
    
    public function getDataMaster(){
        
        $modelFile = PayrollUploadMaster::find()
                ->join('INNER JOIN','payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
                ->where(['payroll_upload_master.valid_stat'=>'WAITING'])
                ->andWhere(['otor_stat'=>'INAU'])
                ->andWhere(['payroll_parameter.tipe_transaksi' => 'MULTIKREDIT'])
                ->all();
        
        return $modelFile;
    }
    
//    public function getDataByDate(){
//         $modelFile = PayrollUploadMaster::find()
//                ->join('INNER JOIN','payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
//                ->where(['payroll_upload_master.valid_stat'=>'WAITING'])
//                ->andWhere(['otor_stat'=>'INAU'])
//                ->andWhere(['payroll_parameter.tipe_transaksi' => 'MULTIKREDIT'])
//                ->all();
//        
//        return $modelFile;
//    }
    
    public function updateParam($acctno_charge,$charge_amt,$kode_param,$id_lama){
        $connection = Yii::$app->db;
//        var_dump($charge_amt);die;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_master set "
                        ."acctno_charge = '".$acctno_charge."'"
                        .", charge_amt = '".$charge_amt."'"
                        .", kode_parameter = '".$kode_param."'".
                        "  where kode_parameter= '".$id_lama."'";
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
                
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function countData($co_code){
        $n = PayrollUploadMaster::find()
            ->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
            ->where(['payroll_upload_master.co_code'=>$co_code])
            ->andWhere(['payroll_upload_master.date_upload'=>date("Ymd")])
            ->andWhere(['payroll_parameter.tipe_transaksi' => "MULTIKREDIT"])
            ->count();
        return $n;
    }
    
    public function getDataMasterAuth(){
//       $modelFile = PayrollUploadMaster::find()
//                ->join('INNER JOIN','payroll_parameter','payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
//                ->where(['payroll_upload_master.otor_stat'=>'AUTHORIZED'])
//                ->andWhere(['payroll_upload_master.exec_stat'=>'WAITING'])
//                ->andWhere(['payroll_parameter.tipe_transaksi'=>'MULTIKREDIT'])
//                ->all();
//        return $modelFile;
        $modelFile = PayrollUploadMaster::find()
                    ->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
                    ->where(['payroll_upload_master.otor_stat' => "AUTHORIZED"])
                    ->andWhere(['payroll_upload_master.exec_stat' => "WAITING"])
                    ->andWhere(['payroll_upload_master.valid_stat' => "VALID"])
                    ->andWhere(['payroll_parameter.tipe_transaksi' => "MULTIKREDIT"])
                    ->all();
        
        return $modelFile;
    }
    
    public function deleteMaster($nama_file, $co_code){
         $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "delete from payroll_upload_master where "
                        . "nama_file_upload = '".$nama_file."' and co_code = '".$co_code."'";
                
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
                
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
                
        
    }
    
    public function updateData($modeldetail,$new_status){
        
        $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_master set valid_stat = '".$new_status."' where nama_file_upload = '".$modeldetail['nama_file_upload']."' and co_code ='".$modeldetail['co_code']."'";
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
        
    }
    
    public function updateStartExec($model,$new_status){
        $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_master set "
                        . " exec_stat = '".$new_status."'"
                        . " where nama_file_process = '"
                        .$model['nama_file_process']."'"
                        . " and co_code = '".$model['co_code']."'";
                
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
                
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
    public function updateStatExec($modeldetail,$exec_stat,$date_process){
        
        $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $sql = "update payroll_upload_master set "
                        . "date_process=".$date_process
                        . ",exec_stat = '".$exec_stat."'"
                        . " where nama_file_process = '"
                        .$modeldetail['nama_file_process']."'"
                        . " and co_code ='".$modeldetail['co_code']."'";
                
                $connection->createCommand($sql)
                           ->execute();
                $transaction->commit();
                
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', "error database:" . $e->getMessage());
                echo $e->getMessage();
            }
    }
    
     public function getInputter() {
        $inputter = Yii::$app->user->identity->id;
        $model = User::findOne($inputter);
        if (!empty($model)){
            $inputter_name = $model->user_name;
        }
        return $inputter_name;
     }
     
      public function validateNarasi2($attribute, $params)
    {
        if (strpos('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $this->$attribute)) {
            $this->addError($attribute, 'Tidak boleh ada karakter spesial');
        }
    }
    
    public function validateNarasi($attribute, $params)
    {
        if (!in_array($this->$attribute, ['/[\'^£$%&*()}{@#~?><>,|=_+¬-]/'])) {
            $this->addError($attribute, 'The country must be either "USA" or "Indonesia".');
        }
    }
  

}
