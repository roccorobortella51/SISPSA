<?php
/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Bienvenido a SISPSA';
?>

<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --light-color: #ecf0f1;
        --dark-color: #2c3e50;
    }
    
    body {
        font-family: 'Open Sans', sans-serif;
        color: #333;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        line-height: 1.6;
    }
    
    h1, h2, h3, h4, h5, h6 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        margin-top: 0;
    }
    
    /* Hero Section */
    .hero-section {
        position: relative;
        height: 100vh;
        min-height: 600px;
        background: linear-gradient(rgba(44, 62, 80, 0.85), rgba(44, 62, 80, 0.85)), 
                    url('<?= Yii::getAlias('@web/img/medical-bg.jpg') ?>') no-repeat center center/cover;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
    }
    
    .hero-content {
        padding: 0 15px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .logo-sipsa {
        max-width: 250px;
        height: auto;
        margin-bottom: 2rem;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }
    
    .main-title {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .subtitle-paragraph {
        font-size: 1.5rem;
        max-width: 700px;
        margin: 0 auto 2.5rem;
        line-height: 1.6;
    }
    
    .btn-cta {
        background-color: var(--accent-color);
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-cta:hover {
        background-color: #c0392b;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        color: white;
    }
    
    /* Info Section */
    .info-section {
        background-color: white;
    }
    
    .section-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .info-section-title {
        text-align: center;
        margin-bottom: 3rem;
        color: var(--primary-color);
        position: relative;
        padding-bottom: 15px;
    }
    
    .info-section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background-color: var(--secondary-color);
    }
    
    .card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: none;
        margin-bottom: 2rem;
        height: 100%;
    }
    
    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .card-icon {
        background-color: var(--secondary-color);
        color: white;
        font-size: 2.5rem;
        padding: 1.5rem 0;
        text-align: center;
    }
    
    .card-content {
        padding: 1.8rem;
        text-align: center;
    }
    
    .card-content h3 {
        color: var(--primary-color);
        margin-bottom: 1.2rem;
    }
    
    /* Animations */
    .animate {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease-out;
    }
    
    .animate.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .main-title {
            font-size: 3rem;
        }
        
        .subtitle-paragraph {
            font-size: 1.3rem;
        }
    }
    
    @media (max-width: 768px) {
        .main-title {
            font-size: 2.5rem;
        }
        
        .subtitle-paragraph {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .logo-sipsa {
            max-width: 200px;
        }
        
        .hero-section {
            min-height: 500px;
            height: auto;
            padding: 100px 0;
        }
        
        .info-section {
            padding: 3rem 0;
        }
    }
    
    @media (max-width: 576px) {
        .main-title {
            font-size: 2rem;
        }
        
        .card-content {
            padding: 1.2rem;
        }
    }
</style>

<!-- Hero Section -->


<!-- Information/Features Section -->
<section class="info-section" id="features">

    <div class="hero-content" align="center">
        <img src="<?= Yii::getAlias('@web/img/sispsa.png') ?>" class="logo-sipsa animate" alt="Logo SISPSA">
        <h1 class="main-title animate" style="transition-delay: 0.1s">¡Test3 Bienvenido a SISPSA!</h1>
        <p class="subtitle-paragraph animate" style="transition-delay: 0.2s">Tu viaje en el Sistema Integral de Salud comienza ahora. Explora todas las funcionalidades diseñadas para tu bienestar.</p>
    </div>


    <div class="emergency-info-section">
        <div class="container text-center">
            <h2 class="emergency-title animate">Emergencia 🚑</h2>
            <h3 class="emergency-subtitle animate">Sin plazos de espera.</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card emergency-card animate">
                        <div class="card-content">
                            <h4>Beneficios Únicos</h4>
                            <ul class="emergency-benefits-list">
                                <li><strong>Atención Primaria de Emergencia.</strong></li>
                                <li><strong>1 Hematología</strong></li>
                                <li><strong>1 Glicemia</strong></li>
                                <li><strong>1 Rayos X</strong> a una proyección</li>
                                <li><strong>Aplicación de un medicamento analgésico</strong> según criterio médico</li>
                                <li><strong>1 ecograma</strong> según criterio médico</li>
                                <li><strong>Uso de emergencia</strong></li>
                                <li><strong>Sala de cura menor</strong></li>
                                <li><strong>Terapia Respiratoria</strong></li>
                                <li><strong>No cubre preexistencia.</strong></li>
                                <li><strong>Hospitalización por 48Hrs</strong></li>
                                <li><strong>Cirugías por Apendicitis</strong></li>
                                <li><strong>Inmovilización por accidentes de miembros superior e interiores. Excluidos accidentes cráneocefalicos</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <p class="emergency-note animate mt-4">
                <strong>NOTA:</strong> En caso de que el paciente requiera otro beneficio no estipulado o una cantidad no estipulada en el contrato, sale por cuenta del usuario (paciente). Por favor notificar previamente antes de aplicar cualquier otro beneficio. Que no este en el plan. (el usuario ya está en conocimiento de los beneficios al cual accede, sin embargo, notificar de igual forma), ya que estos deben ser pagados a la clínica directamente, por parte del afiliado, <strong>SISPSA no se hará responsable</strong>, en caso de que esto ocurra.
            </p>
        </div>
    </div>
    ---
    
    <div class="section-container">
        <h2 class="info-section-title animate">Explora Nuestras Soluciones</h2>
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card animate" style="transition-delay: 0.1s">
                    <div class="card-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="card-content">
                        <h3>Misión</h3>
                        <p>Brindar soluciones de salud y bienestar de medicina prepagada con una excelente relación precio/calidad.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card animate" style="transition-delay: 0.2s">
                    <div class="card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="card-content">
                        <h3>Visión</h3>
                        <p>Liderar el mercado nacional ofreciendo una amplia gama de planes de salud, usando tecnología para brindar servicios médicos con excelente atención.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card animate" style="transition-delay: 0.3s">
                    <div class="card-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="card-content">
                        <h3>Valores</h3>
                        <p>Responsabilidad, Respeto, Empatía, Solidaridad, Compromiso, Calidad, Calidez.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card animate" style="transition-delay: 0.4s">
                    <div class="card-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="card-content">
                        <h3>Propósito</h3>
                        <p>Ofrecer salud al alcance de todos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
// Animación al hacer scroll mejorada
document.addEventListener('DOMContentLoaded', function() {
    // Observador de intersección para las animaciones
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1
    });
    
    // Observar todos los elementos con clase animate
    document.querySelectorAll('.animate').forEach(el => {
        observer.observe(el);
    });
    
    // Smooth scroll para el botón
    document.querySelector('a[href^="#"]').addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            window.scrollTo({
                top: target.offsetTop - 20,
                behavior: 'smooth'
            });
        }
    });
});
</script>
