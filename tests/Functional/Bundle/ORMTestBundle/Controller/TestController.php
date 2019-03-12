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

namespace QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Controller;

use QOne\PrivacyBundle\Survey\SurveyRequest;
use QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Entity\DemoEntity;
use QOne\PrivacyBundle\Tests\Functional\Bundle\ORMTestBundle\Entity\DemoUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    public function createAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = new DemoUser('user', 'password');
        $em->persist($user);
        $em->flush();

        $demo = new DemoEntity();
        $demo->setUser($user);
        $demo->setStr('Hallo Hallo');
        $demo->setFoo('foofoo');
        $em->persist($demo);
        $em->flush();

        $em->clear();

        $demo = $em->getRepository(DemoEntity::class)->find($demo->getId());
        $demo->setStr('trololololol');
        $em->persist($demo);
        $em->flush();

        $em->clear();

        $demo = $em->getRepository(DemoEntity::class)->find($demo->getId());
        $demo->setFoo('xxxxxxxxxxxxxxxxxxx');
        $em->persist($demo);
        $em->flush();

        $em->clear();

        $demo = $em->getRepository(DemoEntity::class)->find($demo->getId());
        $em->remove($demo);
        $em->flush();

        return new Response('success');
    }

    public function surveyAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = new DemoUser('user', 'password');
        $em->persist($user);
        $em->flush();

        for ($i = 0; $i < 100; ++$i) {
            $demo = new DemoEntity();
            $demo->setUser($user);
            $demo->setStr('Hallo Hallo '.$i);
            $em->persist($demo);
        }

        $em->flush();

        return new Response('success');
    }

    public function collectorAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = new DemoUser('user', 'password');
        $em->persist($user);
        $em->flush();

        for ($i = 0; $i < 100; ++$i) {
            $demo = new DemoEntity();
            $demo->setUser($user);
            $demo->setStr('Hallo Hallo '.$i);
            $em->persist($demo);
        }

        $em->flush();

        $foo = $this->get('qone_privacy.collector')->createSurvey(new SurveyRequest(DemoUser::class, ['id' => 1]));

        return new Response('success');
    }
}
