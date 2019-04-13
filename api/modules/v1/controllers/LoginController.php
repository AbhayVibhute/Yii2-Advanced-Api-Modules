<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use common\models\User;
use Firebase\JWT\JWT;
use  yii\web\Session;

define("SECRETE_KEY", 'test123');
define("SUCCESS_RESPONSE", 200);
define('ACCESS_TOKEN_ERROR', 302);
/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class LoginController extends ActiveController
{
    public $modelClass = 'common\models\LoginForm';


    public function actionGenerateToken(){
        $session = Yii::$app->session;

        if($session->has('token')){
            $session->destroy();
        }

        $username = Yii::$app->request->post('username');
        $user = User::find()->where(['username'=>$username])->one();
        $password = Yii::$app->request->post('password');
        if(Yii::$app->security->validatePassword($password, $user->password_hash)){
            $payload = [
                'exp' => time() + (60*65),
                'userId' => $user->id,
                'role' => $user->role
            ];
            
            $token = JWT::encode($payload, SECRETE_KEY);
            $session->set('user_id', $user->id);
            $data = ['token' => $token, 'message'=>'First Time generated.'];
            
            $this->returnResponse(SUCCESS_RESPONSE, $data);

            die();
        }else{
            print_r('username or password is invalid');
        }
        die();
    }

    /**
    * Get hearder Authorization
    * */
    public function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    public function getBearerToken() {

        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        $this->throwError( ATHORIZATION_HEADER_NOT_FOUND, 'Access Token Not found');
    }

    public function validateToken() {
        try {
            $session = Yii::$app->session;
            if($session->has('token')){
                $this->refreshToken();
            }else{
                $token = $this->getBearerToken();

                $payload = JWT::decode($token, SECRETE_KEY, ['HS256']);
                
                if(($payload->exp - time()) / 60 <= 5){

                    $this->refreshToken($payload, $token);
                }
            
                return $payload;
            }
            } catch (Exception $e) {
                $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
            }
    }

    public function refreshToken($payload = '', $token = ''){
         
        $session = Yii::$app->session;
        
        if (!$session->has('token')) {
            
            $refreshPayload = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (60*3),
                'userId' => $payload->userId
            ];
            
            $token = JWT::encode($refreshPayload, SECRETE_KEY);

            $session->set('token', $token);
            //$session->set('user_id', $payload->id);

        } else {
            $token = $session->get('token');

            $payload = JWT::decode($token, SECRETE_KEY, ['HS256']);

            if(($payload->exp - time()) / 60 <= 2){
                
                $refreshPayload = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (60*5),
                    'userId' => $payload->userId
                ];
                
                $token = JWT::encode($refreshPayload, SECRETE_KEY);

                $session->set('token', $token);
            }
        }
    }

    public function returnResponse($code, $data){
        header('content-type:application/json');
        $response = json_encode(['response' => ['status'=>$code, 'message'=>$data]]);
        echo $response; die();
    }

    public function throwError($code, $message){
        header('content-type:application/json');
        $errorMsg =  json_encode(['error' => ['status' => $code, 'message'=>$message]]);
        echo $errorMsg; exit;
    }

}
