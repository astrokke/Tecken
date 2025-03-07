<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function deleteUser(User $user): bool
    {
        try {
            $connection = $this->entityManager->getConnection();

            // Début de la transaction
            $connection->beginTransaction();

            try {
                // Désactiver les contraintes
                $connection->executeStatement('SET CONSTRAINTS ALL DEFERRED');

                // Supprimer les références associées
                $connection->executeStatement(
                    'DELETE FROM due_task WHERE collaborator_id = ?',
                    [$user->getId()]
                );

                $connection->executeStatement(
                    'DELETE FROM assignment WHERE collaborator_id = ?',
                    [$user->getId()]
                );

                // Nettoyer l'image si elle existe
                if ($user->getImage()) {
                    $user->setImage(null);
                }

                // Supprimer l'utilisateur
                $this->entityManager->remove($user);
                $this->entityManager->flush();

                // Réactiver les contraintes
                $connection->executeStatement('SET CONSTRAINTS ALL IMMEDIATE');

                // Valider la transaction
                $connection->commit();

                return true;
            } catch (\Exception $e) {
                // Annuler la transaction en cas d'erreur
                $connection->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
