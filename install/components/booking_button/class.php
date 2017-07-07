<?php

/**
 * Класс кнопка фофрмления заказа
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class TravelsoftBookingButton extends CBitrixComponent {

    /**
     * make url for make order page
     */
    protected function _makeLink() {

        $this->arResult['BOOKING_REQUEST'] = array();

        if (is_array($this->arParams['BOOKING_REQUEST']) && !empty($this->arParams['BOOKING_REQUEST'])) {

            foreach ($this->arParams['BOOKING_REQUEST'] as $k => $v) {

                if (is_array($v)) {

                    foreach ($v as $kk => $vv) {

                        $this->arResult['BOOKING_REQUEST'][] = 'BOOKING_REQUEST[' . $k . '][' . $kk . ']=' . $vv;
                    }
                } else {

                    $this->arResult['BOOKING_REQUEST'][] = 'BOOKING_REQUEST[' . $k . ']=' . $v;
                }
            }
        }

        $this->arResult['LINK'] = '';

        if (strlen($this->arParams['BOOKING_URL']) > 0) {

            $this->arResult['LINK'] = $this->arParams['BOOKING_URL'];
        }

        if (!empty($this->arResult['BOOKING_REQUEST'])) {

            $this->arResult['LINK'] .= '?' . implode('&', $this->arResult['BOOKING_REQUEST']);
        }
    }

    /**
     * make custom stylesheet button
     */
    protected function _makeStylesheet() {

        $this->arResult['STYLE'] = NULL;

        if (strlen($this->arParams['STYLE']) > 0) {

            $this->arResult['STYLE'] = $this->arParams['STYLE'];
        }
    }

    /**
     * component body
     */
    public function executeComponent() {

        try {

            $this->_makeLink();

            $this->_makeStylesheet();

            if ($this->arParams['USE_FRAME_MODE'] == 'Y') {

                CJSCore::Init(array('ajax', 'popup'));
            }

            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}
