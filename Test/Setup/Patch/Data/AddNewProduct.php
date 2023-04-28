<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\State;

class AddNewProduct implements DataPatchInterface
{

    private State $state;
    private ProductInterfaceFactory $productFactory;
    private ProductRepositoryInterface $productRepository;
    private CategoryFactory $categoryFactory;

    public function __construct(
        State $state,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryFactory $categoryFactory
    )
    {
        $this->state = $state;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): void
    {
        $product = $this->productFactory->create();
        $this->state->setAreaCode('adminhtml');

        $product->setSku('test-product-sku');
        $product->setName('test-product-name');
        $product->setDescription('test-product-Description');
        $product->setPrice(99.99);
        $product->setAttributeSetId(4);
        $product->setStatus(Status::STATUS_ENABLED);
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setType(Type::TYPE_SIMPLE);

        $menCategory = $this->categoryFactory->create()->loadByAttribute('name', 'Men');
        $product->setCategoryIds([$menCategory->getId()]);

        $this->productRepository->save($product);
    }
}
