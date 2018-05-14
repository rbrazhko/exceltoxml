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
                <category id="5">Автотовары</category>
                <category id="6">Красота, здоровье, уход</category>
                <category id="7">Товары для дома</category>
                <category id="8">Электроника</category>
                <category id="9">Игрушки для детей</category>
                <category id="10">Спорт, Здоровье, Туризм</category>
                <category id="11">Всё для ремонта, инструменты</category>
                <category id="12">TV Shop товары</category>
                <category id="13">Кошельки и клатчи</category>
                <category id="14">Гаджеты и подарки</category>
                <category id="15">Метеостанции и барометры</category>
                <category id="16">Уход и хранение одежды и обуви</category>
                <category id="17">Садовый инвентарь</category>
                <category id="18">Электрические проточные водонагреватели</category>
                <category id="19">Шведские стенки и турники</category>
                <category id="20">Шланги</category>
                <category id="21">Ночники</category>
                <category id="22">Аксессуары для дверей</category>
                <category id="23">Кофеварки</category>
                <category id="24">Рюкзаки</category>
                <category id="25">Уличное освещение</category>
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
