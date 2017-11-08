<?php
namespace travelsoft\booking\doccreators;

/**
 * Фабрика шаблонизаторов
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class TemplateProccessorFactory {
    
    /**
     * @param string $path
     * @return \travelsoft\booking\adapters\DocxTemplateProccessor|\travelsoft\booking\adapters\HTMLTemplateProcessor
     * @throws \Exception
     */
    public function create (string $path) {
        
        $tplExt = array_pop(explode(".", $path));
        
        switch ($tplExt) {
            
            case "html":
                return new \travelsoft\booking\adapters\HTMLTemplateProcessor($path);
            case "doc":
            case "docx":
                return new \travelsoft\booking\adapters\DocxTemplateProccessor($path);
            default:
                throw new \Exception('Unknown format of template');
        }
    }
}
