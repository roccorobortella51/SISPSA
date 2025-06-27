<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoices_sum_up".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $beneficiarioID
 * @property float|null $suma
 */
class InvoicesSumUp extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoices_sum_up';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['beneficiarioID', 'suma'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['beneficiarioID'], 'default', 'value' => null],
            [['beneficiarioID'], 'integer'],
            [['suma'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'beneficiarioID' => 'Beneficiario ID',
            'suma' => 'Suma',
        ];
    }

}
