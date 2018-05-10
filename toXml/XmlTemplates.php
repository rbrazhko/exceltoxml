<?php
class XmlTemplates
{
    /**
     * @return string
     */
    public static function getXmlLayoutTemplate()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
    <!DOCTYPE yml_catalog SYSTEM "shops.dtd">
    <yml_catalog date="[[DATE]]">
        <shop>
            <name>[[BRAND_NAME]]</name>
            <company>[[COMPANY]]</company>
            <currencies>
                <currency id="UAH" rate="1"/>
            </currencies>
            <categories>
                <category id="1">Мобильные телефоны</category>
                <category id="2">Аксессуары для мобильных телефонов и смартфонов</category>
                <category id="3">Компьютеры и ноутбуки</category>
                <category id="4">Медиаплееры</category>
            </categories>
            <offers>
                [[OFFERS]]
            </offers>
        </shop>
    </yml_catalog>';
    }

    /**
     * @return string
     */
    public static function getOfferTemplate()
    {
        return '<offer id="[[OFFER_ID]]" available="[[AVAILABLE]]">
                    <url>
                        [[URL]]
                    </url>
                    <price>[[PRICE]]</price>
                    <currencyId>[[CURRENCY_NAME]]</currencyId>
                    <categoryId>[[CATEGORY_ID]]</categoryId>
                    [[PICTURES]]
                    <vendor>[[VENDOR]]</vendor>
                    <name>
                        [[OFFER_NAME]]
                    </name>
                    <description>[[DESCRIPTION]]</description>
                    [[PARAMS]]
                </offer>
                ';
    }

    /**
     * @return string
     */
    public static function getPicturesTemplate()
    {
        return '
                    <picture>[[PICTURE_URL]]</picture>';

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
