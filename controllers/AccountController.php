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
use helpers\UrlHelper;
use Klein\Request;
use Klein\Response;
use models\UserModel;

class AccountController extends BaseController
{
    public function registration(Request $request, Response $response, $service, $app)
    {
        if ($request->method('get')) {
            return $this->render('registration/registration');
        }

        $user = $request->paramsPost()->all();
        $errors = $this->validateRegistrationForm($user);
        if (false === empty($errors)) {
            $this->data['errors'] = $errors;
            $this->data['form'] = $user;
            return $this->render('registration/registration');
        }

        MailHelper::mail($user['email'], 'registerSubject', 'register', $user);

        $userModel = new UserModel($user);
        $userModel->hashPassword()->save();

        $this->data = [
            'username' => $user['username'],
            'email' => $user['email']
        ];
        return $this->render('registration/registration_succeed');
    }

    //get
    public function forgot(Request $request, Response $response, $service, $app)
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

    public function logout(Request $request, Response $response, $service, $app)
    {
        SessionHelper::remove('userId');
        return $response->redirect(UrlHelper::href());
    }

    protected function validateRegistrationForm($user)
    {
        $errors = [];
        if (!isset($user['username']) || empty($user['username'])) {
            $errors['username'] = true;
        }

        if (isset($user['username']) && UserModel::where([ 'username' => $user['username'] ])->count() > 0) {
            $errors['usernameExists'] = true;
        }

        if (!isset($user['password']) || empty($user['password'])) {
            $errors['password'] = true;
        }

        if (!isset($user['password2']) || empty($user['password2']) || $user['password'] !== $user['password2']) {
            $errors['password2'] = true;
        }

        if (!isset($user['email']) || empty($user['email']) || false === filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = true;
        }

        if (isset($user['email']) && UserModel::where([ 'email' => $user['email'] ])->count() > 0) {
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
