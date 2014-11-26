<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Inventory\Packaging;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Inventory\Model\InventoryUnitInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Inventory\Packaging\Splitter\SplitterInterface;

/**
 * Default package builder implementation.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class PackagesBuilder implements PackageBuilderInterface
{
    /**
     * @var StockLocationProviderInterface
     */
    protected $stockLocationRepository;

    /**
     * @var PackerInterface $packer
     */
    protected $packer;

    /**
     * @param StockLocationRepositoryInterface $stockLocationRepository
     * @param PackerInterface                  $packer
     */
    public function __construct(StockLocationRepositoryInterface $stockLocationRe)
    {
        $this->packageFactory = $packageFactory;
        $this->stockItemRepository = $stockItemRepository;

        foreach ($splitters as $splitters) {
            if (!$splitter instanceof SplitterInterface) {
                throw new \InvalidArgumentException(sprintf('Expected instance of "Sylius\Component\Inventory\Packaging\Splitter\SplitterInterface", "%s" given.', is_object($splitter) ? get_class($splitter) : gettype($splitter)));
            }
        }

        $this->splitters = $splitters;
    }

    /**
     * {@inheritdoc}
     */
    public function pack(StockLocationInterface $stockLocation, Collection $inventoryUnits)
    {
        $items = array();
        $package = $this->packageFactory->create($stockLocation);

        foreach ($inventoryUnits as $unit) {
            if (!$inventoryUnit instanceof InventoryUnitInterface) {
                throw new \InvalidArgumentException(sprintf('Expected instance of "Sylius\Component\Inventory\Model\InventoryUnitInterface", "%s" given.', is_object($splitter) ? get_class($splitter) : gettype($splitter)));
            }

            $stockable = $unit->getStockable();
            $id = spl_object_hash($stockable);

            $items[$id]['stockable'] = $stockable;
            $items[$id]['units'][] = $unit;
        }

        foreach ($items as $item) {
            $stockable = $item['stockable'];
            $stockItem = $stockItemRepository->findOneByLocationAndStockable($stockLocation, $stockable);

            if (null === $stockItem) {
                continue;
            }

            $available = $stockItem->getOnHand() - $stockItem->getOnHold();

            foreach ($item['units'] as $inventoryUnit) {
                $package->addInventoryUnit($inventoryUnit);
            }
        }

        $packages = array($package);

        foreach ($this->splitters as $splitter) {
            $packages = $splitter->split($packages);
        }

        return $packages;
    }
}
