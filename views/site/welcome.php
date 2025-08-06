<?php
/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url; // Aunque no se usen botones, Url::to() sigue siendo útil para enlaces de navegación si se añaden más tarde

// Establece el título de la página (esto aún es necesario para Yii2)
$this->title = 'Bienvenido a SISPSA Clínicas';
?>

    <!-- Sección Principal (Hero) -->
    <section class="hero-section">
        <!-- Capa de superposición para la legibilidad del texto y el logo -->
        <div class="hero-overlay"></div>

        <!-- Contenido principal centrado -->
        <div class="welcome-content-panel">
            
            <!-- Logo SISPSA -->
            <div class="mb-8">
                <img src="<?= Yii::getAlias('@web/img/sispsa.png')?>" class="img-circle elevation-2" alt="User Image">
            </div>

            <h1 class="main-title">
                ¡Bienvenido a SISPSA!
            </h1>
            <p class="subtitle-paragraph">
                Tu viaje en el Sistema Integral de Salud comienza ahora. Explora todas las funcionalidades.
            </p>
            <!-- No hay botones aquí, solo el mensaje de bienvenida -->
        </div>
    </section>

    <!-- Sección de Información/Características -->
    <section class="info-section">
        <div class="info-section-container">
            <h2 class="info-section-title">Explora Nuestras Soluciones</h2>
            <div class="info-cards-grid">
                <!-- Característica 1 -->
                <div class="info-card">
                    <div class="icon-container">
                        <span class="icon icon-stethoscope"></span> <!-- Icono de ejemplo -->
                    </div>
                    <h3 class="text-center">Gestión de Expedientes</h3>
                    <p>Accede y administra de forma segura los historiales clínicos de tus pacientes.</p>
                </div>
                <!-- Característica 2 -->
                <div class="info-card">
                    <div class="icon-container">
                        <span class="icon icon-chart-line"></span> <!-- Icono de ejemplo -->
                    </div>
                    <h3>Reportes y Estadísticas</h3>
                    <p>Obtén información valiosa para optimizar la operación de tu clínica.</p>
                </div>
                <!-- Característica 3 -->
                <div class="info-card">
                    <div class="icon-container">
                        <span class="icon icon-user-md"></span> <!-- Icono de ejemplo -->
                    </div>
                    <h3>Administración de Personal</h3>
                    <p>Gestiona eficientemente a tu equipo médico y administrativo.</p>
                </div>
            </div>
        </div>
    </section>

   
