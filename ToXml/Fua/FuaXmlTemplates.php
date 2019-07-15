<?php

namespace ExcelToXml\ToXml\Fua;

class FuaXmlTemplates
{
    /**
     * @return string
     */
    public static function getXmlLayoutTemplate()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
    <price>
        <currencies>
            <currency id="USD" rate="26"/>
            <currency id="UAH" rate="1"/>
        </currencies>
        <categories>
            [[CATEGORIES]]
        </categories>
        <items>
            [[ITEMS]]
        </items>
    </price>';
    }

    /**
     * @return string
     */
    public static function getItemTemplate()
    {
        return '<item>
                    [[IMPORTANT_NODES]]
                    [[IMAGES]]
                    [[PARAMS]]
                    <warranty official="[[WARRANTY_OFFICIAL]]">[[WARRANTY_MONTHS]]</warranty>
                    <delivery method="[[DELIVERY_METHOD]]" type="[[DELIVERY_TYPE]]" name="[[DELIVERY_NAME]]" note="[[DELIVERY_NOTE]]">[[DELIVERY_PRICE]]</delivery>
                </item>
                ';
    }

    public static function getImportantNodeWithValue()
    {

        return '
                    <[[IMPORTANT_TAG_NAME]]>[[IMPORTANT_VALUE]]</[[IMPORTANT_TAG_NAME]]>';
    }

    /**
     * @return string
     */
    public static function getImageTemplate()
    {
        return '
                    <image>[[IMAGE_URL]]</image>';
    }

    /**
     * @return string
     */
    public static function getExtraImageTemplate()
    {
        return '
                    <extraimage>[[IMAGE_URL]]</extraimage>';

    }

    /**
     * @return string
     */
    public static function getParamTemplate()
    {
        return '
                    <param name="[[PARAM_NAME]]">[[PARAM_VALUE]]</param>';
    }
}
