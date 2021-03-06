<?php

namespace App\Controller;

use App\Entity\Orders\TransferOrder;
use App\Entity\Orders\TransferOrderLineItem;
use App\Entity\StorageLocation;
use App\Transformers\TransferOrderTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/orders/transfer")
 */
class TransferOrdersController extends OrderController
{
    protected $defaultEntityName = TransferOrder::class;

    /**
     * @return TransferOrderLineItem
     */
    protected function createLineItem()
    {
        return new TransferOrderLineItem();
    }

    /**
     * Save a new Transfer
     *
     * @Route(path="", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $params = $this->getParams($request);

        $order = new TransferOrder();

        if ($params['sourceLocation']['id']) {
            $newLocation = $this->getEm()->find(StorageLocation::class, $params['sourceLocation']['id']);
            $order->setSourceLocation($newLocation);
        }

        if ($params['targetLocation']['id']) {
            $newLocation = $this->getEm()->find(StorageLocation::class, $params['targetLocation']['id']);
            $order->setTargetLocation($newLocation);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $order->validate();

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($order);

        $this->getEm()->persist($order);
        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * Whole or partial update of a order
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        $params = $this->getParams($request);
        /** @var TransferOrder $order */
        $order = $this->getOrder($id);

        $this->checkEditable($order);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($order);


        if ($params['sourceLocation']['id']) {
            $newLocation = $this->getEm()->find(StorageLocation::class, $params['sourceLocation']['id']);
            $order->setSourceLocation($newLocation);
        }

        if ($params['targetLocation']['id']) {
            $newLocation = $this->getEm()->find(StorageLocation::class, $params['targetLocation']['id']);
            $order->setTargetLocation($newLocation);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $order->validate();

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    protected function getDefaultTransformer()
    {
        return new TransferOrderTransformer;
    }
}
