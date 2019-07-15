<?php

namespace ExcelToXml\ToXml\Mixing;

trait CategoriesTrait
{
    /**
     * @param array $rows
     *
     * @return string
     * @throws \Exception
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
            throw new \Exception('Ошибка Категорий: Неверно указаны заголовки колонок. Необходимые колонки: ' . implode(', ', $generalKeys));
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

            $categoriesXml .= strtr(self::getCategoryTemplate(), [
                '[[CATEGORY_ID]]' => $categoryId,
                '[[CATEGORY_NAME]]' => $categoryName
            ]);
        }

        return $categoriesXml;
    }

    /**
     * @return string
     */
    protected static function getCategoryTemplate()
    {
        return '
                <category id="[[CATEGORY_ID]]">[[CATEGORY_NAME]]</category>';
    }
}
