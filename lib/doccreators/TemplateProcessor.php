<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace travelsoft\booking\doccreators;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Description of TemplateProcessor
 *
 * @author dimabresky
 */
class TemplateProcessor extends \PhpOffice\PhpWord\TemplateProcessor {
    
    public function __construct(Repository $repository) {
        
        parent::__construct($repository->path);
        foreach ($repository->getLabels() as $label => $value) {
            $this->setValue($label, $value);
        }
    }
}
