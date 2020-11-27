<?php

namespace App\ComponentInterface\Service;
use App\ComponentInterface\CustomException\UserTerminateForm;
use App\Entity\FormLog;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\FormLogRepository;

use Doctrine\ORM\EntityManagerInterface;



class FormLoggingService{

    private $formLogRepo;
    private $entityManager;

    public function __construct(FormLogRepository $userRepo, EntityManagerInterface $entityManager){
        $this->formLogRepo = $userRepo;
        $this->entityManager = $entityManager;
    }


    public function insertFormLog(string $userIdentification, Question $question, User $user = null, string $answer = null){

        if($answer && $answer == FormService::$endForm)
            throw new UserTerminateForm();

        $formLog = new FormLog();
        $formLog->setUser($user);
        $formLog->setUserIdentification($userIdentification);
        $formLog->setQuestionId($question->getId());
        $formLog->setContent($answer ? $answer : $question->getQuestion());
        $formLog->setType($answer ? "answer" : "question");

        $this->entityManager->persist($formLog);
        $this->entityManager->flush();

    }

    public function retrieveUserFormLogs(string $userIdentification){

        return $this->formLogRepo->findBy(["user_identification" => $userIdentification]);

    }

    public function retrieveUserLastQuestionLog(string $userIdentification){

        $userFormLogs = $this->formLogRepo->findBy(["user_identification" => $userIdentification]);
        return $userFormLogs[count($userFormLogs) - 1];

    }

    public function deleteUserFormLogs(string $userIdentification){
        $formLogs = $this->formLogRepo->findAll();

        foreach ($formLogs as $formLog)
            $this->entityManager->remove($formLog);

        $this->entityManager->flush();
    }


}