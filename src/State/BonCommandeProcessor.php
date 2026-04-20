<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\BonCommande;
use Symfony\Bundle\SecurityBundle\Security;

class BonCommandeProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof BonCommande && $data->getId() === null) {
            $data->setCreatedAt(new \DateTimeImmutable());
            $user = $this->security->getUser();
            if ($user) {
                $data->setCreateUser($user);
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
