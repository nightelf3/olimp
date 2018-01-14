<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 12.04.2017
 * Time: 0:33
 */
namespace controllers;

use helpers\MailHelper;
use helpers\SessionHelper;
use helpers\SettingsHelper;
use helpers\TemplateHelper;
use helpers\UrlHelper;
use helpers\UserHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use models\UserModel;

class AccountController extends BaseController
{
    public function registration(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $userData = [
            'backLink' => UrlHelper::href(),
            'submitText' => TemplateHelper::text('register')
        ];

        if ($request->method('get')) {
            $this->data['userForm'] = TemplateHelper::render('components/user_form', $userData);
            return $this->render('registration/registration');
        }

        $user = $request->paramsPost()->all();
        $errors = $this->validateUserForm($user);
        if (false === empty($errors)) {
            $userData['errors'] = $errors;
            $userData['form'] = $user;
            $this->data['userForm'] = TemplateHelper::render('components/user_form', $userData);
            return $this->render('registration/registration');
        }

        MailHelper::mail($user['email'], 'registerSubject', 'register', $user);

        $userModel = new UserModel($user);
        $userModel->hashPassword()->save();

        $this->data['username'] = $user['username'];
        $this->data['email'] = $user['email'];
        return $this->render('registration/registration_succeed');
    }

    public function forgot(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        if ($request->method('get')) {
            return $this->render('forgot/forgot');
        }

        $form = $request->paramsPost()->all();
        $errors = $this->validateForgotForm($form);

        if (false == empty($errors)) {
            $this->data['form'] = $form;
            $this->data['errors'] = $errors;
            return $this->render('forgot/forgot');
        }

        /** @var UserModel $user */
        $user = UserModel::where([
            'email' => $form['email'],
            'username' => $form['username']
        ])->first();

        $newPassword = uniqid(mt_rand(), false);
        $user->password = $newPassword;
        $user->hashPassword()->save();

        $data = [
            'username' => $user->username,
            'password' => $newPassword
        ];
        MailHelper::mail($user->email, 'forgotSubject', 'forgot', $data);

        $this->data = [
            'username' => $user->username,
            'email' => $user->email
        ];
        return $this->render('forgot/forgot_succeed');
    }

    public function logout(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        SessionHelper::remove('userId');
        return $response->redirect(UrlHelper::href());
    }

    public function user(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $userData = [
            'form' => UserHelper::getUser(),
            'backLink' => UrlHelper::href('task'),
            'submitText' => TemplateHelper::text('save'),
            'updateOnly' => true
        ];
        if ($request->method('get')) {
            $this->data['userForm'] = TemplateHelper::render('components/user_form', $userData);
            return $this->render('user');
        }

        $user = $request->paramsPost()->all();
        $errors = $this->validateUserForm($user, true);
        if (false === empty($errors)) {
            $userData['errors'] = $errors;
            $this->data['userForm'] = TemplateHelper::render('components/user_form', $userData);
            return $this->render('user');
        }

        $user = UserHelper::getUser();
        $user->fill([
            'class' => $user['class'],
            'school' => $user['school'],
            'phone' => $user['phone'],
            'name' => $user['name'],
            'surname' => $user['surname']
        ]);
        if (!empty($user['password'])) {
            $user->password = $user['password'];
            $user->hashPassword();
        }
        $user->save();

        $userData['succeed'] = true;
        $this->data['userForm'] = TemplateHelper::render('components/user_form', $userData);
        return $this->render('user');
    }

    protected function validateUserForm($user, $updateOnly = false)
    {
        $errors = [];
        if (!!$updateOnly) {
            if (!isset($user['username']) || empty($user['username'])) {
                $errors['username'] = true;
            }

            if (isset($user['username']) && UserModel::where(['username' => $user['username']])->count() > 0) {
                $errors['usernameExists'] = true;
            }
        }

        if (!$updateOnly && (!isset($user['password']) || empty($user['password']))) {
            $errors['password'] = true;
        }

        if (!$updateOnly && (!isset($user['password2']) || empty($user['password2'])) || $user['password'] !== $user['password2']) {
            $errors['password2'] = true;
        }

        if (!$updateOnly && (!isset($user['email']) || empty($user['email']) || false === filter_var($user['email'], FILTER_VALIDATE_EMAIL))) {
            $errors['email'] = true;
        }

        if (!$updateOnly && (isset($user['email']) && UserModel::where([ 'email' => $user['email'] ])->count() > 0)) {
            $errors['emailExists'] = true;
        }

        if (!isset($user['surname']) || empty($user['surname'])) {
            $errors['surname'] = true;
        }

        if (!isset($user['name']) || empty($user['name'])) {
            $errors['name'] = true;
        }

        if (!isset($user['phone']) || empty($user['phone'])) {
            $errors['phone'] = true;
        }

        if (!isset($user['school']) || empty($user['school'])) {
            $errors['school'] = true;
        }

        if (!isset($user['class']) || empty($user['class'])) {
            $errors['class'] = true;
        }

        return $errors;
    }

    protected function validateForgotForm($form)
    {
        $errors = [];
        if (!isset($form['username']) || empty($form['username'])) {
            $errors['username'] = true;
        }

        if (!isset($form['email']) || empty($form['email']) || false === filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = true;
        }

        if (0 === UserModel::where([
            'email' => $form['email'],
            'username' => $form['username']
        ])->count()) {
            $errors['userNotExists'] = true;
        }

        return $errors;
    }
}
