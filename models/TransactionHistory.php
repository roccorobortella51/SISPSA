<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction_history".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $user_id
 * @property string|null $transaction_type
 * @property string|null $transaction_details
 * @property string|null $transaction_date
 * @property string|null $updated_at
 *
 * @property UserDatos $user
 */
class TransactionHistory extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaction_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'transaction_type', 'transaction_details', 'transaction_date', 'updated_at'], 'default', 'value' => null],
            [['created_at', 'transaction_date', 'updated_at'], 'safe'],
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['transaction_type', 'transaction_details'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'transaction_type' => 'Transaction Type',
            'transaction_details' => 'Transaction Details',
            'transaction_date' => 'Transaction Date',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

}
