<?php

namespace travelsoft\booking\adapters;

/**
 * Адаптер для шаблонов документов формата docx
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class DocxTemplateProccessor extends \travelsoft\booking\abstraction\TemplateProcessor{
    
    /**
     * @var \PhpOffice\PhpWord\TemplateProcessor
     */
    protected $_ext = null;
    
    /**
     * @param string $path
     */
    public function __construct(string $path) {
        $this->_ext = new \PhpOffice\PhpWord\TemplateProcessor($path);
    }
    
    /**
     * @param string $fileName
     * @throws \Exception
     */
    public function saveAs(string $fileName) {
        
        try {

            $savePath = $this->_getSavePath($fileName);

            switch ($this->_getExt($fileName)) {
                
                case "docx":
                    
                    $this->_ext->saveAs($savePath);
                    break;

                case "pdf":
                    
                    $htmlPath = \travelsoft\booking\Settings::getDocsSavePath() . '/' . $this->_getFileName($fileName) . '.html';
                    
                    \PhpOffice\PhpWord\IOFactory::createWriter(
                        \PhpOffice\PhpWord\IOFactory::load($this->_ext->save()), 'HTML')->save($htmlPath);
                    
                    $pdf = new \Dompdf\Dompdf();
                    $pdf->loadHtml(file_get_contents($htmlPath));
                    $pdf->render();
                    file_put_contents($savePath, $pdf->output());
                    unlink($htmlPath);
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
        $this->_ext->setValue($label, $value);
    }
}
