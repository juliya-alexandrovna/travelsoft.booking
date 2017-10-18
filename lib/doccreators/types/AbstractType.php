<?php

namespace travelsoft\booking\doccreators\types;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Класс формирования документа
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class AbstractType {
        
    /**
     * @var type 
     */
    protected $_reader = null;
    
    abstract public function __construct(\travelsoft\booking\doccreators\Repository $repository);
    
    abstract public function save (string $path);
    
    abstract public function stream (string $path);
    
}
