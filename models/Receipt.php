<?php
// app/models/Receipt.php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "receipts".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $payment_id
 * @property int|null $installment_id
 * @property int|null $contract_id
 * @property int|null $user_id
 * @property string|null $receipt_number
 * @property string|null $issue_date
 * @property string|null $coverage_start_date
 * @property string|null $coverage_end_date
 * @property float|null $amount
 * @property string|null $currency
 * @property string|null $payment_method
 * @property string|null $reference_number
 * @property string|null $bank_name
 * @property string|null $payment_place
 * @property string|null $status
 * @property string|null $pdf_path
 * @property string|null $html_content
 */
class Receipt extends ActiveRecord
{
    // Status constants as class constants - these are safe
    const STATUS_GENERATED = 'Generado';
    const STATUS_CANCELLED = 'Anulado';
    const STATUS_PRINTED = 'Impreso';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'receipts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_id', 'installment_id', 'contract_id', 'user_id'], 'default', 'value' => null],
            [['payment_id', 'installment_id', 'contract_id', 'user_id'], 'integer'],
            [['created_at', 'issue_date', 'coverage_start_date', 'coverage_end_date'], 'safe'],
            [['amount'], 'number'],
            [['html_content'], 'string'],
            [['receipt_number', 'currency', 'payment_method', 'reference_number', 'bank_name', 'payment_place', 'status', 'pdf_path'], 'string', 'max' => 255],
            [['receipt_number'], 'unique'],
            [['status'], 'in', 'range' => [self::STATUS_GENERATED, self::STATUS_CANCELLED, self::STATUS_PRINTED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Fecha Creación',
            'payment_id' => 'Pago ID',
            'installment_id' => 'Cuota ID',
            'contract_id' => 'Contrato ID',
            'user_id' => 'Usuario ID',
            'receipt_number' => 'Número de Recibo',
            'issue_date' => 'Fecha Emisión',
            'coverage_start_date' => 'Vigencia Desde',
            'coverage_end_date' => 'Vigencia Hasta',
            'amount' => 'Monto',
            'currency' => 'Moneda',
            'payment_method' => 'Forma de Pago',
            'reference_number' => 'Número Referencia',
            'bank_name' => 'Banco',
            'payment_place' => 'Lugar de Pago',
            'status' => 'Estatus',
            'pdf_path' => 'PDF',
        ];
    }

    /**
     * Gets query for [[Payment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Pagos::class, ['id' => 'payment_id']);
    }

    /**
     * Gets query for [[Installment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInstallment()
    {
        return $this->hasOne(Cuotas::class, ['id' => 'installment_id']);
    }

    /**
     * Gets query for [[Contract]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContract()
    {
        return $this->hasOne(Contratos::class, ['id' => 'contract_id']);
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

    /**
     * Generate unique receipt number using a safer approach
     * Format: REC-YYYYMMDD-XXXXX
     * 
     * @return string
     */
    public static function generateReceiptNumber()
    {
        $prefix = 'REC-' . date('Ymd');

        // Get the highest receipt number for today
        $sql = "SELECT receipt_number FROM receipts WHERE receipt_number LIKE :prefix ORDER BY receipt_number DESC LIMIT 1";
        $lastReceiptNumber = Yii::$app->db->createCommand($sql, [':prefix' => $prefix . '%'])->queryScalar();

        if ($lastReceiptNumber) {
            // Extract the numeric part (last 5 digits)
            $parts = explode('-', $lastReceiptNumber);
            $lastNumber = intval(end($parts));
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        $receiptNumber = $prefix . '-' . $newNumber;

        // Double-check uniqueness (in case of race condition)
        $exists = Receipt::findOne(['receipt_number' => $receiptNumber]);
        if ($exists) {
            // Add microtime as suffix to ensure uniqueness
            $receiptNumber = $prefix . '-' . $newNumber . '-' . substr(microtime(), 2, 4);
        }

        return $receiptNumber;
    }

    /**
     * Get formatted amount
     * 
     * @return string
     */
    public function getFormattedAmount()
    {
        return number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get formatted amount with currency
     * 
     * @return string
     */
    public function getFormattedAmountWithCurrency()
    {
        $currency = $this->currency ?: 'USD';
        return $currency . ' ' . $this->getFormattedAmount();
    }

    /**
     * Get status badge HTML
     * 
     * @return string
     */
    public function getStatusBadge()
    {
        $badgeClass = 'badge-secondary';
        $badgeText = $this->status ?: 'Desconocido';

        if ($this->status === self::STATUS_GENERATED) {
            $badgeClass = 'badge-info';
            $badgeText = 'Generado';
        } elseif ($this->status === self::STATUS_PRINTED) {
            $badgeClass = 'badge-success';
            $badgeText = 'Impreso';
        } elseif ($this->status === self::STATUS_CANCELLED) {
            $badgeClass = 'badge-danger';
            $badgeText = 'Anulado';
        }

        return '<span class="badge ' . $badgeClass . '">' . $badgeText . '</span>';
    }

    /**
     * Get status label
     * 
     * @return string
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_GENERATED => 'Generado',
            self::STATUS_PRINTED => 'Impreso',
            self::STATUS_CANCELLED => 'Anulado',
        ];

        return isset($labels[$this->status]) ? $labels[$this->status] : $this->status;
    }

    /**
     * Before save event
     * 
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                // ONLY generate receipt number if it's not already set
                if (empty($this->receipt_number)) {
                    $this->receipt_number = static::generateReceiptNumber();
                }

                // Set issue date if not set
                if (empty($this->issue_date)) {
                    $this->issue_date = date('Y-m-d');
                }

                // Set default status if not set
                if (empty($this->status)) {
                    $this->status = self::STATUS_GENERATED;
                }

                // Set default currency if not set
                if (empty($this->currency)) {
                    $this->currency = 'USD';
                }
            }

            // Sanitize HTML content - Remove any emojis or problematic characters
            if (!empty($this->html_content)) {
                // Remove emojis and 4-byte UTF-8 characters
                $this->html_content = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $this->html_content);
                $this->html_content = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $this->html_content);
                $this->html_content = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $this->html_content);
                $this->html_content = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $this->html_content);
                $this->html_content = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $this->html_content);
                $this->html_content = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $this->html_content);
            }

            return true;
        }

        return false;
    }
}
