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
        $userValidator = v::key('username', v::stringType()->notEmpty())
                ->key('password', v::stringType()->notEmpty());

        try {
            $userValidator->assert($postData);
            $postData = $request->getParsedBody();

            $user = new User();
            $user->username = $postData['username'];
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
}
