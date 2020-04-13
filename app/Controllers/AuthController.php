<?php
namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;


class AuthController extends BaseController {

    public function getLogin()
    {
        return $this->renderHTML('login.twig');
    }

    public function getLogout()
    {
        unset($_SESSION['userId']);
        return new RedirectResponse('/login');
    }



    public function postLogin(ServerRequest $request){
        $responseMessage = null;
        $postData = $request->getParsedBody();
        $user = User::where('username',$postData['username'])->first();
        if($user) {
            if(password_verify($postData['password'],$user->password)){
                $_SESSION['userId'] = $user->id_user;
               return new RedirectResponse('/admin');
            } else {
                $responseMessage = "constraseña o usuario incorrecto";
            }
        } else {
                $responseMessage ="constraseña o usuario incorrecto";
            
        }

        return $this->renderHTML('login.twig', ['responseMessage' => $responseMessage]);

    }

}