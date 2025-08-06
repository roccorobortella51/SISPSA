<?php
/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

// Establece el título de la página
$this->title = 'Bienvenido a SISPSA Clínicas';
?>

<!-- Contenedor principal para la vista de bienvenida, usado para escopar el CSS -->
<div id="welcome-page-container">
    <style>
        /* Definición de variables CSS para los colores del proyecto */
        /* Estas variables se usarán localmente en esta vista */
        #welcome-page-container :root {
            --sipsa-blue-primary: #2563eb;
            --sipsa-blue-dark: #1d4ed8;
            --sipsa-indigo-accent: #4f46e5;
            --sipsa-red: #dc2626;
            --sipsa-gray-secondary: #e5e7eb;
            --sipsa-gray-dark: #374151; /* Darker gray for borders */
            --sipsa-gray-text-muted: #a0aec0;
            --sipsa-gray-text-active: #64748b;
            --sipsa-gray-border: #e2e8f0;
            --sipsa-gray-bg-light: #f0f2f5;
            --sipsa-gray-bg-panel: #fcfcfd;
            --sipsa-panel-bg: #ffffff;
            --sipsa-text-heading: #1a202c;
            --sipsa-text-section-title: #2d3748;
            --sipsa-text-paragraph: #4a5568;
            --sipsa-green-active: #065f46;
            --sipsa-green-bg: #d1fae5;
            --sipsa-red-inactive: #991b1b;
            --sipsa-red-bg: #fee2e2;
            --sipsa-yellow: #eab308;
            --sipsa-teal: #14b8a6;
            --sipsa-cyan: #06b6d4;
            --sipsa-pink: #ec4899;
            --sipsa-white: #ffffff; /* Added for footer hover */

            /* Soft colors for the main background */
            --sipsa-hero-bg-start: #E0ECFF; /* Very light blue */
            --sipsa-hero-bg-end: #F0F4F8;   /* Very light grayish blue */
        }

        /* RESET GLOBAL: Asegura que html y body no tengan márgenes/rellenos por defecto */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
            width: 100%;
            overflow-x: hidden; /* Evita scroll horizontal en toda la página */
            display: flex; /* Hace que el body sea un contenedor flex para el layout principal */
            flex-direction: column; /* Apila los elementos principales verticalmente */
        }

        /* Contenedor principal de la vista de bienvenida */
        #welcome-page-container {
            width: 100%;
            height: 100%; /* Ocupa la altura disponible dentro del body */
            margin: 0 !important; /* Asegura que no tenga margen superior */
            padding: 0 !important; /* Asegura que no tenga relleno superior */
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            flex-grow: 1; /* Permite que este contenedor crezca y ocupe el espacio disponible */
        }

        /* Estilos generales para el body dentro del contenedor */
        #welcome-page-container body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--sipsa-gray-bg-light);
        }

        /* Elimina márgenes y rellenos superiores de elementos comunes del layout de Yii */
        #welcome-page-container main, 
        #welcome-page-container .container, 
        #welcome-page-container .wrap, 
        #welcome-page-container #w0, 
        #welcome-page-container #app, 
        #welcome-page-container #page-wrapper, 
        #welcome-page-container .content-wrapper, 
        #welcome-page-container .wrapper, 
        #welcome-page-container .main-header, 
        #welcome-page-container .navbar {
            padding-top: 0 !important;
            margin-top: 0 !important;
            min-height: 0 !important; /* Asegura que no haya altura mínima */
            height: auto !important; /* Permite que la altura se ajuste al contenido */
        }

        /* Regla específica y agresiva para .content-header */
        #welcome-page-container .content-header {
            padding-top: 0 !important;
            margin-top: 0 !important;
            height: 0 !important; /* Fuerza la altura a 0 */
            min-height: 0 !important; /* Asegura que no haya altura mínima */
            line-height: 0 !important; /* Colapsa la altura de línea */
            overflow: hidden !important; /* Oculta cualquier contenido si se colapsa */
        }

        #welcome-page-container .content-wrapper > .content {
            padding-top: 0 !important;
        }

        /* Hero Section - Main welcome background */
        #welcome-page-container .hero-section {
            position: relative;
            height: 60vh; /* Aumentado de 45vh a 60vh */
            min-height: 300px; /* Aumentado de 300px a 400px */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            background: linear-gradient(135deg, var(--sipsa-hero-bg-start) 0%, var(--sipsa-hero-bg-end) 100%);
            margin-top: 0 !important; /* Asegura que el hero no tenga margen superior */
        }
        @media (max-width: 767px) { /* Mobile */
            #welcome-page-container .hero-section {
                height: 60vh; /* Ajustado para móviles también */
            }
        }


        /* Overlay layer for readability */
        #welcome-page-container .hero-overlay {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.1);
            z-index: 0;
        }

        /* Central content panel in the welcome section */
        #welcome-page-container .welcome-content-panel {
            position: relative;
            z-index: 10;
            background-color: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 1.5rem; /* Panel padding */
            max-width: 960px;
            margin-left: auto;
            margin-right: auto;
            transition: all 0.7s ease-out;
        }
        @media (min-width: 768px) { /* md breakpoint */
            #welcome-page-container .welcome-content-panel {
                padding: 2rem; /* Panel padding on desktop */
            }
        }

        /* Styles for SISPSA logo */
        #welcome-page-container .logo-sipsa {
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 12rem; /* Logo size */
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 3px rgba(0,0,0,0.07)) drop-shadow(0 2px 2px rgba(0,0,0,0.06));
            transition: transform 0.5s ease-in-out;
            margin-bottom: 1.5rem; /* Logo bottom margin */
        }
        #welcome-page-container .logo-sipsa:hover {
            transform: scale(1.05);
        }
        @media (min-width: 768px) { /* md breakpoint */
            #welcome-page-container .logo-sipsa {
                width: 16rem; /* Logo size on desktop */
            }
        }

        /* Styles for main title */
        #welcome-page-container .main-title {
            font-size: 1.75rem; /* Title font size */
            font-weight: 800;
            color: var(--sipsa-text-heading);
            margin-bottom: 0.75rem; /* Title bottom margin */
            line-height: 1.25;
        }
        @media (min-width: 768px) { /* md breakpoint */
            #welcome-page-container .main-title {
                font-size: 2.75rem; /* Font size on desktop */
            }
        }

        /* Styles for subtitle/paragraph */
        #welcome-page-container .subtitle-paragraph {
            font-size: 1rem; /* Subtitle font size */
            color: var(--sipsa-text-paragraph);
            margin-bottom: 0;
            max-width: 42rem;
            margin-left: auto;
            margin-right: auto;
        }
        @media (min-width: 768px) { /* md breakpoint */
            #welcome-page-container .subtitle-paragraph {
                font-size: 1.125rem; /* Font size on desktop */
            }
        }

        /* Information/Features Section */
        #welcome-page-container .info-section {
            padding-top: 2rem; /* Reducido de 2.5rem a 2rem */
            padding-bottom: 2.5rem; /* Bottom padding */
            background-color: var(--sipsa-gray-bg-light); /* Changed to bg-light for contrast */
            flex-grow: 1; /* Allows this section to take up remaining space */
            display: flex; /* To center content vertically if there's space */
            align-items: center; /* Vertically centers */
        }

        #welcome-page-container .info-section-container {
            max-width: 1152px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            text-align: center;
        }

        #welcome-page-container .info-section-title {
            font-size: 1.5rem; /* Section title font size */
            font-weight: 800;
            color: var(--sipsa-blue-primary);
            margin-bottom: 2rem; /* Section title bottom margin */
        }
        @media (min-width: 768px) { /* md breakpoint */
            #welcome-page-container .info-section-title {
                font-size: 1.875rem; /* Font size on desktop */
            }
        }

        /* IMPORTANT: Force horizontal display with flexbox */
        /* Updated to use .card-container for the grid */
        #welcome-page-container .card-container {
            display: flex; /* Use flexbox */
            flex-wrap: nowrap; /* Prevent wrapping onto new lines */
            gap: 1.5rem; /* Slightly more space between cards */
            overflow-x: auto; /* Allow horizontal scrolling if cards overflow */
            padding-bottom: 0.5rem; /* Add some padding for scrollbar visibility */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            justify-content: center; /* Center cards when there's enough space */
            align-items: stretch; /* Ensure all cards stretch to the same height */
        }

        /* Styles for information cards - Updated to .card */
        #welcome-page-container .card {
            background-color: var(--sipsa-panel-bg); /* White */
            border-radius: 0.75rem; /* rounded-xl */
            /* Increased shadow for a much darker and more pronounced effect */
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.4), 0 4px 8px -2px rgba(0, 0, 0, 0.2); 
            padding: 1.5rem; /* Card padding */
            border: 2px solid var(--sipsa-gray-dark); /* Darker and thicker border */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            text-align: center;
            display: flex; /* Use flexbox to align content vertically */
            flex-direction: column;
            align-items: center; /* Horizontally center internal elements */
            justify-content: flex-start; /* Vertically align to top */
            
            /* IMPORTANT: Set a fixed width or min-width for each card */
            /* Increased min-width for wider cards */
            min-width: 250px; /* Minimum width for each card (increased from 200px) */
            flex-shrink: 0; /* Prevent cards from shrinking below their content size */
            flex-grow: 1; /* Allow cards to grow if there's extra space, but respect min-width */
            flex-basis: 0; /* Distribute space based on flex-grow */
        }
        #welcome-page-container .card:hover {
            transform: translateY(-4px);
            /* Slightly more pronounced shadow on hover */
            box-shadow: 0 15px 30px -7px rgba(0, 0, 0, 0.5), 0 6px 12px -3px rgba(0, 0, 0, 0.3); 
        }

        /* Styles for card icons - Updated to .card-icon */
        #welcome-page-container .card-icon {
            color: var(--sipsa-blue-primary); /* Icon color, similar to the image */
            margin-bottom: 1rem; /* More space below the icon */
            display: flex; /* To center the SVG */
            justify-content: center;
            align-items: center;
            width: 3.5rem; /* Icon container size */
            height: 3.5rem;
        }

        /* Styles for Font Awesome icons */
        #welcome-page-container .card-icon .fas {
            font-size: 2.5rem; /* Adjust size for Font Awesome icons */
            line-height: 1; /* Ensure proper vertical alignment */
        }

        /* Styles for card titles - Updated to .card-content h3 */
        #welcome-page-container .card-content h3 {
            font-size: 1.05rem; /* text-lg */
            font-weight: 700; /* font-bold */
            color: var(--sipsa-text-section-title);
            margin-bottom: 0.5rem; /* Card title bottom margin */
            line-height: 1.4; /* Line spacing for titles */
        }

        /* Styles for card paragraphs - Updated to .card-content p */
        #welcome-page-container .card-content p {
            color: var(--sipsa-text-paragraph);
            font-size: 0.85rem; /* Slightly smaller for descriptions */
            line-height: 1.5; /* Better readability */
            flex-grow: 1; /* Allows the paragraph to take up remaining space */
            margin-bottom: 0; /* Remove default bottom margin for paragraphs in cards */
        }

        /* Footer */
        #welcome-page-container .main-footer {
            background-color: var(--sipsa-blue-primary);
            color: var(--sipsa-gray-text-muted);
            padding-top: 1.5rem; /* Top padding */
            padding-bottom: 1.5rem; /* Bottom padding */
            text-align: center;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            margin-top: auto; /* Pushes footer to the bottom */
        }

        #welcome-page-container .footer-content {
            max-width: 1152px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        #welcome-page-container .footer-links {
            margin-top: 0.75rem; /* Top margin */
            font-size: 0.8rem; /* Font size */
        }

        #welcome-page-container .footer-links a {
            color: inherit;
            text-decoration: none;
            margin-left: 0.5rem;
            margin-right: 0.5rem;
            transition: color 0.2s ease-in-out;
        }
        #welcome-page-container .footer-links a:hover {
            color: var(--sipsa-white);
        }

        /* Font Awesome CDN link - IMPORTANT: This needs to be in a <link> tag in <head> for production */
        /* For this example, it's placed here for immediate visibility within the style block. */
        /* In a real Yii app, you'd add this to your main layout file or register it as an asset. */
        @import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css");
    </style>

    <!-- Main Section (Hero) -->
    <section class="hero-section">
        <!-- Overlay layer for text and logo readability -->
        <div class="hero-overlay"></div>

        <!-- Centered main content -->
        <div class="welcome-content-panel">
            
            <!-- SISPSA Logo -->
            <div class="mb-8">
                <img src="<?= Yii::getAlias('@web/img/sispsa.png')?>" class="logo-sipsa" alt="Logo SISPSA">
            </div>

            <h1 class="main-title">
                ¡Bienvenido a SISPSA!
            </h1>
            <p class="subtitle-paragraph">
                Tu viaje en el Sistema Integral de Salud comienza ahora. Explora todas las funcionalidades.
            </p>
            <!-- No buttons here, just the welcome message -->
        </div>
    </section>

    <!-- Information/Features Section -->
    <section class="info-section">
        <div class="info-section-container">
            <h2 class="info-section-title">Explora Nuestras Soluciones</h2>
            <div class="card-container">
                <!-- Card 1: Misión -->
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-bullseye"></i> <!-- Icono de diana para Misión -->
                    </div>
                    <div class="card-content">
                        <h3>Misión</h3>
                        <p>Brindar soluciones de salud y bienestar de medicina prepagada con una excelente relación precio/calidad.</p>
                    </div>
                </div>

                <!-- Card 2: Visión -->
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-eye"></i> <!-- Icono de ojo para Visión -->
                    </div>
                    <div class="card-content">
                        <h3>Visión</h3>
                        <p>Liderar el mercado nacional ofreciendo una amplia gama de planes de salud de medicina prepagada, usando la tecnología para brindar servicios médicos con una excelente atención a nuestros afiliados.</p>
                    </div>
                </div>

                <!-- Card 3: Valores -->
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-handshake"></i> <!-- Icono de apretón de manos para Valores -->
                    </div>
                    <div class="card-content">
                        <h3>Valores</h3>
                        <p>Responsabilidad, Respeto, Empatía, Solidaridad, Compromiso, Calidad, Calidez.</p>
                    </div>
                </div>

                <!-- Card 4: Propósito -->
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-lightbulb"></i> <!-- Icono de bombilla para Propósito -->
                    </div>
                    <div class="card-content">
                        <h3>Propósito</h3>
                        <p>Ofrecer salud al alcance de todos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

   
</div>
