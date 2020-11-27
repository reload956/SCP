<?php 

namespace App\ComponentInterface\Service;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserService{

    private $userRepo;
    private $entityManager;
    private $passwordEncoder;

    public function __construct(UserRepository $userRepo, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder){
        $this->userRepo = $userRepo;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function createUser(array $userData){

        $user = new User();
        $user->setName($userData["name"]);
        $user->setEmail($userData["email"]);
        $user->setPhone($userData["phone"]);

        //password need to be hashed
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $userData["password"]
        ));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;

    }

    /**
     * {@inheritDoc}
     */
    public function findUserWithPSID($PSID){
        return $this->userRepo->findOneBy(["name" => $PSID]);
    }

    /**
     * {@inheritDoc}
     */
    public function createUserWithPSID($PSID){
        $user = new User();
        $user->setName($PSID);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserWithPhone($phone) {
        return $this->userRepo->findOneBy(["phone" => $phone]);
    }


}