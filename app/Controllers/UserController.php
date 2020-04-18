<?php
namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;

class UserController extends BaseController
{
    public function getAddUserAction($request)
    {
        return $this->renderHTML('addUser.twig');
    }

    public function postSaveUserAction($request)
    {
        $responseMessage = null;
        $postData = $request->getParsedBody();
        $userValidator = v::key('email', v::stringType()->notEmpty())
                ->key('password', v::stringType()->notEmpty());

        try {
            $userValidator->assert($postData);
            $postData = $request->getParsedBody();

            $user = new User();
            $user->email = $postData['email'];
            $user->password = password_hash($postData['password'], PASSWORD_DEFAULT);
            $user->save();

            $responseMessage = 'Saved';
        } catch (\Exception $e) {
            $responseMessage = $e->getMessage();
        }

        return $this->renderHTML('addUser.twig', [
            'responseMessage' =>$responseMessage
        ]);
    }

    public function getEditUserAction(){
        $user = User::findOrFail($_SESSION['userId']);
        return $this->renderHTML('users/edit.twig', [
            'user' => $user
        ]);
    }

    public function postUpdateUserAction($request){
        $responseMessage = null;
        $postData = $request->getParsedBody();
        $user = null;
        $userValidator = v::key('password', v::stringType()->notEmpty());
        try {
            $userValidator->assert($postData);
            $postData = $request->getParsedBody();

            $user = User::findOrFail($_SESSION['userId']);
            $user->password = password_hash($postData['password'], PASSWORD_DEFAULT);
            $user->save();

            $responseMessage = 'Updated';
        } catch (\Exception $e) {
            $responseMessage = $e->getMessage();
        }

        return $this->renderHTML('users/edit.twig', [
            'responseMessage' =>$responseMessage,
            'user' => $user
        ]);
    }
}
