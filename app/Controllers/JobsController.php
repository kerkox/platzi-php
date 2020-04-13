<?php
namespace App\Controllers;

use App\Models\Job;
use App\Models\User;
use Respect\Validation\Validator as v;

class JobsController extends BaseController {
    public function getAddJobAction($request) {
        $responseMessage = null;
        $user = null;
        if(isset($_SESSION['userId'])){
            $user = User::where('id_user',$_SESSION['userId'])->first();
        }
        if ($request->getMethod() == 'POST') {
            $postData = $request->getParsedBody();
            $jobValidator = v::key('title', v::stringType()->notEmpty())
                  ->key('description', v::stringType()->notEmpty());

            try {
                $jobValidator->assert($postData);
                $postData = $request->getParsedBody();

                $files = $request->getUploadedFiles();
                $logo = $files['logo'];
                $fileName = "";
                if($logo->getError() == UPLOAD_ERR_OK){
                    $fileName = $logo->getClientFileName();
                    $filePath = "/uploads/$fileName";
                    $logo->moveTo($filePath);
                }
                $job = new Job();
                $job->title = $postData['title'];
                $job->description = $postData['description'];
                $job->logo = $filePath;
                $job->save();

                $responseMessage = 'Saved';
            } catch (\Exception $e) {
                $responseMessage = $e->getMessage();
            }
        }

        return $this->renderHTML('addJob.twig', [
            'responseMessage' =>$responseMessage,
            'user' => $user
        ]);
    }
}