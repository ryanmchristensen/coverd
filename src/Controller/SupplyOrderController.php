<?php

namespace App\Controller;

use App\Entity\Orders\SupplyOrder;
use App\Entity\Orders\SupplyOrderLineItem;
use App\Entity\Supplier;
use App\Entity\SupplierAddress;
use App\Entity\Warehouse;
use App\Transformers\SupplyOrderTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/orders/supply")
 */
class SupplyOrderController extends OrderController
{
    protected $defaultEntityName = SupplyOrder::class;

    /**
     * @return \App\Entity\Orders\SupplyOrderLineItem
     */
    protected function createLineItem()
    {
        return new SupplyOrderLineItem();
    }

    /**
     * Save a new Supply Order
     *
     * @Route(path="", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $params = $this->getParams($request);

        $order = new SupplyOrder();

        if ($params['warehouse']['id']) {
            $newWarehouse = $this->getEm()->find(Warehouse::class, $params['warehouse']['id']);
            $order->setWarehouse($newWarehouse);
        }

        if ($params['supplier']['id']) {
            $newSupplier = $this->getEm()->find(Supplier::class, $params['supplier']['id']);
            $order->setSupplier($newSupplier);
        }

        if ($params['supplierAddress']['id']) {
            $newSupplierAddress = $this->getEm()->find(SupplierAddress::class, $params['supplierAddress']['id']);
            $order->setSupplierAddress($newSupplierAddress);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

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
     */
    public function update(Request $request, $id)
    {
        $params = $this->getParams($request);
        /** @var \App\Entity\Orders\SupplyOrder $order */
        $order = $this->getOrder($id);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($order);

        $this->checkEditable($order);
        
        if ($params['warehouse']['id']) {
            $newWarehouse = $this->getEm()->find('App\Entity\Warehouse', $params['warehouse']['id']);
            $order->setWarehouse($newWarehouse);
        }

        if ($params['supplier']['id']) {
            $newSupplier = $this->getEm()->find('App\Entity\Supplier', $params['supplier']['id']);
            $order->setSupplier($newSupplier);
        }

        if ($params['supplierAddress']['id']) {
            $newSupplierAddress = $this->getEm()->find('App\Entity\SupplierAddress', $params['supplierAddress']['id']);
            $order->setSupplierAddress($newSupplierAddress);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }



    protected function getDefaultTransformer()
    {
        return new SupplyOrderTransformer;
    }
}
