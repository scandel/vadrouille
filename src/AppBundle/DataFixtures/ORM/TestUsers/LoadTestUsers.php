<?php
namespace AppBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumber;
use AppBundle\Entity\User;
use AppBundle\Entity\Person;

class LoadTestUsersData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $users = array(
            array(
                'email' => "severine@me.com",
                'firstName' => 'Séverine',
                'lastName' => 'Candelier',
                'gender' => 'w',
                'phone' => '33612345678',
                'roles' => array('ROLE_ADMIN','ROLE_SUPER_ADMIN')
            ),
            array(
                'email' => "jeannot@me.com",
                'firstName' => 'Jeannot',
                'lastName' => 'Lapin',
                'gender' => 'm',
                'phone' => '33612345678',
            ),
            array(
                'email' => "jeannette@me.com",
                'firstName' => 'Jeannette',
                'lastName' => 'Lapine',
                'gender' => 'w',
                'phone' => '33612345678',
            ),
        );

        foreach ($users as $user) {

            $theUser = new User();

            $phoneNumber = new PhoneNumber();
            $phoneNumber->setNationalNumber($user['phone']);

            $theUser->setEmail($user['email'])
                ->setEnabled(true)
                ->setPlainPassword("123456")
                ->setFirstName($user['firstName'])
                ->setLastName($user['lastName'])
                ->setGender($user['gender'])
                ->setPhone($phoneNumber);
            if (isset($user['roles']) && is_array($user['roles'])) {
                $theUser->setRoles($user['roles']);
            }

            $manager->persist($theUser);

            $person = new Person($theUser);
            $manager->persist($person);
            $manager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }

}