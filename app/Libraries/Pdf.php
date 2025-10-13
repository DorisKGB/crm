<?php

namespace App\Libraries;

require_once APPPATH . "ThirdParty/tcpdf/tcpdf.php";

class Pdf extends \TCPDF {

    private $pdf_type;

    public function __construct($pdf_type = '') {
        parent::__construct();

        $this->pdf_type = $pdf_type;
        $this->SetFontSize(10);
    }

    public function Header() {
        /*if ($this->pdf_type == 'invoice') {
            $break_margin = $this->getBreakMargin();
            $auto_page_break = $this->AutoPageBreak;
            $this->SetAutoPageBreak(false, 0);

            $img_file = get_file_from_setting("invoice_pdf_background_image", false, get_setting("timeline_file_path"));
            $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 500, '', false, false, 0);

            // restore auto-page-break status
            $this->SetAutoPageBreak($auto_page_break, $break_margin);
        } else {
            // call the original Header method from the parent class
            parent::Header();
        }*/
    }

    
/*public function generatePdf($htmlContent, $fileName = 'documento.pdf') {

    // Agregar una nueva página
    //$this->AddPage('L', 'LETTER');
    $this->AddPage('L', array(400, 250));
    $this->SetFont('helvetica', 'B', 8); 
    
    // Quitar la línea del encabezado (opcional, si deseas un encabezado vacío)
    $this->setHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));  // Sin título y sin línea
    $this->setPrintHeader(false);  // Desactivar la impresión del encabezado

    // Definir la URL de la imagen
    $image_url = 'https://rubymedfc.com/crm/files/profile_images/_file6719a9bce3578-avatar.png';
    
    // Descargar la imagen para obtener su tamaño
    $image_data = file_get_contents($image_url);
    $img_width = 50;  // Establecer el ancho de la imagen
    $img_height = 50; // Mantener la proporción

    // Obtener el tamaño de la página
    $page_width = $this->getPageWidth();
    $page_height = $this->getPageHeight();
    
    // Calcular las coordenadas para centrar la imagen
    $x = ($page_width - $img_width) / 2;  // Centrar en el eje X
    $y = 10;  // Posición vertical (puedes ajustarlo según lo necesites)
    
    // Colocar la imagen en el PDF con el tamaño reducido y centrado
    $this->Image($image_url, $x, $y, $img_width, $img_height, '', '', '', false, 300, '', false, false, 0, 'C');
    
    // Espacio después de la imagen para no sobreponerla con el contenido
    $this->Ln($img_height + 10);  // Agrega un salto de línea para dar espacio a la tabla

    // Añadir el contenido HTML
    $this->writeHTML($htmlContent, true, false, true, false, '');
    
    // Renderizar el PDF y forzar la descarga
    $this->Output($fileName, 'D');  // 'D' para descargar el archivo
}*/
    
public function generatePdf($htmlContent, $fileName = 'documento.pdf') {

    // Agregar una nueva página
    //$this->AddPage('L', 'LETTER');
    $this->AddPage('L', array(400, 250));
    $this->SetFont('helvetica', 'B', 8); 
    
    // Quitar la línea del encabezado (opcional, si deseas un encabezado vacío)
    $this->setHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));  // Sin título y sin línea
    $this->setPrintHeader(false);  // Desactivar la impresión del encabezado

    // Definir la URL de la imagen (logo)
    $image_url = 'https://rubymedfc.com/crm/files/profile_images/_file6719a9bce3578-avatar.png';
    
    // Descargar la imagen para obtener su tamaño
    $image_data = file_get_contents($image_url);
    $img_width = 100;  // Ancho de la imagen (ajusta según tus necesidades)
    $img_height = 100; // Altura de la imagen (ajusta según tus necesidades)
    
    // Obtener el tamaño de la página
    $page_width = $this->getPageWidth();
    $page_height = $this->getPageHeight();
    
    // Calcular las coordenadas para centrar la imagen
    $x = ($page_width - $img_width) / 2;  // Centrar en el eje X
    $y = ($page_height - $img_height) / 2;  // Centrar en el eje Y
    
    // Establecer la opacidad para simular la marca de agua (valor entre 0 y 1, donde 0 es totalmente transparente)
    $this->SetAlpha(0.2); // Baja opacidad para la marca de agua
    
    // Colocar la imagen como marca de agua en el centro de la página
    $this->Image($image_url, $x, $y, $img_width, $img_height, '', '', '', false, 300, '', false, false, 0, 'C');
    
    // Restaurar la opacidad a su valor original para el contenido
    $this->SetAlpha(1);

    // Añadir el contenido HTML
    $this->writeHTML($htmlContent, true, false, true, false, '');
    
    // Renderizar el PDF y forzar la descarga
    $this->Output($fileName, 'D');  // 'D' para descargar el archivo
}
        

}
