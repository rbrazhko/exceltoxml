<?php

namespace ExcelToXml\ToXml;

include_once 'AbstractConverter.php';
include_once 'Rozetka/Converter.php';
include_once 'Fua/Converter.php';

use ExcelToXml\ToXml\Rozetka\Converter as RozetkaConverter;
use ExcelToXml\ToXml\Fua\Converter as FuaConverter;

class GenerateXmlFile
{
    /** @var \ExcelToXml\ToXml\AbstractConverter */
    protected $converter;

    /**
     * @var string
     */
    protected $filename;

    /**
     * GenerateXmlFile constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        switch ($_POST['converter']) {
            case 'rozetka':
                $this->converter = new RozetkaConverter();
                break;
            case 'f-ua':
                $this->converter = new FuaConverter();
                break;
            default:
                throw new \Exception('Ошибка: Конвертер Excel в XML не найден');
        }

        $this->filename = $this->converter->getFinalFilename();
    }

    public function generate()
    {
        $xmlData = $this->converter->generateXml();

        header('Content-Disposition: attachment; filename="' . $this->filename . '.xml"');
        header('Content-type: text/xml; charset="utf8"');
        echo $xmlData;

        header("Expires: 0");
        die;
    }
}
