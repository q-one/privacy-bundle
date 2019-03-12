<?php

/*
 * Copyright 2018-2019 Q.One Technologies GmbH, Essen
 * This file is part of QOnePrivacyBundle.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace QOne\PrivacyBundle\Tests\Functional;

use QOne\PrivacyBundle\Survey\SurveyRequest;
use QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Entity\DemoUser;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class BasicORMTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->createClient(['test_case' => 'BasicORMTest']);
        $this->containers[$this->environment] = $this->client->getContainer();
    }

    public function testCreateAddress()
    {
        $this->loadFixtures([]);
        $this->client->request('POST', '/test/create');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

//    public function testSurvey()
//    {
//        $this->loadFixtures([]);
//        $this->client->request('POST', '/test/survey');
//        $this->client->request(
//            'POST',
//            '/_privacy',
//            [], [], [],
//            $this->getContainer()->get('qone_privacy.publisher')
//                ->getEncodingStrategy()
//                ->encodeRequest(new SurveyRequest(DemoUser::class, ['id' => 1]))
//        );
//        $foo = $this->client->getResponse();
//        $survey = $foo->getContent();
//
//        $surveyd = $this->getContainer()->get('qone_privacy.publisher')
//            ->getEncodingStrategy()
//            ->decodeSurvey($survey, 'text/xml');
//
//        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//    }

//    public function testCollector()
//    {
//        $this->loadFixtures([]);
//        $this->client->request('POST', '/test/collector');
//
//        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//    }
}
