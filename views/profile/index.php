<?php
// views/profile/index.php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Mi Perfil';
$this->params['breadcrumbs'][] = $this->title;

// Get user photo URL
$photoUrl = !empty($userDatos->selfie) ? Yii::getAlias('@web/' . $userDatos->selfie) . '?v=' . time() : null;
$userInitials = 'U';
$displayName = 'Usuario';
$userRole = 'Sin rol asignado';

if (!empty($userDatos->nombres)) {
    $displayName = trim($userDatos->nombres . ' ' . ($userDatos->apellidos ?? ''));
    $nameParts = explode(' ', $displayName);
    if (count($nameParts) >= 2) {
        $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
    } else {
        $userInitials = strtoupper(substr($userDatos->nombres, 0, 2));
    }
}

if (!empty($userDatos->role)) {
    $userRole = $userDatos->role;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden;">

                <div style="background: linear-gradient(135deg, #0F6CBD 0%, #106EBE 100%); padding: 30px;">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div style="position: relative; display: inline-block;">
                                <?php if ($photoUrl && file_exists(Yii::getAlias('@webroot/' . $userDatos->selfie))): ?>
                                    <img src="<?= $photoUrl ?>"
                                        style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid white; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; color: #0F6CBD;">
                                        <?= Html::encode($userInitials) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col">
                            <h2 style="color: white; margin: 0 0 5px 0; font-size: 24px;"><?= Html::encode($displayName) ?></h2>
                            <span style="background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 15px; color: white; font-size: 12px;">
                                <?= Html::encode($userRole) ?>
                            </span>
                        </div>
                        <div class="col-auto">
                            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update'], ['class' => 'btn btn-light', 'style' => 'border-radius: 8px;']) ?>
                        </div>
                    </div>
                </div>

                <div style="padding: 25px;">

                    <h4 style="color: #0F6CBD; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0;">
                        <i class="fas fa-user"></i> Información Personal
                    </h4>
                    <div class="row" style="margin-bottom: 30px;">
                        <div class="col-md-6">
                            <label style="color: #6c757d; font-size: 12px;">Nombres</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->nombres ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label style="color: #6c757d; font-size: 12px;">Apellidos</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->apellidos ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label style="color: #6c757d; font-size: 12px;">Tipo Cédula</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->tipo_cedula ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label style="color: #6c757d; font-size: 12px;">Cédula</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->cedula ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label style="color: #6c757d; font-size: 12px;">Fecha Nacimiento</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->fechanac ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label style="color: #6c757d; font-size: 12px;">Sexo</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->sexo ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label style="color: #6c757d; font-size: 12px;">Tipo Sangre</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->tipo_sangre ?? 'No especificado') ?></div>
                        </div>
                    </div>

                    <h4 style="color: #0F6CBD; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0;">
                        <i class="fas fa-address-card"></i> Información de Contacto
                    </h4>
                    <div class="row" style="margin-bottom: 30px;">
                        <div class="col-md-6">
                            <label style="color: #6c757d; font-size: 12px;">Correo Electrónico</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->email ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label style="color: #6c757d; font-size: 12px;">Teléfono</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->telefono ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-12">
                            <label style="color: #6c757d; font-size: 12px;">Dirección</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->direccion ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-3">
                            <label style="color: #6c757d; font-size: 12px;">Estado</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($estadoNombre ?? $userDatos->estado ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-3">
                            <label style="color: #6c757d; font-size: 12px;">Ciudad</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($ciudadNombre ?? $userDatos->ciudad ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-3">
                            <label style="color: #6c757d; font-size: 12px;">Municipio</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($municipioNombre ?? $userDatos->municipio ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-3">
                            <label style="color: #6c757d; font-size: 12px;">Parroquia</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($parroquiaNombre ?? $userDatos->parroquia ?? 'No especificado') ?></div>
                        </div>
                    </div>

                    <h4 style="color: #0F6CBD; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0;">
                        <i class="fas fa-lock"></i> Seguridad
                    </h4>
                    <div class="row">
                        <div class="col-md-6">
                            <label style="color: #6c757d; font-size: 12px;">Correo Electrónico</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->email ?? 'No especificado') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label style="color: #6c757d; font-size: 12px;">Fecha de Registro</label>
                            <div style="font-size: 16px; font-weight: 500;"><?= Html::encode($userDatos->created_at ? Yii::$app->formatter->asDate($userDatos->created_at, 'dd/MM/yyyy') : 'No especificado') ?></div>
                        </div>
                    </div>

                    <div class="alert alert-info" style="margin-top: 20px;">
                        <i class="fas fa-info-circle"></i> Para cambiar tu contraseña, contacta al administrador del sistema.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>