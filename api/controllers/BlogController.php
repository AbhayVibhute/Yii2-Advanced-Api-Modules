<?php

namespace api\controllers;

use Yii;
use api\models\ApiBlog;
use backend\models\Blog;
use yii\data\ActiveDataProvider;

define('FAILDE_TO_SAVE', 400);
define('USER_NOT_ALLOWED_TO_UPDATE', 100);
define('USER_NOT_ALLOWED_TO_DELETE', 101);
/**
 * 
 * http://localhost:8888/blog/api/v1/blogs
 * Run ./yii migrate command
 * CustomerController implements the CRUD actions for Customer model.
 */
class BlogController extends LoginController
{
    public $modelClass = 'api\models\ApiBlog';
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionIndex(){
        $this->validateToken();

        if(isset($_GET['fields'])) {
            $query = Blog::find()->select($_GET['fields']);
            $provider =  new ActiveDataProvider([
                'query' => $query,
            ]);
        } elseif (isset($_GET['page']) && isset($_GET['per_page'])){
            $query = Blog::find();
            $provider =  new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $_GET['per_page'],
                ],
            ]);
        } else {
            $query = Blog::find();
            $provider =  new ActiveDataProvider([
                'query' => $query,
            ]);
        }
            
        return $provider->getModels();
    }

    public function actionView($id){
        $this->validateToken();
        $model = Blog::findOne($id);
        return $model;
    }

    public function actionCreate(){
        $this->validateToken();
        
        $model = new ApiBlog();
        
        $model->load(Yii::$app->request->post(), '');
        $model->user_id = Yii::$app->session->get('user_id');
        $model->created_at = date('Y-m-d h:m:s');
        $model->updated_at = '0000-00-00 00:00:00';

        if($model->save())        
            return $model;
    }

    public function actionUpdate($id){
        $user = $this->validateToken();
        
        $model = Blog::findOne($id);

        if($model->user_id == $user->userId || $user->role == 'admin'){
                $model->load(Yii::$app->request->post(), '');
                $model->updated_at = date('Y-m-d h:m:s');
                if($model->save())
                    return $model;
        }else{
            $this->returnResponse(USER_NOT_ALLOWED_TO_UPDATE, 'This user is not allowed to update this blog.');
        }
    }

    public function actionDelete($id){

        $this->validateToken();

        $model = Blog::findOne($id);

        if($model->user_id == Yii::$app->session->get('user_id')){
            $model->delete();
        }else{
            $this->returnResponse(USER_NOT_ALLOWED_TO_DELETE, 'This user is not allowed to delete this blog.');
        }
    }
}
