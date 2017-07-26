<?php

namespace travelsoft\booking\abstraction;

/**
 * Абстрактный класс для работы с хранилищем данных
 * 
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class Store {

    abstract public static function get(array $query = array(), bool $likeArray = true, callable $callback = null);

    abstract public static function add(array $arSave): int;

    abstract public static function update(int $id, array $arUpdate): bool;

    abstract public static function delete(int $id): bool;

    abstract public static function getById(int $id): array;
}
