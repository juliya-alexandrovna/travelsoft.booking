<?php

namespace travelsoft\booking;

use travelsoft\booking\abstraction\Entity;
use travelsoft\booking\stores\Users;
use travelsoft\booking\adapters\Mail;

/**
 * Клас клиент
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Client extends Entity {

    /**
     * ID клиента
     * @var int
     */
    protected $_id = null;

    /**
     * Имя клиента
     * @var string
     */
    protected $_name = null;

    /**
     * Фамилия клиента
     * @var string
     */
    protected $_lastName = null;

    /**
     * Отчество клиента
     * @var string
     */
    protected $_secondName = null;

    /**
     * Телефон клиента
     * @var string
     */
    protected $_phone = null;

    /**
     * Email клиента
     * @var string
     */
    protected $_email = null;

    /**
     * Является агентом ?
     * @var bool
     */
    protected $_isAgent = false;

    /**
     * БИК
     * @var string 
     */
    protected $_bik = null;

    /**
     * Юр. название
     * @var string
     */
    protected $_legalName = null;

    /**
     * Юр. адрес
     * @var string
     */
    protected $_legalAddress = null;

    /**
     * Название банка
     * @var string
     */
    protected $_bankName = null;

    /**
     * Адрес банка
     * @var string
     */
    protected $_bankAddress = null;

    /**
     * Код банка
     * @var string
     */
    protected $_bankCode = null;

    /**
     * Расчётный счёт
     * @var string
     */
    protected $_checkingAccount = null;

    /**
     * УНП
     * @var string
     */
    protected $_unp = null;

    /**
     * ОКПО
     * @var string
     */
    protected $_okpo = null;

    /**
     * Фактический адрес
     * @var string
     */
    protected $_actualAddress = null;

    /**
     * Валюта счёта
     * @var string
     */
    protected $_accountCurrency = null;

    /**
     * Пароль клиента
     * @var string
     */
    protected $_password = null;

    /**
     * Утсановка ID
     * @param int $id
     * @return $this
     */
    public function setId(int $id) {

        $this->_id = $id;
        return $this;
    }

    /**
     * Возвращает ID
     * @return int
     */
    public function getId(): int {

        return (int) $this->_id;
    }

    /**
     * Устанавливает имя клиента
     * @param string $name
     * @return $this
     */
    public function setName(string $name) {

        $this->_name = $name;
        return $this;
    }

    /**
     * Возвращает имя клиента
     * @return string
     */
    public function getName(): string {

        return (string) $this->_name;
    }

    /**
     * Устанавливает фамилию клиента
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName) {

        $this->_lastName = $lastName;
        return $this;
    }

    /**
     * Возвращает фамилию клиента
     * @return string
     */
    public function getLastName(): string {

        return (string) $this->_lastName;
    }

    /**
     * Устанавливает отчество клиента
     * @param string $secondName
     * @return $this
     */
    public function setSecondName(string $secondName) {

        $this->_secondName = $secondName;
        return $this;
    }

    /**
     * Возвращает отчество клиента
     * @return string
     */
    public function getSecondName(): string {

        return (string) $this->_secondName;
    }

    /**
     * Возвращает полное имя клиента
     * @return string
     */
    public function getFullName(): string {

        return (string) implode(' ', array($this->getName(), $this->getSecondName(), $this->getLastName()));
    }

    /**
     * Устанавливает email
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email) {

        $emailParts = explode('@', $email);
        if (count($emailParts) !== 2) {

            throw new \Exception(get_called_class() . ': Email is not correct');
        }

        $this->_email = $email;
        return $this;
    }

    /**
     * Возвращает email клиента
     * @return string
     */
    public function getEmail(): string {

        return (string) $this->_email;
    }

    /**
     * Установка принадлежности клиента к агентам
     * @return $this
     */
    public function setAgentSign() {

        $this->_isAgent = true;
        return $this;
    }

    /**
     * Клиент является агентом ?
     * @return bool
     */
    public function isAgent(): bool {

        return $this->_isAgent;
    }

    /**
     * Устанавливает валюту счёта
     * @param string $accountCurrency
     * @return $this
     */
    public function setAccountCurrency(string $accountCurrency) {

        $this->_accountCurrency = $accountCurrency;
        return $this;
    }

    /**
     * Возвращает валюту счёта
     * @return string
     */
    public function getAccountCurrency(): string {

        return (string) $this->_accountCurrency;
    }

    /**
     * Устанавливает фактический адрес
     * @param string $actualAddress
     * @return $this
     */
    public function setActualAddress(string $actualAddress) {

        $this->_actualAddress = $actualAddress;
        return $this;
    }

    /**
     * Возвращает фактический адрес
     * @return string
     */
    public function getActualAddress(): string {

        return (string) $this->_actualAddress;
    }

    /**
     * Устанавливает адрес банка
     * @param string $bankAddress
     * @return $this
     */
    public function setBankAddress(string $bankAddress) {

        $this->_bankAddress = $bankAddress;
        return $this;
    }

    /**
     * Возвращает адрес банка
     * @return string
     */
    public function getBankAddress(): string {

        return (string) $this->_bankAddress;
    }

    /**
     * Устанавливает код банка
     * @param string $bankCode
     * @return $this
     */
    public function setBankCode(string $bankCode) {

        $this->_bankCode = $bankCode;
        return $this;
    }

    /**
     * Возвращает код банка
     * @return string
     */
    public function getBankCode(): string {

        return (string) $this->_bankCode;
    }

    /**
     * Устанавливает код банка
     * @param string $bankName
     * @return $this
     */
    public function setBankName(string $bankName) {

        $this->_bankName = $bankName;
        return $this;
    }

    /**
     * Возвращает название банка
     * @return string
     */
    public function getBankName(): string {

        return (string) $this->_bankName;
    }

    /**
     * Устанавливает БИК
     * @param string $bik
     * @return $this
     */
    public function setBik(string $bik) {

        $this->_bik = $bik;
        return $this;
    }

    /**
     * Возвращает БИК
     * @return string
     */
    public function getBik(): string {

        return (string) $this->_bik;
    }

    /**
     * Устанавливает Юр. название
     * @param string $legalName
     * @return $this
     */
    public function setLegalName(string $legalName) {

        $this->_legalName = $legalName;
        return $this;
    }

    /**
     * Возвращает Юр. название
     * @return string
     */
    public function getLegalName(): string {

        return (string) $this->_legalName;
    }

    /**
     * Устанавливает Юр. адрес
     * @param string $legalAddress
     * @return $this
     */
    public function setLegalAddress(string $legalAddress) {

        $this->_legalAddress = $legalAddress;
        return $this;
    }

    /**
     * Возвращает Юр. адрес
     * @return string
     */
    public function getLegalAddress(): string {

        return (string) $this->_legalAddress;
    }

    /**
     * Устанавливает УНП
     * @param string $unp
     * @return $this
     */
    public function setUnp(string $unp) {

        $this->_unp = $unp;
        return $this;
    }

    /**
     * Возвращает УНП
     * @return string
     */
    public function getUnp(): string {

        return (string) $this->_unp;
    }

    /**
     * Устанавливает ОКПО
     * @param string $okpo
     * @return $this
     */
    public function setOkpo(string $okpo) {

        $this->_okpo = $okpo;
        return $this;
    }

    /**
     * Возвращает ОКПО
     * @return string
     */
    public function getOkpo(): string {

        return (string) $this->_okpo;
    }

    /**
     * Устанавливает расчётный счёт
     * @param string $сheckingAccount
     * @return $this
     */
    public function setCheckingAccount(string $сheckingAccount) {

        $this->_сheckingAccount = $сheckingAccount;
        return $this;
    }

    /**
     * Возвращает расчётный счёт
     * @return string
     */
    public function getCheckingAccount(): string {

        return (string) $this->_сheckingAccount;
    }

    /**
     * Устанавливает телефон клиента
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone) {

        $this->_phone = $phone;
        return $this;
    }

    /**
     * Возвращает телефон клиента
     * @return string
     */
    public function getPhone(): string {

        return (string) $this->_phone;
    }

    /**
     * Устанавливает пароль клиента
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password) {

        $this->_password = $password;
        return $this;
    }

    /**
     * Добавление/обновление данных по клиенту
     * @return bool
     */
    public function save(): bool {

        if ($this->_id <= 0) {

            $password = $this->_password;
            if (!strlen($password)) {

                $password = randString(7, array(
                    "abcdefghijklnmopqrstuvwxyz",
                    "ABCDEFGHIJKLNMOPQRSTUVWX­YZ",
                    "0123456789",
                    "!@#\$%^&*()",
                ));
            }

            $arGroups = array();
            $def_group = \Bitrix\Main\Config\Option::get("main", "new_user_registration_def_group");
            if ($def_group != "") {
                $arGroups = explode(",", $def_group);
            }

            $this->_id = Users::add(array(
                        'NAME' => $this->getName(),
                        'LAST_NAME' => $this->getLastName(),
                        'SECOND_NAME' => $this->getSecondName(),
                        'LOGIN' => $this->getEmail(),
                        'EMAIL' => $this->getEmail(),
                        'PASSWORD' => $password,
                        'GROUP_ID' => $arGroups,
                        'CONFIRM_PASSWORD' => $password,
                        'PERSONAL_PHONE' => $this->getPhone(),
                        'UF_LEGAL_NAME' => $this->getLegalName(),
                        'UF_LEGAL_ADDRESS' => $this->getLegalAddress(),
                        'UF_BANK_NAME' => $this->getBankName(),
                        'UF_BANK_ADDRESS' => $this->getBankAddress(),
                        'UF_BANK_CODE' => $this->getBankCode(),
                        'UF_BANK_CHECKING_ACCOUNT' => $this->getCheckingAccount(),
                        'UF_UNP' => $this->getUnp(),
                        'UF_OKPO' => $this->getOkpo(),
                        'UF_ACTUAL_ADDRESS' => $this->getActualAddress(),
                        'UF_ACOUNT_CURRENCY' => $this->getAccountCurrency(),
                        'UF_BIK' => $this->getBik(),
            ));

            if ($this->_id > 0) {

                Mail::sendNewClientRegisterInfo(array(
                    'NAME' => $this->getName(),
                    'LAST_NAME' => $this->getLastName(),
                    'EMAIL' => $this->getEmail(),
                    'USER_ID' => $this->_id,
                    'LOGIN' => $this->getEmail(),
                    'MESSAGE' => 'Вы успешно зарегистрированны на сайте',
                    'URL_LOGIN' => $this->getEmail()
                ));

                if ($this->_isAgent) {

                    Mail::sendNewAgentAdminNotification(array('USER_ID' => $this->_id));
                }

                return true;
            }
        } else {

            if (!strlen($this->getEmail())) {

                $arSave['LOGIN'] = $this->getEmail();
                $arSave['EMAIL'] = $this->getEmail();
            }

            if (!strlen($this->_password)) {

                $arSave['PASSWORD'] = $this->_password;
                $arSave['CONFIRM_PASSWORD'] = $this->_password;
            }

            $arSave = array(
                'NAME' => $this->getName(),
                'LAST_NAME' => $this->getLastName(),
                'SECOND_NAME' => $this->getSecondName(),
                'PERSONAL_PHONE' => $this->getPhone(),
                'UF_LEGAL_NAME' => $this->getLegalName(),
                'UF_LEGAL_ADDRESS' => $this->getLegalAddress(),
                'UF_BANK_NAME' => $this->getBankName(),
                'UF_BANK_ADDRESS' => $this->getBankAddress(),
                'UF_BANK_CODE' => $this->getBankCode(),
                'UF_BANK_CHECKING_ACCOUNT' => $this->getCheckingAccount(),
                'UF_UNP' => $this->getUnp(),
                'UF_OKPO' => $this->getOkpo(),
                'UF_ACTUAL_ADDRESS' => $this->getActualAddress(),
                'UF_ACOUNT_CURRENCY' => $this->getAccountCurrency(),
                'UF_BIK' => $this->getBik(),
            );

            if (Users::update($arSave)) {

                return true;
            }
        }

        return false;
    }

}
