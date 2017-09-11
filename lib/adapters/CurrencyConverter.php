<?php

namespace travelsoft\booking\adapters;

/**
 * Класс адаптер для валюты
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class CurrencyConverter {

    /**
     * @var \travelsoft\currency\Converter
     */
    protected $_converter;

    /**
     * @param int $courseId
     * @param array $commissions
     * @throws \Exception
     */
    public function __construct(int $courseId = null, array $commissions = null) {

        if (!\Bitrix\Main\Loader::includeModule('new.travelsoft.currency')) {

            throw new \Exception(get_called_class() . ': Module travelsoft.currency not found');
        }

        $this->_converter = \travelsoft\currency\Converter::getInstance();
        
        // инициализация валюты в соответствии с входными параметрами
        if ($courseId > 0) {
            
            $arCourse = current(\travelsoft\currency\stores\Courses::get(array("filter" => array("ID" => $courseId))));

            if ($arCourse['ID'] <= 0) {
                throw new \Exception(get_called_class() . ': Course not found');
            }

            $arCurrencies = \travelsoft\currency\stores\Currencies::get();
            $currency = new \travelsoft\currency\Currency(
                    (string) $arCurrencies[$arCourse["UF_BASE_ID"]]["UF_ISO"], intVal($arCourse["UF_BASE_ID"])
            );

            if (!empty($commissions)) {

                foreach ($arCurrencies as $arCurrency) {

                    $value = $arCourse["UF_" . $arCurrency["UF_ISO"]];
                    if (!$arCourse["UF_BASE_ID"] !== $arCurrency["ID"] && $commissions[$arCurrency["UF_ISO"]] > 0) {
                        # расчёт курса с комиссией
                        $value = $value + $value * ($commissions[$arCurrency["UF_ISO"]] / 100);
                    }

                    $currency->addCourse($arCurrency["UF_ISO"], new \travelsoft\currency\Course((float) $value, (string) $arCourse["UF_DATE"]));
                }
            } else {
                
                foreach ($arCurrencies as $arCurrency) {
                    $currency->addCourse($arCurrency["UF_ISO"], new \travelsoft\currency\Course((float) $arCourse["UF_" . $arCurrency["UF_ISO"]], (string) $arCourse["UF_DATE"]));
                }
            }
            
            $this->_converter->setCurrency($currency);
            $this->_converter->setCrossCourse($currency);
            $this->_converter->setDefaultConversionISO($currency->ISO);
            $this->_converter->setDecimal(\travelsoft\currency\Settings::formatDecimal());
            $this->_converter->setDecPoint(\travelsoft\currency\Settings::formatDecPoint());
            $this->_converter->setSSep(\travelsoft\currency\Settings::formatSSep());
            
        } elseif (strlen($this->_converter->getDefaultCurrencyIso()) <= 0) {
            
            // инициализация валюты в соответсвии с настройками модуля
            $this->_converter->initDefault();
        }
    }

    /**
     * Конвертирует цену в текущую валюту сайта
     * @param float $price
     * @param string $iso
     * @param string $isoOut
     * @return float
     */
    public function convert(float $price, string $iso, string $isoOut = null): float {

        if ($isoOut) {

            $result = $this->_converter->convert($price, $iso, $isoOut)->getResultLikeArray();
        } else {

            $result = $this->_converter->convert($price, $iso)->getResultLikeArray();
        }

        return $result['price'];
    }

    /**
     * Возвращает iso текущей валюты приложения
     * @return string
     */
    public function getCurrentCurrencyIso(): string {

        return $this->_converter->getDefaultCurrencyIso();
    }

    /**
     * Возвращает отформатированную цену
     * @param float $price
     * @param string $iso
     * @return string
     */
    public function getFormatted(float $price, string $iso): string {

        return $this->_converter->format($price, $iso);
    }

    /**
     * Возвращает отформатированное число
     * @param float $price
     * @return string
     */
    public function format(float $price): string {

        return $this->_converter->formatFloatValue($price);
    }

    /**
     * Возвращает список доступных валют
     * @return array
     */
    public function getListOfCurrency(): array {

        $arCurrencies = \travelsoft\currency\stores\Currencies::get();

        $arResult = array();
        foreach ($arCurrencies as $arCurrency) {

            $arResult[] = $arCurrency['UF_ISO'];
        }

        return $arResult;
    }

    /**
     * Возвращает текущий id курса в системе
     * @return int
     */
    public function getCurrentCourseId(): int {

        return \travelsoft\currency\Settings::currentCourseId();
    }

    /**
     * Текущая коммиссия
     * @return array
     */
    public static function getCommissions(): array {
        return \travelsoft\currency\Settings::commissions();
    }

}
