<?php

use Bitrix\Main\ModuleManager;

class travelsoft_booking extends CModule {

    public $MODULE_ID = "travelsoft.booking";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "N";
    protected $namespaceFolder = "travelsoft";
    protected $adminFiles = array(
        "travelsoft_crm_booking_add_prices.php",
        "travelsoft_crm_booking_agents.php",
        "travelsoft_crm_booking_clients.php",
        "travelsoft_crm_booking_documents.php",
        "travelsoft_crm_booking_orders.php"
    );

    function __construct() {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = "Модуль бронирования туристических услуг";
        $this->MODULE_DESCRIPTION = "Модуль бронирования туристических услуг";
        $this->PARTNER_NAME = "dimabresky";
        $this->PARTNER_URI = "https://github.com/dimabresky/";
    }

    public function copyFiles() {

        foreach ($this->adminFiles as $fileName) {

            @copy($_SERVER["DOCUMENT_ROOT"] . "/local/modules/travelsoft.booking/install/admin/" . $fileName, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $fileName);
        }
    }

    public function deleteFiles() {

        foreach ($this->adminFiles as $fileName) {

            @unlink($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $fileName);
        }
    }

    public function DoInstall() {
        try {

            if (!ModuleManager::isModuleInstalled("iblock")) {
                throw new Exception("Для работы модуля необходим модуль инфоблока");
            }

            if (!ModuleManager::isModuleInstalled("highloadblock")) {
                throw new Exception("Для работы модуля необходим модуль highloadblock");
            }

            $this->copyFiles();

            // register module 
            RegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\travelsoft\\booking\\EventsHandlers", "addGlobalAdminMenuItem");

            // register module
            ModuleManager::registerModule($this->MODULE_ID);

            return true;
        } catch (Exception $ex) {
            $GLOBALS["APPLICATION"]->ThrowException($ex->getMessage());
            $this->DoUninstall();
            return false;
        }

        return true;
    }

    public function DoUninstall() {

        $this->deleteFiles();

        // unregister module dependecies
        UnRegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\travelsoft\\booking\\EventsHandlers", "addGlobalAdminMenuItem");

        // delete options
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'TOURS_IB'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'FOOD_IB'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'ORDERS_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'CITIZENSHIP_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'PRICE_TYPES_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'PRICES_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'QUOTAS_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'STATUSES_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'TOURISTS_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'CRMSETTINGS_HL'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'MANAGERS_USER_GROUPS'));
        Bitrix\Main\Config\Option::delete($this->MODULE_ID, array('name' => 'AGENTS_USER_GROUPS'));

        // unregister module
        ModuleManager::UnRegisterModule($this->MODULE_ID);

        return true;
    }

}
