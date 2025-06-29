<?php
use yii\bootstrap4\Nav;
use mdm\admin\components\MenuHelper;
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="border-bottom: none; background-color: #007bff !important; color: white !important;">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link d-flex justify-content-center" style="padding-top: 0; padding-bottom: 0; border-bottom: none;">
        <img src="<?= Yii::getAlias('@web') ?>/img/sispsa-12-62.png" alt="Logo" class="brand-image" style="opacity: 1; margin: 15px auto; max-width: 150px; width: auto; height: auto; object-fit: contain;">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel d-flex flex-column align-items-center">
            <div class="image mb-2">
                <img src="<?= Yii::getAlias('@web') ?>/img/avatar5.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info text-center">
                <a href="#" class="d-block"><?= Yii::$app->user->identity->username ?? 'Usuario' ?></a>
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
