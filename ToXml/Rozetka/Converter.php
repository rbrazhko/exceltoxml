<?php

namespace ExcelToXml\ToXml\Rozetka;

include_once 'RozetkaXmlTemplates.php';
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
    protected $brand;

    /** @var array  */
    protected $generatedOfferIds = [];

    public function __construct()
    {
        $this->companyName = $_POST['companyName'];
        $this->brand = $_POST['brand'];
        $this->validate();
        $this->tmpFile = $_FILES['excel']['tmp_name'];
    }

    protected function validate()
    {
        if (!$this->companyName) {
            throw new \Exception('Ошибка Rozetka: Не указано поле "Компания"');
        }

        if (!$this->brand) {
            throw new \Exception('Ошибка Rozetka: Не указано поле "Бренд"');
        }

        if ($_FILES['excel']['error']) {
            throw new \Exception('Ошибка ' . $_FILES['excel']['error'] .  ': Файл не загружен');
        }

        $tmpFile = $_FILES['excel']['tmp_name'];
        if (!$tmpFile || !is_file($tmpFile)) {
            throw new \Exception('Ошибка Rozetka: Не удалось временно сохранить Excel файл');
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
        return $this->brand;
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
            'Наименование',
            'URL',
            'Описание',
            'Состояние товара',
            'Старая цена',
            'Розничная цена',
            'Наличие (+ або -)',
            'Категория №',
            'Бренд',
            'Ссылки на изображение (более одной ссылки пишем через запятую)'
        ];

        $generalColumnsMapping = [];
        foreach ($columnNameToIndex as $columnName => $index) {
            if (!in_array($columnName, $generalKeys)) {
                continue;
            }
            $generalColumnsMapping[trim($columnName)] = $index;
        }
        if (!$generalColumnsMapping || count($generalKeys) !== count($generalColumnsMapping)) {
            throw new \Exception('Ошибка Rozetka: Неверно указаны заголовки колонок. Необходимые колонки: ' . implode(', ', $generalKeys));
        }

        $paramsColumnsMapping = [];
        foreach ($columnNameToIndex as $columnName => $index) {
            if (in_array($columnName, $generalKeys)) {
                continue;
            }
            $paramsColumnsMapping[trim($columnName)] = $index;
        }

        $offers = '';
        foreach ($rows as $number => $row) {
            if ($number == 0) {
                continue;
            }

            $offers .= $this->generateOffer($row, $generalColumnsMapping, $paramsColumnsMapping);
        }

        $xmlLayout = strtr(RozetkaXmlTemplates::getXmlLayoutTemplate(), [
            '[[DATE]]' => date('Y-m-d H:i'),
            '[[BRAND_NAME]]' => $this->brand,
            '[[COMPANY_NAME]]' => $this->companyName,
            '[[CATEGORIES]]' => $categoriesXml,
            '[[OFFERS]]' => $offers
        ]);

        return $xmlLayout;
    }

    protected function generateOffer($row, $generalColumnsMapping, $paramsColumnsMapping)
    {
        $offerId = $row[$generalColumnsMapping['Артикул']];
        if (!$offerId) {
            return '';
        }
        if (in_array($offerId, $this->generatedOfferIds)) {
            throw new \Exception('Ошибка Rozetka: Товар с Артикул "' . $offerId . '"" уже существует');
        }
        $this->generatedOfferIds[] = $offerId;

        $stateTemplate = '';
        if ($row[$generalColumnsMapping['Состояние товара']] && $row[$generalColumnsMapping['Состояние товара']] != '---') {
            $stateTemplate = strtr(RozetkaXmlTemplates::getStateTemplate(), [
                '[[STATE]]' => $row[$generalColumnsMapping['Состояние товара']]
            ]);
        }

        $priceOldTemplate = '';
        if ($row[$generalColumnsMapping['Старая цена']] && $row[$generalColumnsMapping['Старая цена']] != '---') {
            $priceOldTemplate = strtr(RozetkaXmlTemplates::getPriceOldTemplate(), [
                '[[PRICE_OLD]]' => $row[$generalColumnsMapping['Старая цена']]
            ]);
        }

        $trimmedUrls = str_replace(', ', ',', trim($row[$generalColumnsMapping['Ссылки на изображение (более одной ссылки пишем через запятую)']]));
        $pictures = explode(',', $trimmedUrls);
        $picturesXml = '';
        foreach ($pictures as $pictureUrl) {
            $picturesXml .= strtr(RozetkaXmlTemplates::getPicturesTemplate(), [
                '[[PICTURE_URL]]' => $pictureUrl
            ]);
        }

        $paramsXml = '';
        foreach ($paramsColumnsMapping as $paramName => $index) {
            if ($row[$index] == '---') {
                continue;
            }
            $paramsXml .= strtr(RozetkaXmlTemplates::getParamTemplate(), [
                '[[PARAM_NAME]]' => $paramName,
                '[[PARAM_VALUE]]' => $this->wrapValue($row[$index]),
            ]);
        }

        return strtr(RozetkaXmlTemplates::getOfferTemplate(), [
            '[[OFFER_ID]]' => $offerId,
            '[[AVAILABLE]]' => (trim($row[$generalColumnsMapping['Наличие (+ або -)']]) == '+') ? 'true' : 'false',
            '[[URL]]' => $this->wrapValue($row[$generalColumnsMapping['URL']]),
            '[[STATE_TEMPLATE]]' => $stateTemplate,
            '[[PRICE]]' => $this->wrapValue($row[$generalColumnsMapping['Розничная цена']]),
            '[[PRICE_OLD_TEMPLATE]]' => $priceOldTemplate,
            '[[CURRENCY_NAME]]' => 'UAH',
            '[[CATEGORY_ID]]' => $this->wrapValue($row[$generalColumnsMapping['Категория №']]),
            '[[PICTURES]]' => $picturesXml,
            '[[VENDOR]]' => $this->wrapValue($row[$generalColumnsMapping['Бренд']]),
            '[[OFFER_NAME]]' => $this->wrapValue($row[$generalColumnsMapping['Наименование']]),
            '[[DESCRIPTION]]' => $this->wrapValue($row[$generalColumnsMapping['Описание']]),
            '[[PARAMS]]' => $paramsXml,
        ]);
    }
}
