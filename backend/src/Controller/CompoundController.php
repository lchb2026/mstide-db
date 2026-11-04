<?php

namespace App\Controller;

use App\Entity\Compound;
use App\Entity\Fragment;
use App\Entity\Type;

use App\Repository\CompoundRepository;
use App\Repository\FragmentRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/compound')]
class CompoundController extends AbstractController
{
    #[Route('/fragments', name: 'compound_fragments', methods: ['GET'])]
    public function fragments(FragmentRepository $fragmentRepository): JsonResponse
    {
        $fragments = $fragmentRepository->findAll();

        $serialized = array_map(fn(Fragment $f) => [
            'id' => $f->getId(),
            'name' => $f->getName(),
            'canonicalSmiles' => $f->getCanonicalSmiles(),
            'molecularMass' => $f->getMolecularMass(),
            'picture' => $f->getPicture() ? $_ENV['BACKEND_URL'] . $f->getPicture() : null,
        ], $fragments);

        return $this->json($serialized);
    }

    #[Route('/maxSecondDigit', name: 'compound_max_second_digit', methods: ['GET'])]
    public function getMaxSecondDigit(Request $request, CompoundRepository $compoundRepository): JsonResponse
    {
        $mass = $request->query->get('mass');

        if (!$mass || !is_numeric($mass)) {
            return $this->json(['error' => 'Invalid mass'], 400);
        }

        $qb = $compoundRepository->createQueryBuilder('c')
            ->where('c.compoundId LIKE :massPattern')
            ->setParameter('massPattern', $mass . '.%');

        $ids = $qb->select('c.compoundId')->getQuery()->getScalarResult();

        $maxSecond = 0;
        foreach ($ids as $row) {
            $parts = explode('.', $row['compoundId']);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $second = (int)$parts[1];
                if ($second > $maxSecond) $maxSecond = $second;
            }
        }

