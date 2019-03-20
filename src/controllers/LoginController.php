<?php

namespace effsoft\eff\module\passport\controllers;

use effsoft\eff\EffController;
use effsoft\eff\module\passport\models\LoginForm;
use effsoft\eff\module\passport\models\UserModel;

class LoginController extends EffController{

    public function actions() {
        return [
            'captcha' => [
                'class' => 'effsoft\eff\actions\CaptchaAction',
                'maxLength' => 6,
                'minLength' => 6,
            ],
        ];
    }

    function actionIndex(){

        //Dynamic change theme

//        $this->getView()->theme = \Yii::createObject([
//            'class' => '\yii\base\Theme',
//            'pathMap' => [ dirname(dirname(__DIR__)) . '/src/views' => [
//                    dirname(dirname(dirname(__DIR__))) . '/effsoft/themes/effsoft/eff-module-passport'
//                    ],
//                ],
//            'baseUrl' => '@web/themes/basic',
//        ]);
//        return $this->render('index.php',[
//            'login_form' => new LoginForm(),
//        ],'effsoft');

        if(!\Yii::$app->user->isGuest){
            return $this->goHome();
        }
        $login_form = new LoginForm();

        if (\Yii::$app->request->isPost){
            $login_form->load(\Yii::$app->request->post());
            if(!$login_form->validate()){
                return $this->render('index.php',[
                    'login_form' => $login_form,
                ]);
            }
            
            $user_model = UserModel::findOne(['email' => $login_form->email]);
            if (empty($user_model)){
                $login_form->addError('request','用户名或密码错误！');
                return $this->render('index.php',[
                    'login_form' => $login_form,
                ]);
            }

            if (!\Yii::$app->security->validatePassword($login_form->password,$user_model->password)){
                $login_form->addError('request','用户名或密码错误！');
                return $this->render('index.php',[
                    'login_form' => $login_form,
                ]);
            }

            if(empty($user_model->activated)){
                $login_form->addError('request','您的帐号还未激活！');
                return $this->render('index.php',[
                    'login_form' => $login_form,
                ]);
            }

            if($user_model->blocked){
                $login_form->addError('request','您的帐号已被禁用！');
                return $this->render('index.php',[
                    'login_form' => $login_form,
                ]);
            }

            \Yii::$app->user->login($user_model, $login_form->remember ? 3600*24*30 : 0);
            return $this->goHome();
        }

        return $this->render('index.php',[
            'login_form' => $login_form,
        ]);
    }
}