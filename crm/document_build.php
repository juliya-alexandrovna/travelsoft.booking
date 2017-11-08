<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Documents;
use travelsoft\booking\stores\Orders;

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
    
    if (0 >= strlen($_GET['DOCFORMAT'])) {
        $errors[] = 'Не указан формат получаемого документа';
    }
    
    attemptSendErros($errors);
    
    $order = Orders::getById((int)$_GET['ORDER_ID']);
    
    if (!$order['ID']) {
        $errors[] = 'Путевка с ID="'+$order['ID']+'" не найдена';
    }
    
    $dbDoc = Documents::getById((int)$_GET['DOC_TPL_ID']);
    
    if (!$dbDoc['ID'] || !$dbDoc['UF_TPL']) {
        $errors[] = 'Шаблон документа с ID="'+$dbDoc['ID']+'" не найден';
    }
    
    attemptSendErros($errors);

    $file = CFile::GetFileArray($dbDoc['UF_TPL']);
    
    $document = $dbDoc['UF_CLASS']::create($order, $file['SRC'], $_GET['DOCFORMAT']);
    
    
} catch (\Exception $e) {
    
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    CAdminMessage::ShowMessage(array('MESSAGE' => $e->getMessage(), 'TYPE' => 'ERROR'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin_after.php");

