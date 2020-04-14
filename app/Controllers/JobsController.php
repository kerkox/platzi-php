<?php
namespace App\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Services\JobService;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;

class JobsController extends BaseController {

    private $jobService;

    public function __construct(JobService $jobService)
    {
        parent::__construct();
        $this->jobService = $jobService;
    }

    public function indexAction() {
        $jobs = Job::withTrashed()->get();
        return $this->renderHTML('jobs/index.twig', compact('jobs'));
    }

    public function deleteAction(ServerRequest $request) {
        $params = $request->getQueryParams();
        $this->jobService->deleteJob($params['id']);

        return new RedirectResponse('/jobs');
    }

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
                    $filePath = "uploads/$fileName";
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