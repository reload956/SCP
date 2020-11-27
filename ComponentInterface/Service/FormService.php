<?php

namespace App\ComponentInterface\Service;





use App\BotChannel\ChannelRequest\ChannelRequest;
use App\ComponentInterface\CustomException\NoFormIsOpened;
use App\ComponentInterface\CustomException\UserFormEnded;
use App\ComponentInterface\CustomException\UserTerminateForm;
use App\Entity\Question;
use App\Repository\FormRepository;

class FormService{

    public static $startFormType = "FORM_STARTED";
    public static $startFormQID = -1;
    public static $endForm = "STOP_FORM";

    private $formRepo;
    private $formLoggingService;
    private $questionService;
    private $userService;

    public static $defaultFormName = "register";
    protected  $form;

    public function __construct(FormRepository $formRepo, FormLoggingService $formLoggingService, QuestionService $questionService, UserService $userService){
        $this->formRepo = $formRepo;
        $this->formLoggingService = $formLoggingService;
        $this->questionService = $questionService;
        $this->userService = $userService;

        //initialize default form
        $this->form = $this->formRepo->findOneBy(["name" => static::$defaultFormName]);
    }

    public function isUserFormOpened(string $userIdentification){
        return count($this->formLoggingService->retrieveUserFormLogs($userIdentification)) ? true : false;
    }

    public function startUserForm(ChannelRequest $channelRequest){
        $startMessage = "You have started " . $this->form->getName() . " form \nSend \"" . static::$endForm. "\" to terminate it, \nplease answer the following " . $this->form->getQuestionsNumber() . " questions: \n\n";

        $startQuestion = new Question();
        $startQuestion->setQuestion($startMessage);
        $startQuestion->setId(static::$startFormQID);

        $formNextQuestion = $this->askNextQuestion($channelRequest, $startQuestion, $startMessage);

        return $startMessage . $formNextQuestion->getDisplayedQuestion();
    }

    public function retrieveFormNextQuestion(Question $question = null){
        $formQuestions = $this->form->getQuestions()->toArray();
        usort($formQuestions, function(Question $question1, Question $question2){
            return $question1->getQuestionOrder() - $question2->getQuestionOrder();
        });

        if(!$question) return count($formQuestions) ? $formQuestions[0] : null;

        for($i = 0; $i < count($formQuestions) - 1; $i++){
            if($formQuestions[$i]->getId() === $question->getId())
                return $formQuestions[$i + 1];
        }

        throw new UserFormEnded();
        return null;
    }

    public function isLastQuestion(Question $question){
        return $this->retrieveFormNextQuestion() ? true : false;
    }

    public function retrieveLastAskedQuestion(string $userIdentification){

        if($this->isUserFormOpened($userIdentification)){

            $lastQuestionLog = $this->formLoggingService->retrieveUserLastQuestionLog($userIdentification);
            return $this->questionService->retrieveQuestionById($lastQuestionLog->getQuestionId());

        }

        return null;

    }

    public function getSubmittedForm(string $userIdentification, bool $output = false){

        //construct submitted form from database
        $userFormLogs = $this->formLoggingService->retrieveUserFormLogs($userIdentification);

        //even question answer pair
        $form = [];
        $outputForm = "";
        for($i = 1; $i < count($userFormLogs); $i += 2){

            //question answer pair
            $form[$userFormLogs[$i]->getContent()] = $userFormLogs[$i + 1]->getContent();
            $outputForm .= $userFormLogs[$i]->getContent() . ": " . $userFormLogs[$i + 1]->getContent() . "\n";
        }

        $form["phone"] = $userIdentification;  //for whatsapp only, to be abstract later
        $outputForm .= "phone: " . $userIdentification;

        return $output ? $outputForm : $form;


    }

    public function clearUserFormLogging(string $userIdentification){

        //clear user form from database
        $this->formLoggingService->deleteUserFormLogs($userIdentification);

    }

    public function askNextQuestion(ChannelRequest $channelRequest, Question $previousQuestion, $message){

        //save answer, reply with next question
        $this->formLoggingService->insertFormLog($channelRequest->getUserIdentification(), $previousQuestion, null, $message);

        //insert next question then ask it
        $nextFormQuestion = $this->retrieveFormNextQuestion($previousQuestion->getId() != static::$startFormQID ? $previousQuestion : null);

        //save question wait for answer
        $this->formLoggingService->insertFormLog($channelRequest->getUserIdentification(), $nextFormQuestion);

        return $nextFormQuestion;
    }

    public function handleFormIfOpened(ChannelRequest $channelRequest, $message){

        //check form status for this user
        if(!$this->isUserFormOpened($channelRequest->getUserIdentification()))
            throw new NoFormIsOpened();


        try{
            //get last question log
            $userLastQuestion = $this->retrieveLastAskedQuestion($channelRequest->getUserIdentification());

            return $this->askNextQuestion($channelRequest, $userLastQuestion, $message)->getDisplayedQuestion();
        }catch (UserFormEnded $userFormEnded){
            //form completed, lets register that fkin user
            $submittedForm = $this->getSubmittedForm($channelRequest->getUserIdentification());

                //register user
            $this->userService->createUser($submittedForm);

            $submittedFormOut = $this->getSubmittedForm($channelRequest->getUserIdentification(), true);

            //delete form logging
            $this->clearUserFormLogging($channelRequest->getUserIdentification());

            return $submittedFormOut . "\n\n You have registered successfully..";
        }catch (UserTerminateForm $userTerminateForm){

            //delete form logging
            $this->clearUserFormLogging($channelRequest->getUserIdentification());

            return "Form Terminated..";

        }

    }

    public function handleFormIfAvailable(ChannelRequest $channelRequest, $message){

        return $message == ChannelRequest::$registerMe ? $this->startUserForm($channelRequest) : $this->handleFormIfOpened($channelRequest, $message);

    }

}