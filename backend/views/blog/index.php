<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Blogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Blog', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'user.username',
                'header'=>'Blogger Name'],
        
            //'id',
            //'user.username',
            'title',
            'body:ntext',
            'created_at',
            //'updated_at',
            
            ['class' => 'yii\grid\ActionColumn',
            'header' => 'Actions',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                                'title' => Yii::t('app', 'lead-view'),
                    ]);
                },
    
                'update' => function ($url, $model) {
                    return ($model->user_id == Yii::$app->user->id)|| (Yii::$app->user->can('update-blog')) ? Html::a('<span class="glyphicon glyphicon-pencil" ></span>', $url, [
                                'title' => Yii::t('app', 'lead-update'),
                    ]) : '';
                },
                'delete' => function ($url, $model) {
                    return ($model->user_id == Yii::$app->user->id) || (Yii::$app->user->can('delete-blog')) ? Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                'title' => Yii::t('app', 'lead-delete'),
                                'data-method'  => 'post'
                    ]) : '';
                }
              ],
            ],
        ],
    ]); ?>
</div>
