<?php
include_once 'XmlTemplates.php';

class GenerateXmlFile
{
    /** @var SimpleXLSX */
    protected $xlsx;

    /** @var string */
    protected $companyName;

    /** @var string */
    protected $brand;

    /** @var string */
    protected $tmpFile;

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
            throw new \Exception('Ошибка : Не указано поле "Компания"');
        }

        if (!$this->brand) {
            throw new \Exception('Ошибка : Не указано поле "Бренд"');
        }

        if ($_FILES['excel']['error']) {
            throw new \Exception('Ошибка ' . $_FILES['excel']['error'] .  ': Файл не загружен');
        }

        $tmpFile = $_FILES['excel']['tmp_name'];
        if (!$tmpFile || !is_file($tmpFile)) {
            throw new \Exception('Ошибка: Не удалось временно сохранить Excel файл');
        }
    }

    public function generate()
    {
        $categories = $this->parseFile('Категории');
        $categoriesXml = $this->generateCategoriesXml($categories);

        $goods = $this->parseFile('Товары');
        $xmlData = $this->generateXmlFile($goods, $categoriesXml);

        header('Content-Disposition: attachment; filename="' . $this->brand . '.xml"');
        header('Content-type: text/xml; charset="utf8"');
        echo $xmlData;
        // if you want to directly download then set expires time
        header("Expires: 0");
        die;
    }

    /**
     * @param string $pageName
     *
     * @return array
     * @throws Exception
     */
    protected function parseFile($pageName)
    {
        if (!$this->xlsx) {
            if (!$xlsx = SimpleXLSX::parse($this->tmpFile)) {
                throw new \Exception('Ошибка: невозможно извлечь данные из XLSX файла (' . SimpleXLSX::parse_error() . ')');
            }

            $this->xlsx = $xlsx;
        }

        $pageNumber = $this->xlsx->getSheetKeyByName($pageName);
        if ($pageNumber === false) {
            throw new \Exception('Ошибка: в Excel файле отсутствует страница "' . $pageName . '"');
        }

        $result = $this->xlsx->rows($pageNumber);
        return $result;
    }

    /**
     * @param array  $rows
     *
     * @return string
     * @throws Exception
     */
    protected function generateCategoriesXml($rows)
    {
        $columnNameToIndex = array_flip($rows[0]);

        $generalKeys = [
            '№',
            'Категория'
        ];

        $generalColumnsMapping = [];
        foreach ($columnNameToIndex as $columnName => $index) {
            if (!in_array($columnName, $generalKeys)) {
                continue;
            }
            $generalColumnsMapping[trim($columnName)] = $index;
        }
        if (!$generalColumnsMapping || count($generalKeys) !== count($generalColumnsMapping)) {
            throw new \Exception('Ошибка: Неверно указаны заголовки колонок. Необходимые колонки: ' . implode(', ', $generalKeys));
        }

        $categoriesXml = '';
        foreach ($rows as $number => $row) {
            if ($number == 0) {
                continue;
            }

            $categoryId = $this->wrapValue($row[$generalColumnsMapping['№']]);
            $categoryName = $this->wrapValue($row[$generalColumnsMapping['Категория']]);
            if (!$categoryId && !$categoryName) {
                continue;
            }

            $categoriesXml .= strtr(XmlTemplates::getCategoryTemplate(), [
                '[[CATEGORY_ID]]' => $categoryId,
                '[[CATEGORY_NAME]]' => $categoryName
            ]);
        }

        return $categoriesXml;
    }

    /**
     * @param array  $rows
     * @param string $categoriesXml
     *
     * @return string
     * @throws Exception
     */
    protected function generateXmlFile($rows, $categoriesXml)
    {
        $columnNameToIndex = array_flip($rows[0]);

        $generalKeys = [
            'Артикул',
            'Наименование',
            'URL',
            'Описание',
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
            throw new \Exception('Ошибка: Неверно указаны заголовки колонок. Необходимые колонки: ' . implode(', ', $generalKeys));
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

        $xmlLayout = strtr(XmlTemplates::getXmlLayoutTemplate(), [
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
            throw new \Exception('Ошибка: Товар с Артикул "' . $offerId . '"" уже существует');
        }
        $this->generatedOfferIds[] = $offerId;

        $trimmedUrls = str_replace(', ', ',', trim($row[$generalColumnsMapping['Ссылки на изображение (более одной ссылки пишем через запятую)']]));
        $pictures = explode(',', $trimmedUrls);
        $picturesXml = '';
        foreach ($pictures as $pictureUrl) {
            $picturesXml .= strtr(XmlTemplates::getPicturesTemplate(), [
                '[[PICTURE_URL]]' => $pictureUrl
            ]);
        }

        $paramsXml = '';
        foreach ($paramsColumnsMapping as $paramName => $index) {
            if ($row[$index] == '---') {
                continue;
            }
            $paramsXml .= strtr(XmlTemplates::getParamTemplate(), [
                '[[PARAM_NAME]]' => $paramName,
                '[[PARAM_VALUE]]' => $this->wrapValue($row[$index]),
            ]);
        }

        return strtr(XmlTemplates::getOfferTemplate(), [
            '[[OFFER_ID]]' => $offerId,
            '[[AVAILABLE]]' => (trim($row[$generalColumnsMapping['Наличие (+ або -)']]) == '+') ? 'true' : 'false',
            '[[URL]]' => $this->wrapValue($row[$generalColumnsMapping['URL']]),
            '[[PRICE]]' => $this->wrapValue($row[$generalColumnsMapping['Розничная цена']]),
            '[[CURRENCY_NAME]]' => 'UAH',
            '[[CATEGORY_ID]]' => $this->wrapValue($row[$generalColumnsMapping['Категория №']]),
            '[[PICTURES]]' => $picturesXml,
            '[[VENDOR]]' => $this->wrapValue($row[$generalColumnsMapping['Бренд']]),
            '[[OFFER_NAME]]' => $this->wrapValue($row[$generalColumnsMapping['Наименование']]),
            '[[DESCRIPTION]]' => $this->wrapValue($row[$generalColumnsMapping['Описание']]),
            '[[PARAMS]]' => $paramsXml,
        ]);
    }

    protected function wrapValue($value) {
        if (strpos($value, '<![CDATA[') !== false) {
            return $value;
        }
        return htmlspecialchars($value);
    }
}