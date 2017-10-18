<?php

namespace travelsoft\booking\doccreators\types;

/**
 * Класс фабрика типов шаблонов
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Factory {
    
    public function create (\travelsoft\booking\doccreators\Repository $repository) {
        
        $ext = array_pop(explode('.', $repository->path));
        
        if ($ext === 'docx') {
            return new Docx($repository);
        } else {
            throw new \Exception('Неизвестный формат шаблона файла');
        }
        
    }
}
