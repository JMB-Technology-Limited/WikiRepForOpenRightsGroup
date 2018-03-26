<?php

namespace DirectokiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;




/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseTest extends WebTestCase
{

    protected $container;

    protected $application;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();

        $this->application = new Application(static::$kernel);
        $this->application->setAutoExit(false);

    }


    protected function getJSONBitOfAJSONPString($content) {
        $contentBits = explode('{', $content, 2);
        $contentBits = explode('}', $contentBits[1]);
        array_pop($contentBits);
        $content = implode('}', $contentBits);
        return '{'.$content.'}';
    }

}
