<?php

$classes = array(
    "travelsoft\\booking\\abstraction\\Getter" => "lib/abstraction/Getter.php",
    "travelsoft\\booking\\abstraction\\Store" => "lib/abstraction/Store.php",
    "travelsoft\\booking\\abstraction\\Cost" => "lib/abstraction/Cost.php",
    "travelsoft\\booking\\abstraction\\SearchEngine" => "lib/abstraction/SearchEngine.php",
    "travelsoft\\booking\\adapters\\Highloadblock" => "lib/adapters/Highloadblock.php",
    "travelsoft\\booking\\adapters\\Iblock" => "lib/adapters/Iblock.php",
    "travelsoft\\booking\\adapters\\CurrencyConverter" => "lib/adapters/CurrencyConverter.php",
    "travelsoft\\booking\\adapters\\Date" => "lib/adapters/Date.php",
    "travelsoft\\booking\\stores\\Citizenship" => "lib/stores/Citizenship.php",
    "travelsoft\\booking\\stores\\Food" => "lib/stores/Food.php",
    "travelsoft\\booking\\stores\\Orders" => "lib/stores/Orders.php",
    "travelsoft\\booking\\stores\\PriceTypes" => "lib/stores/PriceTypes.php",
    "travelsoft\\booking\\stores\\Prices" => "lib/stores/Prices.php",
    "travelsoft\\booking\\stores\\Quotas" => "lib/stores/Quotas.php",
    "travelsoft\\booking\\stores\\Statuses" => "lib/stores/Statuses.php",
    "travelsoft\\booking\\stores\\Tourists" => "lib/stores/Tourists.php",
    "travelsoft\\booking\\stores\\Tours" => "lib/stores/Tours.php",
    "travelsoft\\booking\\Settings" => "lib/Settings.php",
    "travelsoft\\booking\\EventsHandlers" => "lib/EventsHandlers.php",
    "travelsoft\\booking\\tours\\Cost" => "lib/tours/Cost.php",
    "travelsoft\\booking\\tours\\SearchEngine" => "lib/tours/SearchEngine.php"
);

if (ADMIN_SECTION === true) {

    $classes["travelsoft\\booking\\stores\crm\Settings"] = "lib/stores/crm/Settings.php";
    require_once 'lib/crm_functions.php';
}

CModule::AddAutoloadClasses("travelsoft.booking", $classes);

require_once 'lib/functions.php';
