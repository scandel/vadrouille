<?php
namespace AppBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumber;
use AppBundle\Entity\User;

class LoadTestUsersData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $phoneNumber = new PhoneNumber();
        $phoneNumber->setNationalNumber("33612345678");

        $admin = new User();
        $admin->setEmail("severine@me.com")
            ->setEnabled(true)
            ->setPlainPassword("123456")
            ->setFirstName("Séverine")
            ->setLastName("Candelier")
            ->setGender('w')
            ->setPhone($phoneNumber)
            ->setRoles(array('ROLE_ADMIN','ROLE_SUPER_ADMIN'));;
        $manager->persist($admin);

        $user1 = new User();
        $user1->setEmail("jeannot@me.com")
            ->setEnabled(true)
            ->setPlainPassword("123456")
            ->setFirstName("Jeannot")
            ->setLastName("Lapin")
            ->setGender('m')
            ->setPhone($phoneNumber);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail("jeannette@me.com")
            ->setEnabled(true)
            ->setPlainPassword("123456")
            ->setFirstName("Jeannette")
            ->setLastName("Lapine")
            ->setGender('w')
            ->setPhone($phoneNumber);
        $manager->persist($user2);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }

}