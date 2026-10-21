<?php

namespace App\Controller;

use App\Entity\Compound;
use App\Entity\Fragment;

use App\Repository\FragmentRepository;
use App\Repository\CompoundRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/fragment')]
class FragmentController extends AbstractController
{
    #[Route('/compounds', name: 'fragment_compounds', methods: ['GET'])]
    public function compounds(CompoundRepository $compoundRepository): JsonResponse
    {
        $compounds = $compoundRepository->findAll();

        $serialized = array_map(fn(Compound $c) => [
            'id' => $c->getId(),
            'abbr' => $c->getAbbr(),
            'name' => $c->getName(),
            'compoundId' => $c->getCompoundId(),
            'picture' => $c->getPicture() ? $_ENV['BACKEND_URL'] . $c->getPicture() : null
        ], $compounds);

        return $this->json($serialized);
    }

    #[Route('/list', name: 'fragment_list', methods: ['QUERY'])]
    public function list(Request $request, FragmentRepository $fragmentRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $qb = $fragmentRepository->createQueryBuilder('f');
        $logic = !empty($data['logic']) && strtoupper($data['logic']) === 'OR' ? 'OR' : 'AND';
        $expr = $qb->expr();
        $conditions = [];

        if (!empty($data['name'])) {
            $conditions[] = $expr->like('f.name', ':name');
            $qb->setParameter('name', '%' . $data['name'] . '%');
        }

        if (!empty($data['compoundCanonicalSmiles'])) {
            $qb->join('f.compounds', 'c');
            $conditions[] = $expr->like('c.canonicalSmiles', ':ccs');
            $qb->setParameter('ccs', '%' . $data['compoundCanonicalSmiles'] . '%');
        }

        if (!empty($data['molecularMass'])) {
            $mzValues = array_map('floatval', array_map('trim', explode(',', str_replace(' ', ',', $data['molecularMass']))));
            foreach ($mzValues as $i => $mz) {
                $paramName = 'mz_' . $i;
                $conditions[] = $expr->eq('f.molecularMass', ':' . $paramName);
                $qb->setParameter($paramName, $mz);
            }
        }

        if (!empty($conditions)) {
            if ($logic === 'AND') {
                $qb->andWhere(call_user_func_array([$expr, 'andX'], $conditions));
            } else {
                $qb->andWhere(call_user_func_array([$expr, 'orX'], $conditions));
            }
        }

        $fragments = $qb->getQuery()->getResult();
        usort(
            $fragments,
            fn(Fragment $a, Fragment $b) => $a->getMolecularMass() <=> $b->getMolecularMass()
        );
        $serialized = array_map(fn($f) => $this->serializeFragment($f), $fragments);

        return $this->json($serialized);
    }

    #[Route('/{id}', name: 'fragment_page', methods: ['GET'])]
    public function get(Fragment $fragment): JsonResponse
    {
        return $this->json($this->serializeFragment($fragment));
    }

    #[Route('/{id}', name: 'fragment_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id, EntityManagerInterface $em, FragmentRepository $repo): JsonResponse
    {
        $fragment = $repo->find($id);
        if (!$fragment) {
            return $this->json(['error' => 'Fragment not found'], 404);
        }

        $em->remove($fragment);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Fragment deleted']);
    }

    #[Route('/save', name: 'fragment_save_new', methods: ['POST'])]
    #[Route('/{id}/save', name: 'fragment_save_existing', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function save(
        Request $request,
        EntityManagerInterface $em,
        FragmentRepository $repo,
        ?int $id = null
    ): JsonResponse {
        $fragment = $id ? $repo->find($id) : new Fragment();

        if ($id && !$fragment) {
            return $this->json(['error' => 'Fragment not found'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $this->mapDataToFragment($fragment, $data, $em);

        $em->persist($fragment);
        $em->flush();

        return $this->json($this->serializeFragment($fragment));
    }

    #[Route('/{id}/upload-picture', name: 'fragment_upload_picture', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadPicture(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        FragmentRepository $repo
    ): JsonResponse {
        $fragment = $repo->find($id);

        if (!$fragment) {
            return $this->json(['error' => 'Fragment not found'], 404);
        }

        $file = $request->files->get('picture');
        if (!$file) {
            return $this->json(['error' => 'No picture provided'], 400);
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/fragments/images';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        if ($fragment->getPicture()) {
            $oldPath = $this->getParameter('kernel.project_dir') . '/public' . $fragment->getPicture();
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $filename = uniqid('frag_') . '.' . $file->guessExtension();
        $file->move($uploadsDir, $filename);

        $fragment->setPicture('/uploads/fragments/images/' . $filename);

        $em->flush();

        return $this->json([
            'success' => true,
            'picture' => $fragment->getPicture(),
        ]);
    }

    private function mapDataToFragment(Fragment $fragment, array $data, EntityManagerInterface $em): void
    {
        $fragment->setName($data['name'] ?? '');
        $fragment->setCanonicalSmiles($data['canonicalSmiles'] ?? '');
        $fragment->setMolecularMass($data['molecularMass'] ?? 0);

        if (isset($data['compounds']) && is_array($data['compounds'])) {
            $fragment->clearCompounds();

            foreach ($data['compounds'] as $compoundId) {
                if (!$compoundId) continue;

                $compound = $em->getRepository(Compound::class)->find($compoundId);
                if ($compound) {
                    $fragment->addCompound($compound);
                }
            }
        }
    }

    private function serializeFragment(Fragment $fragment): array
    {
        return [
            'id' => $fragment->getId(),
            'name' => $fragment->getName(),
            'canonicalSmiles' => $fragment->getCanonicalSmiles(),
            'molecularMass' => $fragment->getMolecularMass(),
            'compounds' => array_map(fn(Compound $c) => [
                'id' => $c->getId(),
                'abbr' => $c->getAbbr(),
                'name' => $c->getName(),
                'compoundId' => $c->getCompoundId(),
                'picture' => $c->getPicture() ? $_ENV['BACKEND_URL'] . $c->getPicture() : null
            ], $fragment->getCompounds()->toArray()),
            'picture' => $fragment->getPicture()
                ? $_ENV['BACKEND_URL'] . $fragment->getPicture()
                : null,
        ];
    }
}
