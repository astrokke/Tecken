<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Assignment;
use App\Entity\Client;
use App\Entity\DueTask;
use App\Entity\Interlocutor;
use App\Entity\Milestone;
use App\Entity\State;
use App\Entity\Task;
use App\Entity\TypeOfActivity;
use App\Entity\TypeOfTask;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $date = new DateTimeImmutable();

        $typeOfActivity1 = new TypeOfActivity();
        $typeOfActivity1->setLabel("Interne");
        $typeOfActivity2 = new TypeOfActivity();
        $typeOfActivity2->setLabel("Externe");
        $typeOfActivity3 = new TypeOfActivity();
        $typeOfActivity3->setLabel("Courante");

        $typeOfTask1 = new TypeOfTask();
        $typeOfTask1->setLabel("Conception")
            ->setCoefHourRate(1)
            ->setColor("rgba(215, 244, 164, 0.5)");
        $typeOfTask2 = new TypeOfTask();
        $typeOfTask2->setLabel("Développement")
            ->setCoefHourRate(1)
            ->setColor("#E7EEFD");
        $typeOfTask3 = new TypeOfTask();
        $typeOfTask3->setLabel("Maintenance")
            ->setCoefHourRate(1)
            ->setColor("rgba(255, 0, 0, 0.5)");
        $typeOfTask4 = new TypeOfTask();
        $typeOfTask4->setLabel("Veille Technologique")
            ->setCoefHourRate(1)
            ->setColor("rgba(236, 47, 126, 0.5)");
        $typeOfTask5 = new TypeOfTask();
        $typeOfTask5->setLabel("Régie")
            ->setCoefHourRate(1)
            ->setColor("rgba(131, 56, 236, 0.5)");
        $typeOfTask6 = new TypeOfTask();
        $typeOfTask6->setLabel("Réunion")
            ->setCoefHourRate(1)
            ->setColor("rgba(255, 147, 0, 0.49)");
        $typeOfTask7 = new TypeOfTask();
        $typeOfTask7->setLabel("Autres")
            ->setCoefHourRate(1)
            ->setColor("rgba(233, 233, 233, 0.7)");

        $state1 = new State();
        $state1->setLabel("Non débuté");
        $state2 = new State();
        $state2->setLabel("En cours");
        $state3 = new State();
        $state3->setLabel("Terminé");
        $state4 = new State();
        $state4->setLabel("Annulé");

        $user1 = new User();
        $user1->setFirstName("Nicolas")
            ->setLastName("Soulay")
            ->setJob("Lead développeur")
            ->setMail("nsoulay@diginamic-formation.fr")
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setPhoneNumber("0635490817")
            ->setHourRateByDefault(25);
        $user2 = new User();
        $user2->setFirstName("Lilya")
            ->setLastName("Emad")
            ->setJob("Développeur")
            ->setMail("lemad-salih@diginamic-formation.fr")
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(20);
        $user3 = new User();
        $user3->setFirstName("Nathan")
            ->setLastName("Musielak")
            ->setJob("Développeur")
            ->setMail("nmusielak@diginamic-formation.fr")
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(20);
        $user4 = new User();
        $user4->setFirstName("Hervé")
            ->setLastName("Crevan")
            ->setJob("Responsable technique")
            ->setMail("hcrevan@diginamic.fr")
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER', 'ROLE_MANAGER'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(35);
        $user5 = new User();
        $user5->setFirstName("Lionel")
            ->setLastName("Cabon")
            ->setJob("Directeur")
            ->setMail("lcabon@diginamic.fr")
            ->setRoles(['ROLE_USER', 'ROLE_DIRECTEUR'])
            ->setPhoneNumber("0173820465");
        $user6 = new User();
        $user6->setFirstName("Robin")
            ->setLastName("Hotton")
            ->setJob("Lead dévelopeur")
            ->setMail("rhotton@diginamic-formation.fr")
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(25);
        $user7 = new User();
        $user7->setFirstName("Sébastien")
            ->setLastName("Thomas")
            ->setJob("Lead dévelopeur")
            ->setMail("sthomas@diginamic-formation.fr")
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(25);
        $user8 = new User();
        $user8->setFirstName("Valentin")
            ->setLastName("Momin")
            ->setJob("Assistant maitrise d'ouvrage")
            ->setMail("vmomin@diginamic-formation.fr")
            ->setRoles(['ROLE_USER', 'ROLE_MANAGER', 'ROLE_ADMIN'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(30);
        $user9 = new User();
        $user9->setFirstName("Abel")
            ->setLastName("Ciccoli")
            ->setJob("Dévelopeur")
            ->setMail("aciccoli@diginamic-formation.fr")
            ->setRoles(['ROLE_USER'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(20);
        $user10 = new User();
        $user10->setFirstName("Lucas")
            ->setLastName("Preaux")
            ->setJob("Dévelopeur")
            ->setMail("lpreaux@diginamic-formation.fr")
            ->setRoles(['ROLE_USER'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(20);
        $user11 = new User();
        $user11->setFirstName("Yvan")
            ->setLastName("Douënel")
            ->setJob("Formateur")
            ->setMail("ydouenel@diginamic.fr")
            ->setRoles(['ROLE_USER'])
            ->setPhoneNumber("0173820465")
            ->setHourRateByDefault(30);

        // $client1 = new Client();
        // $client1->setSIRET("19283740192837")
        //     ->setSIREN("192837401")
        //     ->setAdress("10 rue de la terrine")
        //     ->setSocialReason("Septeo")
        //     ->setPhoneNumber("0918257563")
        //     ->setMail("contact@septeo.fr")
        //     ->setWebSite("septeo.fr")
        //     ->setPostalCode("34000")
        //     ->setCity("Montpellier")
        //     ->setTVANumber("FR23192837401");
        $client2 = new Client();
        $client2->setSIRET("81824197800050")
            ->setSIREN("818241978")
            ->setAdress("40 rue Louis Lépine")
            ->setSocialReason("Diginamic")
            ->setPhoneNumber("0918257563")
            ->setMail("contact@diginamic.fr")
            ->setWebSite("diginamic.fr")
            ->setPostalCode("34470")
            ->setCity("Pérols")
            ->setTVANumber("FR05818241978");

        // $interlocutor1 = new Interlocutor();
        // $interlocutor1->setFirstName("Simon")
        //     ->setLastName("DeSepteo")
        //     ->setMail("simon@septeo.fr")
        //     ->setPhoneNumber("0424623978")
        //     ->setJob("Product Owner")
        //     ->setClient($client1);
        //
        // $interlocutor2 = new Interlocutor();
        // $interlocutor2->setFirstName("Constantin")
        //     ->setLastName("Second")
        //     ->setMail("constantin@septeo.fr")
        //     ->setPhoneNumber("0493847263")
        //     ->setJob("Lead Developer")
        //     ->setClient($client1);

        $interlocutor3 = new Interlocutor();
        $interlocutor3->setFirstName("Christophe")
            ->setLastName("Germain")
            ->setMail("cgermain@diginamic.fr")
            ->setPhoneNumber("0424623978")
            ->setJob("Résponsable Pédagogique")
            ->setClient($client2);

        $interlocutor4 = new Interlocutor();
        $interlocutor4->setFirstName("Lionel")
            ->setLastName("Cabon")
            ->setMail("lcabon@diginamic.fr")
            ->setPhoneNumber("0424623978")
            ->setJob("Directeur")
            ->setClient($client2);

        //
        // $activity1 = new Activity();
        // $activity1->setBillable(true)
        //     ->setName("Ingeneo")
        //     ->setStartDate($date->setDate(2024, 6, 12))
        //     ->setDescription("Migration du site Ingeneo sur Symfony 6.4")
        //     ->setClient($client1)
        //     ->setTypeOfActivity($typeOfActivity2)
        //     ->addInterlocutor($interlocutor1)
        //     ->addInterlocutor($interlocutor2);

        $activity2 = new Activity();
        $activity2->setBillable(false)
            ->setName("TeckTime")
            ->setStartDate($date->setDate(2024, 6, 12))
            ->setDescription("Développement d'un gestionaire de planning")
            ->setTypeOfActivity($typeOfActivity1);

        $activity3 = new Activity();
        $activity3->setBillable(true)
            ->setName("DigiRh")
            ->setStartDate($date->setDate(2024, 6, 12))
            ->setDescription("Support et évolution du site DigiRh")
            ->setClient($client2)
            ->setTypeOfActivity($typeOfActivity2)
            ->addInterlocutor($interlocutor4);

        // $task1 = new Task();
        // $task1->setName("Us345")
        //     ->setDescription("Affichage Portefeuille Client")
        //     ->setStartDateForecast($date->setDate(2024, 6, 12))
        //     ->setEndDateForecast($date->setDate(2025, 6, 27))
        //     ->setDurationForecast(9)
        //     ->setState($state1)
        //     ->setTypeOfTask($typeOfTask5)
        //     ->setActivity($activity1);

        // $task2 = new Task();
        // $task2->setName("Ticket Resto")
        //     ->setDescription("Résoudre le problème d'affichage des tickets restaurant")
        //     ->setStartDateForecast($date->setDate(2024, 6, 12))
        //     ->setEndDateForecast($date->setDate(2025, 6, 27))
        //     ->setDurationForecast(2)
        //     ->setState($state1)
        //     ->setTypeOfTask($typeOfTask3)
        //     ->setActivity($activity3);

        // $task3 = new Task();
        // $task3->setName("Développement")
        //     ->setDescription("Intégrer les demande du CdC")
        //     ->setStartDateForecast($date->setDate(2024, 6, 12))
        //     ->setEndDateForecast($date->setDate(2025, 6, 27))
        //     ->setDurationForecast(6)
        //     ->setState($state1)
        //     ->setTypeOfTask($typeOfTask2)
        //     ->setActivity($activity2);

        // $milestone1 = new Milestone;
        // $milestone1->setLabel("Version 1")
        //     ->setActivity($activity2)
        //     ->setStartDate($date->setDate(2024, 7, 10))
        //     ->setDateEnd($date->setDate(2024, 9, 22))
        //     ->addTask($task3);

        // $assignment1 = new Assignment();
        // $assignment1->setTask($task1)
        //     ->setCollaborator($user1);
        // $assignment2 = new Assignment();
        // $assignment2->setTask($task1)
        //     ->setCollaborator($user2);
        // $assignment3 = new Assignment();
        // $assignment3->setTask($task2)
        //     ->setCollaborator($user1);
        // $assignment4 = new Assignment();
        // $assignment4->setTask($task3)
        //     ->setCollaborator($user1);
        // $assignment5 = new Assignment();
        // $assignment5->setTask($task3)
        //     ->setCollaborator($user2);
        // $assignment6 = new Assignment();
        // $assignment6->setTask($task3)
        //     ->setCollaborator($user3);

        // $dueTask1 = new DueTask();
        // $dueTask1->setAssignment($assignment1)
        //     ->setDateDueTask($date->setDate(2024, 06, 25))
        //     ->setStartHour($date->setTime(9, 30, 0, 0))
        //     ->setEndHour($date->setTime(13, 0, 0, 0));
        // $dueTask2 = new DueTask();
        // $dueTask2->setAssignment($assignment2)
        //     ->setDateDueTask($date->setDate(2024, 06, 25))
        //     ->setStartHour($date->setTime(9, 30, 0, 0))
        //     ->setEndHour($date->setTime(13, 0, 0, 0));
        // $dueTask3 = new DueTask();
        // $dueTask3->setAssignment($assignment3)
        //     ->setDateDueTask($date->setDate(2024, 06, 25))
        //     ->setStartHour($date->setTime(14, 0, 0, 0))
        //     ->setEndHour($date->setTime(17, 30, 0, 0));
        // $dueTask4 = new DueTask();
        // $dueTask4->setAssignment($assignment4)
        //     ->setDateDueTask($date->setDate(2024, 06, 26))
        //     ->setStartHour($date->setTime(9, 30, 0, 0))
        //     ->setEndHour($date->setTime(11, 30, 0, 0));
        // $dueTask5 = new DueTask();
        // $dueTask5->setAssignment($assignment5)
        //     ->setDateDueTask($date->setDate(2024, 06, 27))
        //     ->setStartHour($date->setTime(14, 0, 0, 0))
        //     ->setEndHour($date->setTime(17, 30, 0, 0));
        // $dueTask6 = new DueTask();
        // $dueTask6->setAssignment($assignment6)
        //     ->setDateDueTask($date->setDate(2024, 06, 25))
        //     ->setStartHour($date->setTime(9, 30, 0, 0))
        //     ->setEndHour($date->setTime(12, 0, 0, 0));

        $manager->persist($typeOfActivity1);
        $manager->persist($typeOfActivity2);
        $manager->persist($typeOfActivity3);
        $manager->persist($typeOfTask1);
        $manager->persist($typeOfTask2);
        $manager->persist($typeOfTask3);
        $manager->persist($typeOfTask4);
        $manager->persist($typeOfTask5);
        $manager->persist($typeOfTask6);
        $manager->persist($typeOfTask7);
        $manager->persist($state1);
        $manager->persist($state2);
        $manager->persist($state3);
        $manager->persist($state4);
        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->persist($user4);
        $manager->persist($user5);
        $manager->persist($user6);
        $manager->persist($user7);
        $manager->persist($user8);
        $manager->persist($user9);
        $manager->persist($user10);
        $manager->persist($user11);
        // $manager->persist($client1);
        $manager->persist($client2);
        // $manager->persist($interlocutor1);
        // $manager->persist($interlocutor2);
        $manager->persist($interlocutor3);
        $manager->persist($interlocutor4);
        // $manager->persist($activity1);
        $manager->persist($activity2);
        $manager->persist($activity3);
        // $manager->persist($task1);
        // $manager->persist($task2);
        // $manager->persist($task3);
        // $manager->persist($milestone1);
        // $manager->persist($assignment1);
        // $manager->persist($assignment2);
        // $manager->persist($assignment3);
        // $manager->persist($assignment4);
        // $manager->persist($assignment5);
        // $manager->persist($assignment6);
        // $manager->persist($dueTask1);
        // $manager->persist($dueTask2);
        // $manager->persist($dueTask3);
        // $manager->persist($dueTask4);
        // $manager->persist($dueTask5);
        // $manager->persist($dueTask6);
        $manager->flush();
    }
}
