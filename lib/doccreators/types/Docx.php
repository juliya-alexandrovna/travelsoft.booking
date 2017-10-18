<?php

namespace travelsoft\booking\doccreators\types;

/**
 * Класс типа шаблона docx
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Docx extends AbstractType{
    
    public function __construct(\travelsoft\booking\doccreators\Repository $repository) {
//        $this->_reader = \PhpOffice\PhpWord\IOFactory::createReader('Word2007')->load($repository->path);
//        foreach ($this->_reader->getSections() as &$section) {
//            foreach ($section->getElements() as &$e) {
//                switch (get_class($e)) {
//                    
//                    case "PhpOffice\PhpWord\Element\Text":
//                        $e->setText(str_replace($repository->getLabels(), $repository->getLabelsValues(), $e->getText()));
//                        break;
//                    case "PhpOffice\PhpWord\Element\TextRun":
//                        foreach ($e->getElements() as &$se) {
//                            $se->setText(str_replace($repository->getLabels(), $repository->getLabelsValues(), $se->getText()));
//                        }
//                        break;
//                }
//            }
//        }
    }
    
    public function save(string $fileName) {
        
        \PhpOffice\PhpWord\IOFactory::createWriter($this->_reader, "Word2007")->save($_SERVER['DOCUMENT_ROOT'] . '/' .$fileName);
    }
    
    public function stream(string $path) {
        ;
    }
}
