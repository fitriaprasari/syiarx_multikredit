<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\payroll\models\PayrollUploadMaster;

/**
 * PayrollUploadMaster_search represents the model behind the search form about `app\modules\payroll\models\upload_payroll\PayrollUploadMaster`.
 */
class PayrollUploadMaster_search extends PayrollUploadMaster
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kode_parameter', 'nama_file_upload', 'nama_file_process', 'date_upload', 'date_exec', 'valid_stat', 'co_code', 'charge_flag', 'acctno_charge', 'charge_amt', 'time_upload', 'time_exec', 'otor_stat'], 'safe'],
            [['inputter', 'authoriser'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    
    public function searchNau($params,$co_code){
//        var_dump($co_code);die;
             
          $query = PayrollUploadMaster::find()
                ->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
                ->where(['payroll_upload_master.otor_stat' => 'INAU'])
                ->andWhere(['payroll_upload_master.co_code'=>$co_code])
                ->andWhere(['payroll_upload_master.valid_stat'=>'VALID'])
                ->andWhere(['payroll_parameter.tipe_transaksi' => 'MULTIKREDIT'])
                ->orderBy(['date_exec'=>SORT_ASC,
                           'time_exec'=> SORT_ASC]);
       
        $dataProvider = new ActiveDataProvider(['query'=>$query,]);
        $this->load($params);
        
        if(!$this->validate()){
            return $dataProvider;
        }
                
        $query->andFilterWhere([
            'date_upload' => $this->date_upload,
            'date_exec' => $this->date_exec,
            'inputter' => $this->inputter,
            'authoriser' => $this->authoriser,
            'time_upload' => $this->time_upload,
            'time_exec' => $this->time_exec,
        ]);

        $query->andFilterWhere(['like', 'payroll_upload_master.kode_parameter', $this->kode_parameter])
            ->andFilterWhere(['like', 'nama_file_upload', $this->nama_file_upload])
            ->andFilterWhere(['like', 'nama_file_process', $this->nama_file_process])
            ->andFilterWhere(['like', 'valid_stat', $this->valid_stat])
            ->andFilterWhere(['like', 'co_code', $this->co_code])
            ->andFilterWhere(['like', 'charge_flag', $this->charge_flag])
            ->andFilterWhere(['like', 'acctno_charge', $this->acctno_charge])
            ->andFilterWhere(['like', 'charge_amt', $this->charge_amt])
            ->andFilterWhere(['like', 'otor_stat', $this->otor_stat]);
        
        return $dataProvider;
    }
    
    public function searchByDate($date_exec,$co_code){
        
        $modelFile = PayrollUploadMaster::find()
                ->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
                ->where(['payroll_upload_master.co_code' => $co_code])
                ->andwhere(['payroll_upload_master.otor_stat' => "AUTHORIZED"])
                ->andWhere(['payroll_upload_master.valid_stat' => "VALID"])
                ->andWhere(['payroll_upload_master.date_exec' => $date_exec])
                ->andWhere(['payroll_parameter.tipe_transaksi' => "MULTIKREDIT"]);
        return $modelFile;
    }
       
    public function searchAuthorized($params,$co_code){
       //$co_code = Yii::$app->user->identity->branch_cd;
        
        $query = PayrollUploadMaster::find()
                ->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
                ->where(['payroll_upload_master.co_code' => $co_code])
                ->andwhere(['payroll_upload_master.otor_stat' => "AUTHORIZED"])
                ->andWhere(['payroll_upload_master.valid_stat' => "VALID"])
                ->andWhere(['payroll_parameter.tipe_transaksi' => "MULTIKREDIT"])
                ->orderBy(['date_exec' => SORT_DESC,
                           'time_exec' => SORT_DESC]);

        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
//        
        if(!$this->validate()){
            return $dataProvider;
        }
                
        $query->andFilterWhere([
            'date_upload' => $this->date_upload,
            'date_exec' => $this->date_exec,
            'inputter' => $this->inputter,
            'authoriser' => $this->authoriser,
            'time_upload' => $this->time_upload,
            'time_exec' => $this->time_exec,
        ]);

        $query->andFilterWhere(['like', 'payroll_upload_master.kode_parameter', $this->kode_parameter])
            ->andFilterWhere(['like', 'nama_file_upload', $this->nama_file_upload])
            ->andFilterWhere(['like', 'nama_file_process', $this->nama_file_process])
            ->andFilterWhere(['like', 'valid_stat', $this->valid_stat])
            ->andFilterWhere(['like', 'co_code', $this->co_code])
            ->andFilterWhere(['like', 'charge_flag', $this->charge_flag])
            ->andFilterWhere(['like', 'acctno_charge', $this->acctno_charge])
            ->andFilterWhere(['like', 'charge_amt', $this->charge_amt])
            ->andFilterWhere(['like', 'otor_stat', $this->otor_stat]);
        
        return $dataProvider;
    }
    
//    public function searchAuthorized($params,$co_code){
////        var_dump($co_code);die;
//        $query = PayrollUploadMaster::find()->where(['otor_stat'=>"AUTHORIZED"])
//                ->andWhere(['valid_stat'=>"VALID"])
//                ->orderBy(['date_exec'=>SORT_DESC,
//                           'time_exec'=>SORT_DESC]);
//        
////        $query = PayrollUploadMaster::find()
////        ->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
////        ->where(['payroll_upload_master.co_code' =>"'".$co_code."'"])
////        ->andWhere(['payroll_upload_master.otor_stat' => "AUTHORIZED"])
////        ->andWhere(['payroll_upload_master.valid_stat' => "VALID"])
////        ->andWhere(['payroll_parameter.tipe_transaksi' => "MULTIKREDIT"])
////        ->orderBy(['date_exec' => SORT_DESC,
////                   'time_exec' => SORT_DESC]);
////              
//        $dataProvider = new ActiveDataProvider(['query'=>$query,]);
//        $this->load($params,$co_code);
//        
//        if(!$this->validate()){
//            return $dataProvider;
//        }
//                
//        $query->andFilterWhere([
//            'date_upload' => $this->date_upload,
//            'date_exec' => $this->date_exec,
//            'inputter' => $this->inputter,
//            'authoriser' => $this->authoriser,
//            'time_upload' => $this->time_upload,
//            'time_exec' => $this->time_exec,
//        ]);
//
//        $query->andFilterWhere(['like', 'kode_parameter', $this->kode_parameter])
//            ->andFilterWhere(['like', 'nama_file_upload', $this->nama_file_upload])
//            ->andFilterWhere(['like', 'nama_file_process', $this->nama_file_process])
//            ->andFilterWhere(['like', 'valid_stat', $this->valid_stat])
//            ->andFilterWhere(['like', 'co_code', $this->co_code])
//            ->andFilterWhere(['like', 'charge_flag', $this->charge_flag])
//            ->andFilterWhere(['like', 'acctno_charge', $this->acctno_charge])
//            ->andFilterWhere(['like', 'charge_amt', $this->charge_amt])
//            ->andFilterWhere(['like', 'otor_stat', $this->otor_stat]);
//        
//        return $dataProvider;
//    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$co_code)
    {
        $query = PayrollUploadMaster::find()
                ->join('INNER JOIN', 'payroll_parameter', 'payroll_upload_master.kode_parameter = payroll_parameter.kode_parameter')
                ->where(['payroll_upload_master.otor_stat' => 'INAU'])
                ->andWhere(['payroll_upload_master.co_code'=>$co_code])
                ->andWhere(['payroll_parameter.tipe_transaksi' => 'MULTIKREDIT'])
                ->orderBy(['date_upload'=>SORT_DESC,
                           'time_upload'=> SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'date_upload' => $this->date_upload,
            'date_exec' => $this->date_exec,
            'inputter' => $this->inputter,
            'authoriser' => $this->authoriser,
            'time_upload' => $this->time_upload,
            'time_exec' => $this->time_exec,
        ]);

        $query->andFilterWhere(['like', 'payroll_upload_master.kode_parameter', $this->kode_parameter])
            ->andFilterWhere(['like', 'nama_file_upload', $this->nama_file_upload])
            ->andFilterWhere(['like', 'nama_file_process', $this->nama_file_process])
            ->andFilterWhere(['like', 'valid_stat', $this->valid_stat])
            ->andFilterWhere(['like', 'co_code', $this->co_code])
            ->andFilterWhere(['like', 'charge_flag', $this->charge_flag])
            ->andFilterWhere(['like', 'acctno_charge', $this->acctno_charge])
            ->andFilterWhere(['like', 'charge_amt', $this->charge_amt])
            ->andFilterWhere(['like', 'otor_stat', $this->otor_stat]);

        return $dataProvider;
    }
}
