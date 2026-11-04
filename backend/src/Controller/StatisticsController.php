<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StatisticsController extends AbstractController
{
    #[Route('/api/statistics', name: 'statistics', methods: ['GET'])]
    public function __invoke(Connection $connection): JsonResponse
    {
        $counts = $connection->fetchAssociative('
            SELECT
                (SELECT COUNT(*) FROM compounds) AS compounds,
                (SELECT COUNT(*) FROM fragments) AS fragments,
                (SELECT COUNT(*) FROM fragments_compounds) AS assigned_peaks
        ');

        return $this->json([
            'compounds' => (int) $counts['compounds'],
            'fragments' => (int) $counts['fragments'],
            'assignedPeaks' => (int) $counts['assigned_peaks'],
        ]);
    }
}
