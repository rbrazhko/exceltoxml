<?php

namespace ExcelToXml\ToXml\Rozetka;

class RozetkaXmlTemplates
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
            <company>[[COMPANY_NAME]]</company>
            <currencies>
                <currency id="UAH" rate="1"/>
            </currencies>
            <categories>
                [[CATEGORIES]]
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
                    [[STATE_TEMPLATE]]
                    <price>[[PRICE]]</price>
                    [[PRICE_OLD_TEMPLATE]]
                    <stock_quantity>99</stock_quantity>
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
    public static function getStateTemplate()
    {
        return '
                    <state>[[STATE]]</state>';

    }

    /**
     * @return string
     */
    public static function getPriceOldTemplate()
    {
        return '
                    <price_old>[[PRICE_OLD]]</price_old>';

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
