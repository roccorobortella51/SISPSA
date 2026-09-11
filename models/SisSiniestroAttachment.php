<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "sis_siniestro_attachment".
 *
 * @property int $id
 * @property int $siniestro_id
 * @property string $filename
 * @property string $original_filename
 * @property string $file_path
 * @property string $file_type
 * @property int $file_size
 * @property string|null $document_type
 * @property string|null $description
 * @property string $created_at
 * @property string|null $deleted_at
 *
 * @property SisSiniestro $siniestro
 */
class SisSiniestroAttachment extends ActiveRecord
{
    // Document type constants
    const TYPE_RECETA = 'Receta Médica';
    const TYPE_INFORME = 'Informe Médico';
    const TYPE_AUTORIZACION = 'Autorización';
    const TYPE_RESULTADO = 'Resultado de Examen';
    const TYPE_OTRO = 'Otro';

    public $uploadFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_siniestro_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['siniestro_id', 'filename', 'original_filename', 'file_path', 'file_type', 'file_size'], 'required'],
            [['siniestro_id', 'file_size'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
            [['filename', 'original_filename', 'file_path'], 'string', 'max' => 500],
            [['file_type', 'document_type'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 500],
            [
                ['uploadFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg, pdf, doc, docx',
                'maxSize' => 1024 * 1024 * 10,
                'tooBig' => 'El archivo no debe exceder 10MB.'
            ],
            [['siniestro_id'], 'exist', 'skipOnError' => true, 'targetClass' => SisSiniestro::class, 'targetAttribute' => ['siniestro_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'siniestro_id' => 'Atención/Cita',
            'filename' => 'Nombre de Archivo',
            'original_filename' => 'Nombre Original',
            'file_path' => 'Ruta del Archivo',
            'file_type' => 'Tipo de Archivo',
            'file_size' => 'Tamaño',
            'document_type' => 'Tipo de Documento',
            'description' => 'Descripción',
            'created_at' => 'Subido El',
            'deleted_at' => 'Eliminado El',
            'uploadFile' => 'Archivo',
        ];
    }

    /**
     * Gets query for [[Siniestro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiniestro()
    {
        return $this->hasOne(SisSiniestro::class, ['id' => 'siniestro_id']);
    }

    /**
     * Get document type options for dropdown
     *
     * @return array
     */
    public static function getDocumentTypeOptions()
    {
        return [
            '' => 'Seleccionar tipo...',
            self::TYPE_RECETA => 'Receta Médica',
            self::TYPE_INFORME => 'Informe Médico',
            self::TYPE_AUTORIZACION => 'Autorización',
            self::TYPE_RESULTADO => 'Resultado de Examen',
            self::TYPE_OTRO => 'Otro',
        ];
    }

    /**
     * Get file icon based on file type
     *
     * @return string
     */
    public function getFileIcon()
    {
        $extension = strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));

        $icons = [
            'pdf' => 'fa-file-pdf text-danger',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'jpg' => 'fa-file-image text-success',
            'jpeg' => 'fa-file-image text-success',
            'png' => 'fa-file-image text-success',
            'gif' => 'fa-file-image text-success',
            'xls' => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
        ];

        return $icons[$extension] ?? 'fa-file text-secondary';
    }

    /**
     * Get formatted file size
     *
     * @return string
     */
    public function getFormattedSize()
    {
        $size = (int)$this->file_size;
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / 1048576, 2) . ' MB';
        }
    }

    /**
     * Get full URL for the attachment
     *
     * @return string
     */
    public function getFileUrl()
    {
        return Yii::$app->urlManager->createAbsoluteUrl($this->file_path);
    }

    /**
     * Save uploaded file to server
     *
     * @return bool
     */
    public function saveUploadedFile()
    {
        if (!$this->uploadFile || !$this->uploadFile instanceof UploadedFile) {
            return false;
        }

        try {
            // Generate unique filename
            $extension = $this->uploadFile->extension;
            $filename = uniqid('attachment_' . $this->siniestro_id . '_') . '.' . $extension;

            // Define upload path - use the siniestro_id as the subfolder
            $uploadPath = Yii::getAlias('@webroot/uploads/siniestro_attachments/' . $this->siniestro_id);

            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0777, true)) {
                    Yii::error("Failed to create directory: {$uploadPath}", __METHOD__);
                    return false;
                }
            }

            $filePath = $uploadPath . '/' . $filename;
            $relativePath = '/uploads/siniestro_attachments/' . $this->siniestro_id . '/' . $filename;

            // ✅ CRITICAL FIX: Save the file FIRST
            if ($this->uploadFile->saveAs($filePath)) {
                // Store the file information
                $this->filename = $filename;
                $this->original_filename = $this->uploadFile->name;
                $this->file_path = $relativePath;
                $this->file_type = $this->uploadFile->type;
                $this->file_size = $this->uploadFile->size;

                // ✅ IMPORTANT: Clear the uploadFile property to prevent double processing
                $this->uploadFile = null;

                Yii::info("File saved successfully: {$filePath}", __METHOD__);
                return true;
            } else {
                Yii::error("Failed to save file: {$filePath}", __METHOD__);
                return false;
            }
        } catch (\Exception $e) {
            Yii::error("Error saving attachment file: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Delete file from filesystem before deleting record
     *
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        try {
            $fullPath = Yii::getAlias('@webroot' . $this->file_path);
            if (file_exists($fullPath) && is_file($fullPath)) {
                unlink($fullPath);
                Yii::info("Deleted attachment file: {$fullPath}", __METHOD__);
            }
        } catch (\Exception $e) {
            Yii::error("Error deleting attachment file: " . $e->getMessage(), __METHOD__);
        }

        return true;
    }

    /**
     * Soft delete the attachment
     *
     * @return bool
     */
    public function softDelete()
    {
        $this->deleted_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Check if attachment is deleted
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }
}
