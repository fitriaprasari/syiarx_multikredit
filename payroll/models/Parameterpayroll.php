<?php
/* 
 * Parameterpayroll
 * Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\models;

/**
 * This is the model class for table "payroll_parameter".
 *
 * @property string $kode_parameter
 * @property string $nama_institusi
 * @property string $charge_flag
 * @property string $charge_code
 * @property string $charge_type
 * @property string $charge_amt
 * @property string $narasi
 * @property string $tipe_transaksi
 */
class Parameterpayroll extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payroll_parameter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kode_parameter','nama_institusi','charge_flag','tipe_transaksi','acctno',
                'branch_co_code','branch_nm','acctname'],'required','on'=>'create'],
            [['kode_parameter','nama_institusi','charge_flag','tipe_transaksi','acctno',
                'branch_co_code','branch_nm','acctname'],'required','on'=>'updateparam'],
            [['acctno_charge'],'number'],
            [['acctno'],'number'],
//            [['charge_amt'],'validateCharge'],
            [['charge_amt'], 'number','max'=> 21],
            [['charge_type'],'string','max'=> 21],
            [['kode_parameter'], 'string', 'max' => 6],
            [['nama_institusi', 'narasi','acctname','branch_nm'], 'string', 'max' => 40],
            [['charge_code', 'tipe_transaksi'], 'string', 'max' => 100],
            [['charge_flag'], 'string', 'max' => 1],
            [['narasi'], 'string'],
            [['charge_amt'],'required', 'when' => function ($model) {
                return $model->charge_flag == 'Y';
            }, 'on' =>['updateparam','create']],
            [['charge_amt'],'validateCharge', 'when' => function ($model) {
                return $model->charge_flag == 'Y';
            }, 'on' =>['updateparam','create']],
            ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kode_parameter' => 'Kode Parameter',
            'nama_institusi' => 'Nama Perusahaan',
            'charge_flag' => 'Flag Biaya',
            'charge_code' => 'Kode Biaya',
            'charge_type' => 'Tipe Biaya',
            'charge_amt' => 'Nominal Biaya',
            'narasi' => 'Keterangan',
            'tipe_transaksi' => 'Tipe Transaksi',
            'acctno' => 'No. Rek. Debet',
            'branch_co_code' => 'Kode Cabang Rek.Debet',
            'branch_nm' => 'Nama Cabang',
            'acctname' => 'Nama Pemilik Rekening',
            'acctno_charge' => 'No. Rek. Biaya',
            'co_code'=>'Kode Cabang Inputter'
        ];
    }

    public static function getAccount($id){
        $nama ='';
        $status = 'NA';
        
        //cari di lookup account by acct_no/rekening
        $la = \app\models\LookupAccount::findOne($id);
        if($la != null){
            $nama = $la->acct_title;
            $status = 'OK';
            $co_code = $la->co_cd;
        }else{
            $a = \app\models\Account::findOne($id);
            if($a !=null){
                $nama = $a->account_title;
                $status = 'OK';
                $co_code = $a->co_code;
            }
        }
        return['status'=>$status,'account_title'=>$nama, 'co_code'=>$co_code];
        
    }
    
    public static function getDataParameterPayroll($kode_parameter,$co_code){
        
      $charge_flag = '';    //flag biaya
      $acctno = '';         //no rek debet
      $branch_co_code = ''; //kode cabang rek debet
      $charge_amt = '';     //nominal biaya
      $acctno_charge = '';  //rekening biaya
      
      $data = Parameterpayroll::findOne($kode_parameter,$co_code);
      if($data != null){
          $charge_flag = $data->charge_flag;
          $kode_parameter = $data->kode_parameter;
          $acctno = $data->acctno;
          $branch_co_code = $data->branch_co_code;
          $charge_amt = $data->charge_amt;
          $acctno_charge = $data->acctno_charge;
      }
      return
            ['charge_flag'=>$charge_flag,
             'kode_parameter'=>$kode_parameter,
             'acctno'=>$acctno,
             'branch_co_code' => $branch_co_code,
             'charge_amt'=>$charge_amt,
             'acctno_charge'=>$acctno_charge
            ];
    }
    
        public static function getBranchname($id){
        $branch_nm = '';
        $status = 'NA';
        
        //cari di branch by branch_cd
        $branch = \app\models\Branch::findOne($id);
        if($branch !=null){
            $branch_nm = $branch->branch_nm;
            $status = 'OK';
        }else{
            $branch_nm = '';
            $status ='NA';
        }
        return['status'=>$status,'branch_nm'=>$branch_nm];
    }
    
    public static function searchParameter(){
        $query = Parameterpayroll::find();
        return $query;
    }
    
    public static function cekParam($namaFile){
    $exist = Parameterpayroll::find()->where(['kode_parameter' => $namaFile])->exists();
    return $exist;
    }

//    public function validateCharge()
//    {
//        if ($this->charge_amt <= 0) {
//            $this->addError($this->charge_amt,'Nominal Biaya harus lebih dari 0');
//        }
//    }
    
    public function validateCharge($attribute, $params)
    {
            
        if ((int) $this->$attribute <= 0) {
            $this->addError($attribute, 'Nominal Biaya tidak boleh kosong.');
        }
    }



}