<?php

namespace App\Doctrine;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Bundle\SecurityBundle\Security;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    // On injecte le service de Sécurité pour savoir qui est connecté
    public function __construct(private Security $security)
    {
    }

    // 1. S'active quand on demande une LISTE de ressources (GET /api/tickets)
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    // 2. S'active quand on demande UN SEUL item (GET /api/tickets/1)
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    // La logique commune : C'est ici que la magie opère 🪄
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // A. On vérifie si l'entité demandée est bien un TICKET
        if (Ticket::class !== $resourceClass) {
            return;
        }

        // B. On récupère l'utilisateur connecté
        $user = $this->security->getUser();

        // C. Si personne n'est connecté ou si c'est un ADMIN, on montre tout (on ne filtre pas)
        if (!$user instanceof User || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        // D. Si c'est un utilisateur normal (Technicien), on filtre 
        
        // On récupère l'alias de la table (ex: 't' pour Ticket)
        $rootAlias = $queryBuilder->getRootAliases()[0];

        // On modifie la requête SQL : "Montre-moi les tickets SEULEMENT si ils sont assignés à moi"
        $queryBuilder->andWhere(sprintf('%s.assignedTo = :current_user', $rootAlias));
        
        // On injecte l'ID de l'utilisateur connecté dans le paramètre
        $queryBuilder->setParameter('current_user', $user);
    }
}