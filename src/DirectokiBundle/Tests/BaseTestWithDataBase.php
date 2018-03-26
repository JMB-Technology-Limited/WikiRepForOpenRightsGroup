<?php

namespace DirectokiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseTestWithDataBase extends BaseTest
{


    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;


    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();
        $this->em = $this->container
            ->get('doctrine')
            ->getManager();

        $this->application = new Application(static::$kernel);
        $this->application->setAutoExit(false);
        $this->application->run(new StringInput('doctrine:schema:drop --force --quiet'));
        $this->application->run(new StringInput('doctrine:migrations:version  --no-interaction --delete --all --quiet'));

        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_field_email_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_field_lat_lng_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS directory' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS project' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_field_text_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_field_boolean_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS contact' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_field_multi_select_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS select_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_report' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_field_string_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_note' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS event' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_field_url_value' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS field' );
        $this->em->getConnection()->exec( 'DROP TABLE IF EXISTS record_has_state' );


        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_field_url_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS event_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS field_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS directory_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_field_boolean_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS contact_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_field_lat_lng_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_state_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_field_string_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_field_email_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_report_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS project_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_field_text_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS record_has_field_multi_select_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS select_value_id_seq' );
        $this->em->getConnection()->exec( 'DROP SEQUENCE IF EXISTS user_account_id_seq' );

        $this->application->run(new StringInput('doctrine:migrations:migrate --no-interaction --quiet'));


    }



    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        if ($this->em) {
            $this->em->close();
        }
    }

}
