<?php
use yii\bootstrap4\Nav;
use mdm\admin\components\MenuHelper;
use app\components\UserHelper; 

$rol = UserHelper::getMyRol();
$clinica = "";
if($rol == "Administrador-clinica"){
    $clinica = UserHelper::getMyClinicaName();
}
?>

<style>
/* Custom submenu styling */
.nav-sidebar .nav-item .nav-link {
    color: white !important;
}

.nav-sidebar .nav-item .nav-link:hover {
    background-color: #00E3E2 !important;
    color: #041E3F !important;
}

.nav-sidebar .nav-item .nav-link.active {
    background-color: #00E3E2 !important;
    color: #041E3F !important;
}

/* All submenu items - normal state */
.nav-sidebar .nav-treeview .nav-link,
.nav-sidebar .nav-treeview .nav-item .nav-link,
.nav-sidebar .has-treeview .nav-treeview .nav-link {
    background-color: #00E3E2 !important;
    color: #041E3F !important;
}

/* Submenu hover state - with maximum specificity */
.nav-sidebar .nav-treeview .nav-link:hover,
.nav-sidebar .nav-treeview .nav-item .nav-link:hover,
.nav-sidebar .has-treeview .nav-treeview .nav-link:hover,
.nav-sidebar .nav-treeview .nav-link:hover:not(.active),
.nav-sidebar .nav-treeview .nav-item .nav-link:hover:not(.active),
.nav-sidebar .has-treeview .nav-treeview .nav-link:hover:not(.active) {
    background-color: #13EAB1 !important;
    color: white !important;
}

/* Force white text color on hover for all submenu items - maximum specificity */
.nav-sidebar .nav-treeview .nav-link:hover *,
.nav-sidebar .nav-treeview .nav-item .nav-link:hover *,
.nav-sidebar .has-treeview .nav-treeview .nav-link:hover *,
.nav-sidebar .nav-treeview .nav-link:hover span,
.nav-sidebar .nav-treeview .nav-item .nav-link:hover span,
.nav-sidebar .has-treeview .nav-treeview .nav-link:hover span,
.nav-sidebar .nav-treeview .nav-link:hover .menu-text,
.nav-sidebar .nav-treeview .nav-item .nav-link:hover .menu-text,
.nav-sidebar .has-treeview .nav-treeview .nav-link:hover .menu-text {
    color: white !important;
}

.nav-sidebar .nav-treeview .nav-link.active,
.nav-sidebar .nav-treeview .nav-item .nav-link.active,
.nav-sidebar .has-treeview .nav-treeview .nav-link.active,
.nav-sidebar .nav-treeview .nav-link.active:hover,
.nav-sidebar .nav-treeview .nav-item .nav-link.active:hover,
.nav-sidebar .has-treeview .nav-treeview .nav-link.active:hover {
    background-color: #13EAB1 !important;
    color: #041E3F !important;
}

/* Force dark navy text color on active state for all submenu items */
.nav-sidebar .nav-treeview .nav-link.active *,
.nav-sidebar .nav-treeview .nav-item .nav-link.active *,
.nav-sidebar .has-treeview .nav-treeview .nav-link.active *,
.nav-sidebar .nav-treeview .nav-link.active span,
.nav-sidebar .nav-treeview .nav-item .nav-link.active span,
.nav-sidebar .has-treeview .nav-treeview .nav-link.active span {
    color: #041E3F !important;
}

/* Force override for active state only */
.nav-sidebar .nav-treeview .nav-link.active,
.nav-sidebar .nav-treeview .nav-item .nav-link.active,
.nav-sidebar .has-treeview .nav-treeview .nav-link.active {
    background-color: #13EAB1 !important;
    color: #041E3F !important;
}

/* Additional specificity for submenu items */
.nav-sidebar .nav-treeview .nav-item .nav-link,
.nav-sidebar .has-treeview .nav-treeview .nav-item .nav-link {
    background-color: #00E3E2 !important;
    color: #041E3F !important;
}

/* Ultimate hover state override - ensure white text */
.nav-sidebar .nav-treeview .nav-link:hover,
.nav-sidebar .nav-treeview .nav-item .nav-link:hover,
.nav-sidebar .has-treeview .nav-treeview .nav-link:hover {
    background-color: #13EAB1 !important;
    color: white !important;
}

.nav-sidebar .nav-treeview .nav-link:hover .menu-text,
.nav-sidebar .nav-treeview .nav-item .nav-link:hover .menu-text,
.nav-sidebar .has-treeview .nav-treeview .nav-link:hover .menu-text {
    color: white !important;
}
</style>
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="border-bottom: none; background-color: #041E3F !important; color: white !important;">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link d-flex justify-content-center" style="padding-top: 0; padding-bottom: 0; border-bottom: none;">
        <img src="<?= Yii::getAlias('@web/img/sispsa-12-62.png')?>" alt="Logo"  style="opacity: 1; margin: 15px auto; max-width: 250px; width: auto; height: auto; object-fit: contain;">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel d-flex flex-column align-items-center">
            <div class="image mb-2">
                <img src="<?= Yii::getAlias('@web/img/Fondo SISPSA.png')?>" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info text-center">
                <p><b style="color: white !important;"><?= $clinica ?></b></p>
                <p><b style="color: white !important;"><?= $rol ?></b></p><br>
                <b><a href="#" class="d-block" style="color: white !important;"><?= Yii::$app->user->identity->username ?? 'Usuario' ?></a></b>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            $callback = function($menu) {
                $data = [];
                $icon = 'fas fa-circle'; // Icono por defecto
                
                if (!empty($menu['data'])) {
                    // Si data es un recurso, convertirlo a string
                    $menuData = is_resource($menu['data']) ? stream_get_contents($menu['data']) : $menu['data'];
                    // Intentar decodificar como JSON
                    if ($menuData !== false) {
                        $decoded = @json_decode($menuData, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $data = $decoded;
                            // Extraer el icono si existe
                            if (isset($decoded['icon'])) {
                                $icon = $decoded['icon'];
                            }
                        }
                    }
                }
                
                $menuItem = [
                    'label' => '<span class="menu-text">' . $menu['name'] . '</span>',
                    'url' => [$menu['route']],
                    'options' => $data,
                ];
                
                // Agregar icono al menú
                if (!empty($menu['children'])) {
                    // Si tiene hijos, es un menú desplegable
                    $menuItem['icon'] = $icon;
                    $menuItem['items'] = $menu['children'];
                    $menuItem['options']['class'] = 'nav-item has-treeview';
                    $menuItem['linkOptions'] = ['class' => 'nav-link'];
                } else {
                    // Si no tiene hijos, es un enlace simple
                    $menuItem['icon'] = $icon;
                    $menuItem['options']['class'] = 'nav-item';
                    $menuItem['linkOptions'] = ['class' => 'nav-link'];
                }
                
                return $menuItem;
            };
            
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback),
                'options' => ['class' => 'nav nav-pills nav-sidebar flex-column', 'data-widget' => 'treeview'],
                'encodeLabels' => false,
                'activateParents' => true
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
