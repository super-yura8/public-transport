<?php

namespace App\Controller;

use App\Entity\Transport;
use App\Form\TransportType;
use App\Repository\TransportRepository;
use App\Service\FormsErrorManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;


#[Route('/api/transports', name: 'api_transport_')]
class TransportController extends AbstractController
{

    private TransportRepository $transportRepository;
    private EntityManagerInterface $em;
    private Serializer $serializer;

    public function __construct(TransportRepository $transportRepository, EntityManagerInterface $em)
    {
        $this->transportRepository = $transportRepository;
        $this->em = $em;
        $this->serializer = SerializerBuilder::create()->setPropertyNamingStrategy(
            new SerializedNameAnnotationStrategy(
                new IdenticalPropertyNamingStrategy()
            ))->build();
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
        $data = $this->serializer->toArray($data, context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC']));
        return $this->json($data);
    }

    #[Route('/{id}', name: 'get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function get($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW');
        $transport = $this->transportRepository->find($id);
        if (!is_null($transport)) {
            $transport = $this->serializer
                ->toArray(
                    $transport,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                );
            return $this->json($transport);
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, FormsErrorManager $formsErrorManager): JsonResponse
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
            $transport = $this->serializer
                ->toArray(
                    $transport,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                );
            return $this->json($transport);
        } else {
            return $this->json([
                'message' => $formsErrorManager->getErrorsFromForm($form)
            ], Response::HTTP_CONFLICT);
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
    public function update(Request $request, FormsErrorManager $formsErrorManager, $id): JsonResponse
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
                $transport = $this->serializer
                    ->toArray(
                        $transport,
                        context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                    );
                return $this->json($transport);
            } else {
                return $this->json([
                    'message' => $formsErrorManager->getErrorsFromForm($form)
                ], Response::HTTP_CONFLICT);
            }
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/favorites', methods: ['GET'])]
    public function getFavorites(#[CurrentUser] $user): JsonResponse
    {
        $transport = $this->serializer
            ->toArray(
                $user->getTransports(),
                context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
            );
        return $this->json($transport);
    }

    #[Route('/favorites', methods: ['POST'])]
    public function addToTransportToFavorite(#[CurrentUser] $user, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if(isset($data['transport']) && !is_null($data['transport'])) {
            if(!is_null($transport = $this->transportRepository->find($data['transport']))) {
                $user->addTransport($transport);
                $this->em->persist($user);
                $this->em->flush();
                return new Response(status: Response::HTTP_ACCEPTED);
            }
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/favorites/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function removeFavoriteTransport(#[CurrentUser] $user, $id): Response
    {
        if(!is_null($transport = $this->transportRepository->find($id))) {
                $user->removeTransport($transport);
                $this->em->persist($user);
                $this->em->flush();
                return new Response(status: Response::HTTP_ACCEPTED);
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }
}
