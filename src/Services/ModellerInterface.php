<?php

namespace Fabricio872\ApiModeller\Services;

use Doctrine\Common\Collections\ArrayCollection;

interface ModellerInterface
{
    /**
     * @param Repo $repo
     * @return ArrayCollection|mixed
     * @throws \ReflectionException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getData(Repo $repo);
}
