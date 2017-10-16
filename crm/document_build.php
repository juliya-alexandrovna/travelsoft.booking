<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Documents;
use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Users;
use travelsoft\booking\adapters\CurrencyConverter;

require_once 'header.php';

function attemptSendErros (array $errors) {
    if (!empty($errors)) {
        throw new \Exception(implode('<br>', $errors));
    }
}

try {
    
    $errors = array();
    
    if (0 >= $_GET['ORDER_ID']) {
        $errors[] = 'Не указан id путевки';
    }
    
    if (0 >= $_GET['DOC_TPL_ID']) {
        $errors[] = 'Не указан id шаблона документа';
    }
    
    attemptSendErros($errors);
    
    $order = Orders::getById((int)$_GET['ORDER_ID']);
    
    if (!$order['ID']) {
        $errors[] = 'Путевка с ID="'+$order['ID']+'" не найдена';
    }
    
    $docTpl = Documents::getById((int)$_GET['DOC_TPL_ID']);
    
    if (!$docTpl['ID'] || !$docTpl['UF_TPL']) {
        $errors[] = 'Шаблон документа с ID="'+$docTpl['ID']+'" не найден';
    }
    
    attemptSendErros($errors);
    
    $client = current(Users::get(array('filter' => array('ID' => $order['UF_USER_ID']), 'select' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHONE', 'UF_PASS_NUMBER', 'EMAIL'))));
    
    if (!$client['ID']) {
        $errors[] = 'Клиент не найден в системе';
    }
    
    dm($order);
    
    attemptSendErros($errors);
    
    /**
     * Подготовка переменных шаблона
     * 
     * Описание переменныx:
     * 
     * #NUMBER# - номер договора (путевки)
     * #DATE_FROM# - дата начала тура
     * #DATE_TO# - дата дата окончания тура
     * #CLIENT_NAME# - имя и фамилия клиента
     * #CLIENT_LAT_NAME# - имя и фамилия клиента латиницей
     * #COST# - полная стоимость путевки
     * #PAY_DATE# - дата оплаты пуевки
     * #BIRTHDATE# - день рождение клиента
     * #PASSPORT# - серия и номер паспорта
     * #ADDRESS# - адрес клиента
     * #PHONE# - телефон
     * #DATE_CREATE# - дата создания
     */
    
    $vars[0][0] = "#NUMBER#";
    $vars[1][0] = $order['ID'];
    
    $vars[0][1] = "#DATE_FROM#";
    $vars[1][1] = $order['UF_DATE_FROM']->toString();
    
    $vars[0][2] = "#DATE_TO#";
    $vars[1][2] = $order['UF_DATE_TO']->toString();
    
    $vars[0][2] = "#CLIENT_NAME#";
    $vars[1][2] = $client['FULL_NAME'];
    
    $vars[0][2] = "#CLIENT_LAT_NAME#";
    $vars[1][2] = "";
    
    $vars[0][2] = "#COST#";
    $vars[1][2] = "";
    
//    $vars[0][1] = "#CLIENT_NAME#"
    
    /**
     * Формирование документа
     */
    
    
    
} catch (\Exception $e) {
    
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    CAdminMessage::ShowMessage(array('MESSAGE' => $e->getMessage(), 'TYPE' => 'ERROR'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin_after.php");

