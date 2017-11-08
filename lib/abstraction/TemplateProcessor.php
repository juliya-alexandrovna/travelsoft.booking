<?php

namespace travelsoft\booking\abstraction;

require_once __DIR__ . '../../../vendor/autoload.php';

/**
 * Интерфейс шаблонизаторов документов
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class TemplateProcessor {
    
    abstract public function saveAs (string $path);
    
    abstract public function setValue (string $label, string $value);

    /**
     * @param string $fileName
     * @throws \Exception
     */
    public function stream(string $fileName) {

        $this->saveAs($fileName);
        
        header("Content-Length: " . filesize($fileName));
        header("Content-disposition: attachment; filename=" . $fileName);

        switch ($this->_getExt($fileName)) {

            case "docx":

                header("Content-Type: application/x-force-download; name=\"" . $fileName . "\"");

            case "pdf":

                header("Content-type: application/pdf");
                
            case "html":

                header("Content-type: text/html");
        }

        readfile($this->_getSavePath($fileName));

        exit;
    }
    
    /**
     * @param string $fileName
     * @return string
     */
    protected function _getExt (string $fileName) : string {
        $fileParts = explode(".", $fileName);
        return (string)array_pop($fileParts);
    }
    
    /**
     * @param string $fileName
     * @return string
     */
    protected function _getSavePath (string $fileName) : string {
        return \travelsoft\booking\Settings::getDocsSavePath() . '/' . $fileName;
    }
    
    /**
     * @param string $fileName
     * @return string
     */
    protected function _getFileName (string $fileName) :string  {
        $fileParts = explode(".", $fileName);
        return (string)$fileParts[0];
    }
}
