<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use app\middleware\FirebaseAuthMiddleware;
use app\models\Result;

/**
 * Results Controller with Firebase Authentication
 * 
 * This controller handles lottery results with Firebase token protection
 * for POST and PUT operations
 */
class ResultsController extends ActiveController
{
    public $modelClass = 'app\models\Result';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Add Firebase authentication middleware
        $behaviors['firebaseAuth'] = [
            'class' => FirebaseAuthMiddleware::class,
            // Only protect POST and PUT operations
            'only' => ['create', 'update'],
        ];
        
        // Configure CORS if needed
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3333', 'https://yourdomain.com'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        return $behaviors;
    }

    /**
     * Override create action to add custom logic
     */
    public function actionCreate()
    {
        $model = new Result();
        
        // Get Firebase user information
        $firebaseUser = Yii::$app->get('firebaseUser');
        
        // You can access user information like:
        // $userId = $firebaseUser->sub; // Firebase user ID
        // $email = $firebaseUser->email ?? null;
        // $name = $firebaseUser->name ?? null;
        
        $model->load(Yii::$app->request->getBodyParams(), '');
        
        // Add audit fields if needed
        $model->created_by = $firebaseUser->sub;
        $model->created_at = date('Y-m-d H:i:s');
        
        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        } else {
            Yii::$app->response->statusCode = 422;
            return [
                'errors' => $model->errors,
                'message' => 'Validation failed'
            ];
        }
    }

    /**
     * Override update action to add custom logic
     */
    public function actionUpdate($id)
    {
        $model = Result::findOne($id);
        
        if (!$model) {
            Yii::$app->response->statusCode = 404;
            return ['message' => 'Result not found'];
        }
        
        // Get Firebase user information
        $firebaseUser = Yii::$app->get('firebaseUser');
        
        $model->load(Yii::$app->request->getBodyParams(), '');
        
        // Add audit fields if needed
        $model->updated_by = $firebaseUser->sub;
        $model->updated_at = date('Y-m-d H:i:s');
        
        if ($model->save()) {
            return $model;
        } else {
            Yii::$app->response->statusCode = 422;
            return [
                'errors' => $model->errors,
                'message' => 'Validation failed'
            ];
        }
    }

    /**
     * Custom action to get current user info from Firebase token
     */
    public function actionMe()
    {
        $firebaseUser = Yii::$app->get('firebaseUser');
        
        return [
            'uid' => $firebaseUser->sub,
            'email' => $firebaseUser->email ?? null,
            'name' => $firebaseUser->name ?? null,
            'email_verified' => $firebaseUser->email_verified ?? false,
        ];
    }
}
