<?php

namespace app\models;

use mdm\admin\models\Menu;

/**
 * Extended Menu model to handle icon and visibility from JSON data
 */
class MenuExtended extends Menu
{
    public $parent_name;

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        
        // Convertir el recurso a string si es necesario
        if (is_resource($this->data)) {
            $this->data = stream_get_contents($this->data);
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Asegurar que data sea una cadena JSON válida
        if (is_resource($this->data)) {
            $content = stream_get_contents($this->data);
            if ($content !== false) {
                $this->data = $content;
            } else {
                $this->setDataFromParams();
            }
        } elseif (empty($this->data) || !is_string($this->data)) {
            $this->setDataFromParams();
        } else {
            // Validar que sea JSON válido
            $decoded = @json_decode($this->data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->setDataFromParams();
            }
        }

        return true;
    }

    /**
     * Get icon from JSON data field
     * @return string
     */
    public function getIconFromData()
    {
        if (!empty($this->data)) {
            $menuData = is_resource($this->data) ? stream_get_contents($this->data) : $this->data;
            if ($menuData !== false) {
                $decoded = @json_decode($menuData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['icon'])) {
                    return $decoded['icon'];
                }
            }
        }
        return 'fas fa-circle'; // Icono por defecto
    }

    /**
     * Get visibility from JSON data field
     * @return boolean
     */
    public function getVisibleFromData()
    {
        if (!empty($this->data)) {
            $menuData = is_resource($this->data) ? stream_get_contents($this->data) : $this->data;
            if ($menuData !== false) {
                $decoded = @json_decode($menuData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['visible'])) {
                    return (bool)$decoded['visible'];
                }
            }
        }
        return true; // Visible por defecto
    }

    /**
     * Set icon and visibility to JSON data field
     * @param string $icon
     * @param boolean $visible
     */
    public function setDataFromParams($icon = null, $visible = true)
    {
        $data = [
            'icon' => $icon ?: 'fas fa-circle',
            'visible' => (bool)$visible
        ];
        $this->data = json_encode($data);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['parent_name'], 'string'];
        return $rules;
    }
}
