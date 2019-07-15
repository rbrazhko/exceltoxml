<?php

namespace ExcelToXml\ToXml\Fua;

include_once 'FuaXmlTemplates.php';
include_once __DIR__ . '/../AbstractConverter.php';
include_once __DIR__ . '/../Mixing/CategoriesTrait.php';

use ExcelToXml\ToXml\AbstractConverter;
use ExcelToXml\ToXml\Mixing\CategoriesTrait;

class Converter extends AbstractConverter
{
    use CategoriesTrait;

    /** @var string */
    protected $companyName;

    /** @var string */
    protected $filename;

    /** @var array  */
    protected $generatedItemIds = [];

    public function __construct()
    {
        $this->filename = $_POST['filename'];
        $this->validate();
        $this->tmpFile = $_FILES['excel']['tmp_name'];
    }

    protected function validate()
    {
        if (!$this->filename) {
            throw new \Exception('Ошибка F.ua: Не указано поле "Имя файла"');
        }

        if ($_FILES['excel']['error']) {
            throw new \Exception('Ошибка ' . $_FILES['excel']['error'] .  ': Файл не загружен');
        }

        $tmpFile = $_FILES['excel']['tmp_name'];
        if (!$tmpFile || !is_file($tmpFile)) {
            throw new \Exception('Ошибка F.ua: Не удалось временно сохранить Excel файл');
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generateXml()
    {
        $categories = $this->parseFile('Категории');
        $categoriesXml = $this->generateCategoriesXml($categories);

        $goods = $this->parseFile('Товары');
        $xmlData = $this->generateXmlFile($goods, $categoriesXml);

        return $xmlData;
    }

    /**
     * @return string
     */
    public function getFinalFilename()
    {
        return $this->filename;
    }

    /**
     * @param array  $rows
     * @param string $categoriesXml
     *
     * @return string
     * @throws \Exception
     */
    protected function generateXmlFile($rows, $categoriesXml)
    {
        $columnNameToIndex = array_flip($rows[0]);

        $generalKeys = [
            'Артикул',
            'Название',
            'Бренд',
            'Категория №',
            'Цена',
            'Валюта (цена)',
            'Старая цена',
            'Валюта (старая цена)',
            'Наличие (шт)',
            'Ссылки на изображение',
            'Штрихкод товара',
            'partnumber',
            'Краткое описание товара',
            'Полное описание',
            'Официальная гарантия (1 или 0)',
            'Гарантия (мес)',
            'Метод доставки',
            'Тип доставки',
            'Имя доставки',
            'Примечание по доставке',
            'Цена доставки',
        ];

        $generalColumnsMapping = [];
        foreach ($columnNameToIndex as $columnName => $index) {
            if (!in_array($columnName, $generalKeys)) {
                continue;
            }
            $generalColumnsMapping[trim($columnName)] = $index;
        }
        if (!$generalColumnsMapping || count($generalKeys) !== count($generalColumnsMapping)) {
            throw new \Exception('Ошибка F.ua: Неверно указаны заголовки колонок. Необходимые колонки: ' . implode(', ', $generalKeys));
        }

        $paramsColumnsMapping = [];
        foreach ($columnNameToIndex as $columnName => $index) {
            if (in_array($columnName, $generalKeys)) {
                continue;
            }
            $paramsColumnsMapping[trim($columnName)] = $index;
        }

        $items = '';
        foreach ($rows as $number => $row) {
            if ($number == 0) {
                continue;
            }

            $items .= $this->generateItem($row, $generalColumnsMapping, $paramsColumnsMapping);
        }

        $xmlLayout = strtr(FuaXmlTemplates::getXmlLayoutTemplate(), [
            '[[CATEGORIES]]' => $categoriesXml,
            '[[ITEMS]]' => $items
        ]);

        return $xmlLayout;
    }

    protected function generateItem($row, $generalColumnsMapping, $paramsColumnsMapping)
    {
        $itemId = $row[$generalColumnsMapping['Артикул']];
        if (!$itemId) {
            return '';
        }
        if (in_array($itemId, $this->generatedItemIds)) {
            throw new \Exception('Ошибка F.ua: Товар с Артикул "' . $itemId . '"" уже существует');
        }
        $this->generatedItemIds[] = $itemId;

        $importantColumnsTags = [
            'Артикул' => 'art',
            'Название' => 'name',
            'Бренд' => 'vendor',
            'Категория №' => 'categoryId',
            'Цена' => 'price',
            'Валюта (цена)' => 'priceCurrency',
            'Старая цена' => 'old',
            'Валюта (старая цена)' => 'oldCurrency',
            'Наличие (шт)' => 'amount',
            'Штрихкод товара' => 'barcode',
            'partnumber' => 'partnumber',
            'Краткое описание товара' => 'description',
            'Полное описание' => 'fulldescription'
        ];

        $importantNodesXml = '';
        foreach ($importantColumnsTags as $columnName => $tag) {
            if ($row[$generalColumnsMapping[$columnName]] && $row[$generalColumnsMapping[$columnName]] == '---') {
               continue;
            }

            $importantNodesXml .= strtr(FuaXmlTemplates::getImportantNodeWithValue(), [
                '[[IMPORTANT_TAG_NAME]]' => $tag,
                '[[IMPORTANT_VALUE]]' => $this->wrapValue($row[$generalColumnsMapping[$columnName]]),
            ]);
        }

        $trimmedUrls = str_replace(', ', ',', trim($row[$generalColumnsMapping['Ссылки на изображение']]));
        $images = explode(',', $trimmedUrls);
        $imagesXml = '';
        foreach ($images as $index => $imageUrl) {
            if (!$imageUrl) {
                continue;
            }
            $imageTemplate = ($index === 0)
                ? FuaXmlTemplates::getImageTemplate()
                : FuaXmlTemplates::getExtraImageTemplate();

            $imagesXml .= strtr($imageTemplate, [
                '[[IMAGE_URL]]' => $imageUrl
            ]);
        }

        $paramsXml = '';
        foreach ($paramsColumnsMapping as $paramName => $index) {
            if ($row[$index] == '---') {
                continue;
            }
            $paramsXml .= strtr(FuaXmlTemplates::getParamTemplate(), [
                '[[PARAM_NAME]]' => $paramName,
                '[[PARAM_VALUE]]' => $this->wrapValue($row[$index]),
            ]);
        }

        return strtr(FuaXmlTemplates::getItemTemplate(), [
            '[[IMPORTANT_NODES]]' => $importantNodesXml,
            '[[IMAGES]]' => $imagesXml,
            '[[PARAMS]]' => $paramsXml,
            '[[WARRANTY_OFFICIAL]]' => $this->wrapValue($row[$generalColumnsMapping['Официальная гарантия (1 или 0)']]),
            '[[WARRANTY_MONTHS]]' => $this->wrapValue($row[$generalColumnsMapping['Гарантия (мес)']]),
            '[[DELIVERY_METHOD]]' => $this->wrapValue($row[$generalColumnsMapping['Метод доставки']]),
            '[[DELIVERY_NAME]]' => $this->wrapValue($row[$generalColumnsMapping['Имя доставки']]),
            '[[DELIVERY_TYPE]]' => $this->wrapValue($row[$generalColumnsMapping['Тип доставки']]),
            '[[DELIVERY_NOTE]]' => $this->wrapValue($row[$generalColumnsMapping['Примечание по доставке']]),
            '[[DELIVERY_PRICE]]' => $this->wrapValue($row[$generalColumnsMapping['Цена доставки']]),

        ]);
    }
}
