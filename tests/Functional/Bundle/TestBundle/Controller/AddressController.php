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

namespace QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Controller;

use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity\Address;
use QOne\PrivacyBundle\Tests\Functional\Bundle\TestBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AddressController.
 */
class AddressController extends AbstractController
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    protected $em;

    /**
     * AddressController constructor.
     */
    public function __construct()
    {
    }

    /**
     * @Route("/address", name="create_address", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAddressAction(Request $request)
    {
        $this->em = $this->getDoctrine()->getManager();

        // TODO just creating a new user per request for now
        $user = new User();
        $user->setUsername('qone_'.rand(10, 100000));
        $user->setPassword('somethingplain');

        $this->em->persist($user);

        $street = $request->request->get('street', 'Gladbecker Str. 433');
        $zip = $request->request->get('zip', '45329');
        $city = $request->request->get('city', 'Essen');

        $address = new Address();

        $address->setStreet($street);
        $address->setZip($zip);
        $address->setCity($city);
        $address->setUser($user);

        $this->em->persist($address);
        $this->em->flush();

        return new Response(sprintf('Saved new address with ID "%s"', $address->getId()));
    }

    public function updateAddressAction(Address $address)
    {
    }

    public function deleteAddressAction(Address $address)
    {
    }
}
