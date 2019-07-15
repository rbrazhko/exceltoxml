<?php

namespace ExcelToXml\ToXml;

abstract class AbstractConverter
{
    /** @var \SimpleXLSX */
    protected $xlsx;

    /** @var string */
    protected $tmpFile;

    /**
     * @throws \Exception
     */
    abstract protected function validate();

    /**
     * @return string
     */
    abstract public function generateXml();

    /**
     * @return string
     */
    abstract public function getFinalFilename();

    /**
     * @param string $pageName
     *
     * @return array
     * @throws \Exception
     */
    protected function parseFile($pageName)
    {
        if (!$this->xlsx) {
            if (!$xlsx = \SimpleXLSX::parse($this->tmpFile)) {
                throw new \Exception('Ошибка: невозможно извлечь данные из XLSX файла (' . \SimpleXLSX::parse_error() . ')');
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
     * @param $value
     *
     * @return string
     */
    protected function wrapValue($value) {
        if (strpos($value, '<![CDATA[') !== false) {
            return $value;
        }
        return htmlspecialchars($value);
    }
}