<?php declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;


use Magento\Framework\Setup\Patch\DataPatchInterface;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Api\CategoryLinkManagementInterface;


class CreateSimpleProduct implements DataPatchInterface
{
    protected State $appState;

    protected ModuleDataSetupInterface $setup;

    protected ProductInterfaceFactory $productInterfaceFactory;

    protected ProductRepositoryInterface $productRepository;

    protected EavSetup $eavSetup;

    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        EavSetup $eavSetup,
        State $appState
    ) {
        $this->appState = $appState;
        $this->productRepository = $productRepository;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->setup = $setup;
        $this->eavSetup = $eavSetup;
    }

    public function apply() {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    public function execute(): void {
        // create the product
        $product = $this->productInterfaceFactory->create();
        $sku = 'jeans-scandiweb';

        // check if the product already exists
        if ($product->getIdBySku($sku)) {
            return;
        }

        $this->setup->getConnection()->startSetup();

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        // set attributes
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setName('Jeans Scandiweb')
            ->setSku($sku)
            ->setUrlKey('jeansscandiweb')
            ->setPrice(20)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);

        // save the product to the repository
        $product = $this->productRepository->save($product);
        $objectManager = ObjectManager::getInstance();
        $categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
        $categoryLinkManagement->assignProductToCategories($product->getSku(), [2]);

        // finish setup
        $this->setup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '2.0.1';
    }


    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
