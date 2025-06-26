<?php
use yii\bootstrap4\Nav;
use mdm\admin\components\MenuHelper;
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="<?=$assetDir?>/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?=$assetDir?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Alexander Pierce</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <!-- href be escaped -->
        <!-- <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div> -->

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            /*echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'icon' => 'tachometer-alt',
                        'url' => ['/site/index']
                    ],
                    ['label' => 'Administración', 'header' => true],
                    [
                        'label' => 'Gestión de Usuarios',
                        'icon' => 'users',
                        'items' => [
                            ['label' => 'Usuarios', 'url' => ['/admin/user'], 'iconStyle' => 'far fa-circle'],
                            ['label' => 'Roles', 'url' => ['/admin/role'], 'iconStyle' => 'far fa-circle'],
                            ['label' => 'Permisos', 'url' => ['/admin/permission'], 'iconStyle' => 'far fa-circle'],
                            ['label' => 'Rutas', 'url' => ['/admin/route'], 'iconStyle' => 'far fa-circle'],
                            ['label' => 'Reglas', 'url' => ['/admin/rule'], 'iconStyle' => 'far fa-circle'],
                        ]
                    ],
                    [
                        'label' => 'Configuración',
                        'icon' => 'cogs',
                        'items' => [
                            ['label' => 'Parámetros', 'url' => ['/admin/config'], 'iconStyle' => 'far fa-circle'],
                            ['label' => 'Backup', 'url' => ['/admin/backup'], 'iconStyle' => 'far fa-circle'],
                        ]
                    ],
                    ['label' => 'Desarrollo', 'header' => true],
                    ['label' => 'Gii',  'icon' => 'file-code', 'url' => ['/gii'], 'target' => '_blank'],
                    ['label' => 'Debug', 'icon' => 'bug', 'url' => ['/debug'], 'target' => '_blank'],
                ],
            ]);*/
            $callback = function($menu){
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
                    'label' => $menu['name'], 
                    'url' => [$menu['route']],
                    'options' => $data,
                ];
                
                // Agregar icono al menú
                if (!empty($menu['children'])) {
                    // Si tiene hijos, es un menú desplegable
                    $menuItem['icon'] = $icon;
                    $menuItem['items'] = $menu['children'];
                } else {
                    // Si no tiene hijos, es un enlace simple
                    $menuItem['icon'] = $icon;
                }
                
                return $menuItem;
            };
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id,null,$callback)
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>