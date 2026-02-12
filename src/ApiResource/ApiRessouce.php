<?php

use ApiPlatform\Metadata\ApiResource;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Validator\Exception\ValidationException;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/sortie/{id}'),
        new GetCollection(uriTemplate: '/sorties'),
        new Post(uriTemplate: '/sortie'),
        new Patch(uriTemplate: '/sortie/{id}'),
        new Delete(uriTemplate: '/sortie/{id}'),
    ],

    exceptionToStatus: [
        ValidationException::class => 422,
    ]
)]

class ApiRessouce
{
    public string $id;
}