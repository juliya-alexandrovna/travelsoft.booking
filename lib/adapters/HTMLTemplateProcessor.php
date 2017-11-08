<?php

namespace travelsoft\booking\adapters;

/**
 * Шаблонизотор HTML документов
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft 
 */
class HTMLTemplateProcessor extends \travelsoft\booking\abstraction\TemplateProcessor{
    
    /**
     * @var string
     */
    protected $_context = '';
    
    /**
     * @param string $path
     */
    public function __construct(string $path) {
        $this->_context = \file_get_contents($path);
    }
    
    /**
     * @param string $fileName
     */
    public function saveAs(string $fileName) {
        try {

            $savePath = $this->_getSavePath($fileName);

            switch ($this->_getExt($fileName)) {
                
                case "docx":
                    
                    $w = new \PhpOffice\PhpWord\PhpWord();
                    
                    $section = $w->addSection();
                    
                    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->_context, false);
                    \PhpOffice\PhpWord\IOFactory::createWriter($w, 'Word2007')->save($savePath);
                    break;

                case "pdf":

                    $pdf = new \Dompdf\Dompdf();
                    $pdf->loadHtml($this->_context);
                    $pdf->render();
                    file_put_contents($savePath, $pdf->output());
                    break;

                default:
                    throw new \Exception("unknown file format");
            }
        } catch (\Exception $e) {

            if ($e->getMessage() === "unknown file format") {
                throw new \Exception("Указан неизвестный формат файла для сохранения");
            }

            throw new \Exception($e->getMessage());
        }
    }
    
    /**
     * @param string $label
     * @param string $value
     */
    public function setValue(string $label, string $value) {
        $this->_context = str_replace("\${" . $label . "}", $value, $this->_context);
    }
}
