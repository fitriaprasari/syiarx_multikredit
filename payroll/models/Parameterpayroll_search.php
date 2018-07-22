<?php
/* Author       : fitriana.dewi
 * Created Date : 2018-01-05
 */
namespace app\modules\payroll\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\payroll\models\Parameterpayroll;

/**
 * Parameterpayroll_search represents the model behind the search form about `\app\modules\payroll\models\Parameterpayroll`.
 */
class Parameterpayroll_search extends Parameterpayroll
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return[
            [[
            'kode_parameter',
            'nama_institusi',
            'charge_flag',
            'charge_code',
            'charge_type',
            'charge_amt',
            'narasi',
            'tipe_transaksi',
            'acctno',
            'co_code',
            'branch_nm',
            'acctname',
            'acctno_charge'],'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Parameterpayroll::find()
                 ->where(['tipe_transaksi'=>"MULTIKREDIT"]);
                

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $query->andFilterWhere(['like', 'kode_parameter', $this->kode_parameter])
            ->andFilterWhere(['like', 'nama_institusi', $this->nama_institusi])
            ->andFilterWhere(['like', 'charge_flag', $this->charge_flag])
            ->andFilterWhere(['like', 'charge_code', $this->charge_code])
            ->andFilterWhere(['like', 'charge_type', $this->charge_type])
            ->andFilterWhere(['like', 'narasi', $this->narasi])
            ->andFilterWhere(['like', 'tipe_transaksi', $this->tipe_transaksi])
            ->andFilterWhere(['like', 'charge_amt', $this->charge_amt])
            ->andFilterWhere(['like', 'acctno', $this->acctno])
            ->andFilterWhere(['like', 'acctno', $this->acctno_charge]);

        return $dataProvider;
    }
    
     public static function searchBy($column_name,$key){
        $query = Parameterpayroll::find();
        
        switch ($column_name) {
            case $column_name == "kode_parameter":
                $query->andFilterWhere([
                    'or',
                        ['like', 'kode_parameter', strtoupper($key)],
                ]);
                break;
            case $column_name == "nama_institusi":
                $query->andFilterWhere([
                    'or',
                        ['like', 'nama_institusi', strtoupper($key)],
                ]);
                break;
            case $column_name == "charge_flag":
                $query->andFilterWhere([
                    'or',
                        ['like', 'charge_flag', strtoupper($key)],
                ]);
                break;
            case $column_name == "charge_code":
                $query->andFilterWhere([
                    'or',
                        ['like', 'charge_code', strtoupper($key)],
                ]);
                break;
            case $column_name == "charge_type":
                $query->andFilterWhere([
                    'or',
                        ['like', 'charge_type', strtoupper($key)],
                ]);
                break;
            //show the option from db
            case $column_name = "narasi":
                $query->andFilterWhere([
                    'or',
                        ['like', 'narasi', strtoupper($key)],
                ]);
                break;
            //show the option from db
            case $column_name = "tipe_transaksi":
                $query->andFilterWhere([
                    'or',
                        ['like', 'tipe_transaksi', strtoupper($key)],
                ]);
                break;
            case $column_name = "charge_amt":
                $query->andFilterWhere([
                    'or',
                        ['like', 'charge_amt', strtoupper($key)],
                ]);
                break;
        }
            $query->andFilterWhere(['tipe_transaksi'=>"MULTIKREDIT"]);
            return new ActiveDataProvider(['query' => $query]);
    }
    
    /**
     * Finds the Param model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Wicmaster the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findParam($kode_parameter) {
        $model = Parameterpayroll::findOne($kode_parameter);
        if ($model) {
            return $model;
        } else {
            throw new NotFoundHttpException('Data parameter payroll tidak ditemukan.');
       
        }
    }
}