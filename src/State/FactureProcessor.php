<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Facture;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;

class FactureProcessor implements ProcessorInterface
{
    public function __construct(
        private FactureRepository $factureRepository,
        private EntityManagerInterface $em,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Facture && empty($data->getNumero())) {
            $data->setNumero($this->factureRepository->generateNumero());
        }

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
