<?php

$classes = array(
    "travelsoft\\booking\\abstraction\\Store" => "lib/abstraction/Store.php",
    "travelsoft\\booking\\abstraction\\Entity" => "lib/abstraction/Entity.php",
    "travelsoft\\booking\\abstraction\\Cost" => "lib/abstraction/Cost.php",
    "travelsoft\\booking\\abstraction\\SearchEngine" => "lib/abstraction/SearchEngine.php",
    "travelsoft\\booking\\adapters\\Highloadblock" => "lib/adapters/Highloadblock.php",
    "travelsoft\\booking\\adapters\\Iblock" => "lib/adapters/Iblock.php",
    "travelsoft\\booking\\adapters\\Mail" => "lib/adapters/Mail.php",
    "travelsoft\\booking\\adapters\\MSDocAdapter" => "lib/adapters/MSDocAdapter.php",
    "travelsoft\\booking\\adapters\\CurrencyConverter" => "lib/adapters/CurrencyConverter.php",
    "travelsoft\\booking\\adapters\\Date" => "lib/adapters/Date.php",
    "travelsoft\\booking\\stores\\Citizenship" => "lib/stores/Citizenship.php",
    "travelsoft\\booking\\stores\\Documents" => "lib/stores/Documents.php",
    "travelsoft\\booking\\stores\\Food" => "lib/stores/Food.php",
    "travelsoft\\booking\\stores\\Orders" => "lib/stores/Orders.php",
    "travelsoft\\booking\\stores\\PriceTypes" => "lib/stores/PriceTypes.php",
    "travelsoft\\booking\\stores\\Prices" => "lib/stores/Prices.php",
    "travelsoft\\booking\\stores\\Quotas" => "lib/stores/Quotas.php",
    "travelsoft\\booking\\stores\\Statuses" => "lib/stores/Statuses.php",
    "travelsoft\\booking\\stores\\Tourists" => "lib/stores/Tourists.php",
    "travelsoft\\booking\\stores\\Tours" => "lib/stores/Tours.php",
    "travelsoft\\booking\\stores\\Duration" => "lib/stores/Duration.php",
    "travelsoft\\booking\\stores\\PaymentsTypes" => "lib/stores/PaymentsTypes.php",
    "travelsoft\\booking\\stores\\PaymentHistory" => "lib/stores/PaymentHistory.php",
    "travelsoft\\booking\\stores\\Users" => "lib/stores/Users.php",
    "travelsoft\\booking\\Settings" => "lib/Settings.php",
    "travelsoft\\booking\\EventsHandlers" => "lib/EventsHandlers.php",
    "travelsoft\\booking\\tours\\Cost" => "lib/tours/Cost.php",
    "travelsoft\\booking\\tours\\SearchEngine" => "lib/tours/SearchEngine.php",
    "travelsoft\\booking\\Utils" => 'lib/Utils.php',
    
    // doccreators
    "travelsoft\\booking\\doccreators\\Repository" => 'lib/doccreators/Repository.php',
    "travelsoft\\booking\\doccreators\\TemplateProcessor" => 'lib/doccreators/TemplateProcessor.php',
    "travelsoft\\booking\\doccreators\\types\\AbstractType" => "lib/doccreators/AbstractType.php",
    "travelsoft\\booking\\doccreators\\types\\Factory" => "lib/doccreators/types/Factory.php",
    "travelsoft\\booking\\doccreators\\types\\AbstractType" => "lib/doccreators/types/AbstractType.php",
    "travelsoft\\booking\\doccreators\\types\\Docx" => "lib/doccreators/types/Docx.php",
    "travelsoft\\booking\\doccreators\\bus\\ContractFactory" => "lib/doccreators/bus/ContractFactory.php"
);

if (ADMIN_SECTION === true) {

    $classes["travelsoft\\booking\\crm\\stores\\Settings"] = "lib/crm/stores/Settings.php";
    $classes["travelsoft\\booking\\crm\\Utils"] = "lib/crm/Utils.php";
    $classes["travelsoft\\booking\\crm\\stores\\CashDesks"] = "lib/crm/stores/CashDesks.php";
    $classes["travelsoft\\booking\\crm\\Settings"] = "lib/crm/Settings.php";
}

CModule::AddAutoloadClasses("travelsoft.booking", $classes);
