<?php
/**
 * Created by PhpStorm.
 * User: formation
 * Date: 06/09/2017
 * Time: 10:10
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Author;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthorFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $encoderFactory = $this->container->get('security.encoder_factory');
        $encoder = $encoderFactory->getEncoder(new Author());
        $password = $encoder->encodePassword('123', null);

        $author = new Author();
        $author->setName('Hugo')
            ->setFirstName('Victor')
            ->setEmail('v.hugo@miserable.fr')
            ->setPassword($password);

        $this->addReference('auteur_1', $author);
        $manager->persist($author);

        $author2 = new Author();
        $author2->setName('Destin')
            ->setFirstName('Mopao')
            ->setEmail('yemei@mokonzi.com')
            ->setPassword($password);

        $this->addReference('auteur_2', $author2);
        $manager->persist($author2);

        $author3 = new Author();
        $author3->setName('Ducasse')
            ->setFirstName('Victor')
            ->setEmail('d.toto@tata.com')
            ->setPassword($password);

        $this->addReference('auteur_3', $author3);
        $manager->persist($author3);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}