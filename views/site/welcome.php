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
                <img src="<?= Yii::getAlias('@web') ?>/images/Captura desde 2025-08-05 23-28-46.png"
                     alt="Logo SISPSA"
                     class="logo-sipsa"
                     onerror="this.onerror=null;this.src='https://placehold.co/300x100/2563EB/FFFFFF?text=Logo+SISPSA';"
                >
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
                    <h3>Gestión de Informacion</h3>
                    <p>Accede y administra.</p>
                </div>
                <!-- Característica 2 -->
                <div class="info-card">
                    <div class="icon-container">
                        <span class="icon icon-chart-line"></span> <!-- Icono de ejemplo -->
                    </div>
                    <h3>Reportes y Estadísticas</h3>
                    <p>Obtén información valiosa.</p>
                </div>
                <!-- Característica 3 -->
                <div class="info-card">
                    <div class="icon-container">
                        <span class="icon icon-user-md"></span> <!-- Icono de ejemplo -->
                    </div>
                    <h3>Administración de Personal</h3>
                    <p>Gestiona eficientemente a tu equipo administrativo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> SISPSA. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="#">Política de Privacidad</a> |
                <a href="#">Términos de Servicio</a>
            </div>
        </div>
    </footer>
