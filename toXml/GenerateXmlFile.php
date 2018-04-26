<?php
include_once 'XmlTemplates.php';

class GenerateXmlFile
{
    /** @var string */
    protected $brand;

    /** @var string */
    protected $tmpFile;

    public function __construct()
    {
        $this->brand = $_POST['brand'];
        $this->validate();
        $this->tmpFile = $_FILES['excel']['tmp_name'];
    }

    protected function validate()
    {
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
        $rows = $this->parseFile();
        $xmlData = $this->generateXmlFile($rows);

        header('Content-Disposition: attachment; filename="' . $this->brand . '.xml"');
        header('Content-type: text/xml; charset="utf8"');
        echo $xmlData;
        // if you want to directly download then set expires time
        header("Expires: 0");
        die;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function parseFile()
    {
        if ($xlsx = SimpleXLSX::parse($this->tmpFile)) {
            $result = $xlsx->rows();
        } else {
            throw new \Exception('Ошибка: невозможно извлечь данные из XLSX файла (' . SimpleXLSX::parse_error() . ')');
        }
        return $result;
    }

    /**
     * @param array $rows
     *
     * @return string
     */
    protected function generateXmlFile($rows)
    {
        $columnNameToIndex = array_flip($rows[0]);

        $generalKeys = [
            'Артикул',
            'Наименование',
            'Описание',
            'Розничная цена',
            'Наличие (+ або -)',
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
            '[[COMPANY]]' => 'Smuzi Market',
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

        $trimmedUrls = str_replace(', ', ',', trim($row[$paramsColumnsMapping['Ссылки на изображение (более одной ссылки пишем через запятую)']]));
        $pictures = explode(',', $trimmedUrls);
        $picturesXml = '';
        foreach ($pictures as $pictureUrl) {
            $picturesXml .= strtr(XmlTemplates::getPicturesTemplate(), [
                '[[PICTURE_URL]]' => $pictureUrl
            ]);
        }

        $paramsXml = '';
        foreach ($paramsColumnsMapping as $paramName => $index) {
            $paramsXml .= strtr(XmlTemplates::getParamTemplate(), [
                '[[PARAM_NAME]]' => $paramName,
                '[[PARAM_VALUE]]' => $this->wrapValue($row[$index]),
            ]);
        }

        return strtr(XmlTemplates::getOfferTemplate(), [
            '[[OFFER_ID]]' => $offerId,
            '[[AVAILABLE]]' => (trim($row[$generalColumnsMapping['Наличие (+ або -)']]) == '+') ? true : false,
            '[[URL]]' => $this->wrapValue($row[$generalColumnsMapping['URL']]),
            '[[PRICE]]' => $this->wrapValue($row[$generalColumnsMapping['Розничная цена']]),
            '[[CURRENCY_NAME]]' => 'UAH',
            '[[CATEGORY_ID]]' => '1',
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