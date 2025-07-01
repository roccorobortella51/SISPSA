<?php

use app\models\Area;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\AreaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Inicio';
$this->params['breadcrumbs'][] = $this->title;
use app\components\UserHelper;

?>
  

  <div class="container-fluid">
   
<div class="row">
        <!-- Notificaciones Widgets -->

        <div class="col-xl-3 col-md-6 col-sm-6">
          <a href="#">
            <div class="ms-card card-gradient-custom ms-widget ms-infographics-widget ms-p-relative">
              <div class="ms-card-body media">
                <div class="media-body">
                   <h6 class="bold">Clínicas</h6>
                  <p class="ms-card-change"><?php echo UserHelper::getTotalClinicas(); ?></p>
                </div>
              </div>
              <i class="fas fa-hospital ms-icon-mr"></i>
            </div>
          </a>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6">
          <a href="#">
            <div class="ms-card card-gradient-custom ms-widget ms-infographics-widget ms-p-relative">
              <div class="ms-card-body media">
                <div class="media-body">
                   <h6 class="bold">Agencias</h6>
                  <p class="ms-card-change"> <?php echo UserHelper::getTotalAgentes(); ?></p>
                </div>
              </div>
              <i class="fa fa-user-plus ms-icon-mr"></i>
            </div>
          </a>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6">
          <a href="#">
            <div class="ms-card card-gradient-custom ms-widget ms-infographics-widget ms-p-relative">
              <div class="ms-card-body media">
                <div class="media-body">
                  <h6 class="bold">Usuarios</h6>
                  <p class="ms-card-change"> <?php echo UserHelper::getTotalUsuarios(); ?></p>
                </div>
              </div>
              <i class="fa fa-user ms-icon-mr"></i>
            </div>
          </a>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6">
          <a href="#">
            <div class="ms-card card-gradient-custom ms-widget ms-infographics-widget ms-p-relative">
              <div class="ms-card-body media">
                <div class="media-body">
                  <h6 class="bold">Asesores</h6>
                  <p class="ms-card-change"> <?php echo UserHelper::getTotalAsesores(); ?></p>
                </div>
              </div>
              <i class="fas fa-briefcase-medical ms-icon-mr"></i>
            </div>
          </a>
        </div>
  </div>
    

    

    </div>
</div>



    </div>
  </main>
</div>
</div>
</div>
</div>
</div>
  </div>

</body>

</html>
