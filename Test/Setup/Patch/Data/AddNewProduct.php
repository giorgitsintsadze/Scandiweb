<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\State;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

class AddNewProduct implements DataPatchInterface
{
    protected State $appState;

    protected ProductInterfaceFactory $productFactory;

    protected ProductRepositoryInterface $productRepository;

    protected CategoryFactory $categoryFactory;

    protected SourceItemsSaveInterface $sourceItemsSave;

    protected SourceItemInterfaceFactory $sourceItemFactory;

    protected array $sourceItems;

    /**
     * @param State $appState
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryFactory $categoryFactory
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemInterfaceFactory $sourceItemFactory
     */
    public function __construct(
        State $appState,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryFactory $categoryFactory,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory
    )
    {
        $this->appState = $appState;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     * @throws ValidationException
     */
    public function execute(): void
    {
        $product = $this->productFactory->create();
        $menCategory = $this->categoryFactory->create()->loadByAttribute('name', 'Men');

        $product->setData(
            [
                'sku' => 'test-product-sku2',
                'name' => 'test-product-name2',
                'description' => 'test-product-Description2',
                'price' => 99.99,
                'attribute_set_id' => 4,
                'status' => Status::STATUS_ENABLED,
                'visibility' => Visibility::VISIBILITY_BOTH,
                'type_id' => Type::TYPE_SIMPLE,
                'category_ids' => [$menCategory->getId()],
            ]);

        $this->productRepository->save($product);

        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode('default');
        $sourceItem->setQuantity(100);
        $sourceItem->setSku($product->getSku());
        $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);
        $this->sourceItems[] = $sourceItem;

        $this->sourceItemsSave->execute($this->sourceItems);
    }
}