        return $this->json(['maxSecond' => $maxSecond]);
    }

    #[Route('/compare', name: 'compound_compare', methods: ['POST'])]
    public function compare(Request $request, CompoundRepository $compoundRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return $this->json(['error' => 'No valid ids provided'], 400);
        }

        $ids = array_map('intval', $ids);

        $compounds = $compoundRepository->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $serialized = array_map(fn(Compound $c) => [
            'id' => $c->getId(),
            'abbr' => $c->getAbbr(),
            'name' => $c->getName(),
            'massTable' => $c->getMassTable(),
            'picture' => $c->getPicture() ? $_ENV['BACKEND_URL'] . $c->getPicture() : null,
        ], $compounds);

        return $this->json($serialized);
    }

    #[Route('/types', name: 'compound_types', methods: ['GET'])]
    public function getTypes(EntityManagerInterface $em): JsonResponse
    {
        $types = $em->getRepository(Type::class)->findAll();


        $serialized = array_map(fn(Type $t) => [
            'id' => $t->getId(),
            'name' => $t->getName(),
            'group' => $t->getGroup()
                ? [
                    'id' => $t->getGroup()->getId(),
                    'code' => $t->getGroup()->getCode(),
                    'name' => $t->getGroup()->getName(),
                ]
                : null,
        ], $types);


        return $this->json($serialized);
    }

    #[Route('/list', name: 'compound_list', methods: ['QUERY'])]
    public function list(Request $request, CompoundRepository $compoundRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $qb = $compoundRepository->createQueryBuilder('c');
        $logic = !empty($data['logic']) && strtoupper($data['logic']) === 'OR' ? 'OR' : 'AND';
        $conditions = [];
        $expr = $qb->expr();

        if (!empty($data['name'])) {
            $conditions[] = $expr->orX(
                $expr->like('c.name', ':name'),
                $expr->like('c.abbr', ':name')
            );
            $qb->setParameter('name', '%' . $data['name'] . '%');
        }

        if (!empty($data['molecularFormula'])) {
            $conditions[] = $expr->like('c.molecularFormula', ':mf');
            $qb->setParameter('mf', '%' . $data['molecularFormula'] . '%');
        }

        if (!empty($data['compoundId'])) {
            $conditions[] = $expr->like('c.compoundId', ':ci');
            $qb->setParameter('ci', '%' . $data['compoundId'] . '%');
        }

        if (!empty($data['canonicalSmiles'])) {
            $conditions[] = $expr->like('c.canonicalSmiles', ':cs');
            $qb->setParameter('cs', '%' . $data['canonicalSmiles'] . '%');
        }

        if (!empty($data['mz'])) {
            $mzValues = array_unique(
                array_map(
                    'floatval',
                    array_filter(array_map('trim', explode(',', str_replace(' ', ',', $data['mz']))))
                )
            );

            $qb->leftJoin('c.fragments', 'f');

            if ($logic === 'AND') {
                $qb->andWhere('f.molecularMass IN (:mz)')
                    ->groupBy('c.id')
                    ->having('COUNT(DISTINCT f.molecularMass) = :mzCount')
                    ->setParameter('mz', $mzValues)
                    ->setParameter('mzCount', count($mzValues));
            } else {
                $conditions[] = $expr->in('f.molecularMass', ':mz');
                $qb->setParameter('mz', $mzValues);
            }
        }

        if (!empty($data['types']) && is_array($data['types'])) {
            $qb->leftJoin('c.types', 't');
            if ($logic === 'AND') {
                $qb->andWhere('t.id IN (:types)')
                    ->groupBy('c.id')
                    ->having('COUNT(DISTINCT t.id) = :typesCount')
                    ->setParameter('types', $data['types'])
                    ->setParameter('typesCount', count($data['types']));
            } else {
                $conditions[] = $expr->in('t.id', ':types');
                $qb->setParameter('types', $data['types']);
            }
        }

        if (!empty($data['charge']) && is_array($data['charge'])) {
            $chargeConditions = [];

            foreach ($data['charge'] as $charge) {
                if ($charge == '1') {
                    $chargeConditions[] = $expr->notlike('c.molecularFormula', ':charge_2minus');
                }

                if ($charge == '2') {
                    $chargeConditions[] = $expr->like('c.molecularFormula', ':charge_2minus');
                }
            }

            $qb->setParameter('charge_2minus', '%)2-');

            if (!empty($chargeConditions)) {
                $qb->andWhere(
                    call_user_func_array([$expr, 'orX'], $chargeConditions)
                );
            }
        }

        if (!empty($conditions)) {
            if ($logic === 'AND') {
                $qb->andWhere(call_user_func_array([$expr, 'andX'], $conditions));
            } else {
                $qb->andWhere(call_user_func_array([$expr, 'orX'], $conditions));
            }
        }

        $compounds = $qb->getQuery()->getResult();
        $serialized = array_map(fn($c) => $this->serializeCompound($c), $compounds);

        return $this->json($serialized);
    }

    #[Route('/{id}', name: 'compound_page', methods: ['GET'])]
    public function get(Request $request, Compound $compound, EntityManagerInterface $em): JsonResponse
    {
        return $this->json($this->serializeCompound($compound));
    }

    #[Route('/{id}', name: 'compound_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id, EntityManagerInterface $em, CompoundRepository $repo): JsonResponse
    {
        $compound = $repo->find($id);
        if (!$compound) {
            return $this->json(['error' => 'Compound not found'], 404);
        }

        $em->remove($compound);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Compound deleted']);
    }

    #[Route('/save', name: 'compound_save_new', methods: ['POST'])]
    #[Route('/{id}/save', name: 'compound_save_existing', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function save(
        Request $request,
        EntityManagerInterface $em,
        CompoundRepository $repo,
        ?int $id = null
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $compound = $id ? $repo->find($id) : new Compound();

        if ($id && !$compound) {
            return $this->json(['error' => 'Compound not found'], 404);
        }

        $this->mapDataToCompound($compound, $data, $em);

        $em->persist($compound);
        $em->flush();

        return $this->json($this->serializeCompound($compound));
    }

    #[Route('/{id}/upload-file', name: 'compound_upload_file', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadFile(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        CompoundRepository $repo
    ): JsonResponse {
        $compound = $repo->find($id);

        if (!$compound) {
            return $this->json(['error' => 'Compound not found'], 404);
        }

        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'No file provided'], 400);
        }

        $type = $request->request->get('type');
        $dir = '';
        $oldFile = null;

        switch ($type) {
            case 'picture':
                $dir = 'images';
                $oldFile = $compound->getPicture();
                break;
            case 'sdf':
                $dir = 'sdf';
                $oldFile = $compound->getSDF();
                break;
            default:
                return $this->json(['error' => 'Invalid type'], 400);
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/compounds/' . $dir;
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        if ($oldFile) {
            $oldPath = $this->getParameter('kernel.project_dir') . '/public' . $oldFile;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
        $filename = uniqid('comp_') . '.' . $extension;
        $file->move($uploadsDir, $filename);

        $relativePath = '/uploads/compounds/' . $dir . '/' . $filename;

        if ($type === 'picture') {
            $compound->setPicture($relativePath);
        }
        if ($type === 'sdf') {
            $compound->setSDF($relativePath);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'file' => $relativePath,
        ]);
    }

    private function mapDataToCompound(Compound $compound, array $data, EntityManagerInterface $em): void
    {
        $compound->setAbbr($data['abbr'] ?? null);
        $compound->setName($data['name'] ?? null);
        $compound->setMolecularFormula($data['molecularFormula'] ?? null);
        $compound->setCompoundId($data['compoundId'] ?? null);
        $compound->setInstrument($data['instrument'] ?? null);
        $compound->setScanType($data['scanType'] ?? null);
        $compound->setIonization($data['ionization'] ?? null);
        $compound->setDeclusteringPotential($data['declusteringPotential'] ?? null);
        $compound->setCollisionEnergy($data['collisionEnergy'] ?? null);
        $compound->setCanonicalSmiles($data['canonicalSmiles'] ?? null);
        $compound->setMassTable($data['massTable'] ?? null);

        if (isset($data['types']) && is_array($data['types'])) {
            $compound->clearTypes();

            foreach ($data['types'] as $typeId) {
                $type = $em->getRepository(Type::class)->find($typeId);
                if ($type) {
                    $compound->addType($type);
                }
            }
        }

        if (isset($data['fragments']) && is_array($data['fragments'])) {
            $compound->clearFragments();

            foreach ($data['fragments'] as $fragmentId) {
                $fragment = $em->getRepository(Fragment::class)->find($fragmentId);
                if ($fragment) {
                    $compound->addFragment($fragment);
                }
            }
        }
    }

    private function serializeCompound(Compound $compound): array
    {
        $fragments = $compound->getFragments()->toArray();
        usort(
            $fragments,
            fn(Fragment $a, Fragment $b) => $a->getMolecularMass() <=> $b->getMolecularMass()
        );

        return [
            'id' => $compound->getId(),
            'abbr' => $compound->getAbbr(),
            'name' => $compound->getName(),
            'molecularFormula' => $compound->getMolecularFormula(),
            'compoundId' => $compound->getCompoundId(),
            'instrument' => $compound->getInstrument(),
            'scanType' => $compound->getScanType(),
            'ionization' => $compound->getIonization(),
            'declusteringPotential' => $compound->getDeclusteringPotential(),
            'collisionEnergy' => $compound->getCollisionEnergy(),
            'canonicalSmiles' => $compound->getCanonicalSmiles(),
            'massTable' => $compound->getMassTable(),
            'fragments' => array_map(fn(Fragment $f) => [
                'id' => $f->getId(),
                'name' => $f->getName(),
                'canonicalSmiles' => $f->getCanonicalSmiles(),
                'molecularMass' => $f->getMolecularMass(),
                'picture' => $f->getPicture() ? $_ENV['BACKEND_URL'] . $f->getPicture() : null
            ], $fragments),
            'picture' => $compound->getPicture() ? $_ENV['BACKEND_URL'] . $compound->getPicture() : null,
            'sdf' => $compound->getSDF() ?: null,
            'types' => array_map(fn(Type $t) => [
                'id' => $t->getId(),
                'name' => $t->getName(),
                'group' => $t->getGroup()
                    ? [
                        'id' => $t->getGroup()->getId(),
                        'code' => $t->getGroup()->getCode(),
                        'name' => $t->getGroup()->getName(),
                    ]
                    : null,
            ], $compound->getTypes()->toArray())
        ];
    }
}
