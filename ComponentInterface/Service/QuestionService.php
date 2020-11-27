<?php

namespace App\ComponentInterface\Service;



use App\Repository\QuestionRepository;

class QuestionService{

    private $questionRepo;

    public function __construct(QuestionRepository $questionRepo){
        $this->questionRepo = $questionRepo;
    }


    public function retrieveQuestionById(int $id){
        return $this->questionRepo->findOneBy(["id" => $id]);
    }

}