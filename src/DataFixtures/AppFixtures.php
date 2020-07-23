<?php

namespace App\DataFixtures;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Profil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encode;
    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder=$encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $profils=["ADMIN","FORMATEUR","APPRENANT","CM"];
        foreach($profils as $key=>$libelle){
            $profil = new Profil();
            $profil->setLibelle($libelle);
            $manager->persist($profil);
            $manager->flush();
            for ($i=0; $i < 3; $i++) { 
                $user= new User();
                $user->setProfil($profil);
                $user->setLogin(strtolower($libelle).$i);
                $user->setNomComplet($faker->name);
                //Generation des users
                $password=$this->encoder->encodePassword($user,'pass_1234');
                $user->setPassword($password);
                $manager->persist($user);
            }
            $manager->flush();
        }
    }
}
