<?php

namespace Custom\Catalog\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Helper extends AbstractHelper
{
    protected $productFactory;
    protected $attributeSet;
    protected $linkManagement;
    protected $storeConfig;
    protected $currencyCode;
    protected $blockFactory;
    protected $optionFactory;
    protected $_attributeOptionCollection;
    protected $resourceModelEavAttribute;
    protected $productAttributeRepository;
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement,
        \Magento\Store\Model\StoreManagerInterface $storeConfig,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $resourceModelEavAttribute,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->attributeSet = $attributeSet;
        $this->linkManagement = $linkManagement;
        $this->storeConfig = $storeConfig;
        $this->currencyCode = $currencyFactory->create();
        $this->blockFactory = $blockFactory;
        $this->optionFactory = $optionFactory;
        $this->_attributeOptionCollection = $attributeOptionCollection;
        $this->resourceModelEavAttribute = $resourceModelEavAttribute;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->collectionFactory = $collectionFactory;

        parent::__construct(
            $context
        );
    }

    public function getCategoryByName($name) {
        $collection = $this->collectionFactory
            ->create()
            ->addAttributeToFilter('name', $name)
            ->setPageSize(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return false;
    }

    public function getProductById($id) {
        return $this->productFactory->create()->load($id);
    }

    public function getAttributeText($product, $code) {
        return $product->getResource()->getAttribute($code)->getFrontend()->getValue($product);
    }

    public function getOptionText($optionId) {
        $optionFactory = $this->optionFactory->create();
        $optionFactory->load($optionId);
        $attributeId = $optionFactory->getAttributeId();
        $attr = $this->resourceModelEavAttribute->load($attributeId);
        if ($attr->usesSource()) {
            $optionText = $attr->getSource()->getOptionText($optionId);
            return $optionText;
        }
    }

    public function getOptionIdByText($code, $text) {
        $attribute = $this->productAttributeRepository->get($code);
        return $attribute->getSource()->getOptionId($text);
    }

    public function getAttributeSetName($product) {
        $attributeSetRepository = $this->attributeSet->get($product->getAttributeSetId());
        return $attributeSetRepository->getAttributeSetName();
    }

    function getPriceHtml($product) {
        $symbol = $this->getCurrencySymbol();
        $code = $this->getCurrencyCode();
        $priceHtml = '<span>'.$symbol.$product->getPriceInfo()->getPrice('final_price')->getValue().' '.$code.'</span>';
    }

    public function getCurrencyCode() {
        return $this->storeConfig->getStore()->getCurrentCurrencyCode();
    }

    public function getCurrencySymbol() {
        $currentCurrency = $this->storeConfig->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currency->getCurrencySymbol();
    }

    public function getProductImageUrl($product) {
        $imageBlock = $this->blockFactory->createBlock('Magento\Catalog\Block\Product\ListProduct');
        $productImage = $imageBlock->getImage($product, 'product_page_image_medium');
        $imageUrl = $productImage->getImageUrl();
        return $imageUrl;
    }
}
