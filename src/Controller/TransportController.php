<?php

namespace App\Controller;

use App\Entity\Transport;
use App\Form\TransportType;
use App\Repository\TransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/transports', name: 'api_transport_')]
class TransportController extends AbstractController
{

    private TransportRepository $transportRepository;
    private EntityManagerInterface $em;

    public function __construct(TransportRepository $transportRepository, EntityManagerInterface $em)
    {
        $this->transportRepository = $transportRepository;
        $this->em = $em;
    }

    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW');
        $filter = [];
        if (!is_null($param = $request->query->get('type'))) {
            $filter['type'] = $param;
        }
        if (!is_null($param = $request->query->get('active'))) {
            $filter['active'] = $param;
        }
        try {
            $data = $this->transportRepository->findBy($filter, ['type' => 'ASC', 'number' => 'ASC']);
        } catch (\Throwable $exception) {
            $data = [];
        }
        $serializer = SerializerBuilder::create()->build();
        $data = $serializer->toArray($data, context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC']));
        return $this->json($data);
    }

    #[Route('/{id}', name: 'get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function get($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW');
        $transport = $this->transportRepository->find($id);
        if (!is_null($transport)) {
            $serializer = SerializerBuilder::create()->build();
            $transport = $serializer
                ->toArray(
                    $transport,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                );
            return $this->json($transport);
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('CREATE');
        $data = json_decode($request->getContent(), true);
        $transport = new Transport();
        $form = $this->createForm(TransportType::class, $transport);

        try {
            $form->submit($data);
        } catch (\Throwable $exception) {
            return $this->json(
                ['message'=> 'The data is incorrect or is not full'],
                Response::HTTP_BAD_REQUEST
            );
        }
        if ($form->isValid()) {
            $this->em->persist($transport);
            $this->em->flush();
            $serializer = SerializerBuilder::create()->build();
            $transport = $serializer
                ->toArray(
                    $transport,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                );
            return $this->json($transport);
        } else {
            return $this->json(
                ['message'=> 'The data is incorrect'],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('DELETE');
        $transport = $this->transportRepository->find($id);
        if (!is_null($transport)) {
            $this->transportRepository->remove($transport, true);
            return $this->json(['message' => 'The transport is deleted']);
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PATCH', 'PUT'])]
    public function update(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('UPDATE');
        $data = json_decode($request->getContent(), true);
        $transport = $this->transportRepository->find($id);
        if (!is_null($transport)) {
            $form = $this->createForm(TransportType::class, $transport);
            $clearMissing = $request->getMethod() != 'PATCH';
            try {
                $form->submit($data, $clearMissing);
            } catch (\Throwable $exception) {
                return $this->json(
                    ['message'=> 'The data is incorrect or is not full'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            if ($form->isValid()) {
                $this->em->flush();
                $serializer = SerializerBuilder::create()->build();
                $transport = $serializer
                    ->toArray(
                        $transport,
                        context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                    );
                return $this->json($transport);
            } else {
                return $this->json(
                    ['message'=> 'The data is incorrect'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }
}
