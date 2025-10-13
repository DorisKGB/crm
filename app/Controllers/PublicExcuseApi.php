<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Excuse_model;
use App\Libraries\Pdf;

class PublicExcuseApi extends ResourceController
{
    protected $modelName = 'App\Models\Excuse_model';
    protected $format    = 'json';

    /**
     * Endpoint para validar un certificado (excusa) usando los 8 primeros dígitos del token.
     * Ejemplo: /api/excuse/validateByPrefix?token=ABCDEFGH
     */
    public function validateByPrefix()
    {
     
        $tokenPrefix = $this->request->getGet('token');
        if (empty($tokenPrefix)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El token es requerido'
            ]);
        }
        // Convertir a mayúsculas para la comparación
        $tokenPrefix = strtoupper($tokenPrefix);
        
        // Utilizar el modelo Excuse_model para buscar una excusa cuyo token comience con el token recibido
        $db = \Config\Database::connect();
        $builder = $db->table($this->model->table); // Usamos la propiedad "table" del modelo

        if (strlen($tokenPrefix) == 8) {
          // Buscar por los 8 primeros caracteres (convertidos a mayúsculas)
          $tokenPrefix = strtoupper($tokenPrefix);
          $builder->where("UPPER(SUBSTRING(token, 1, 8))", $tokenPrefix);
      } else {
          // Búsqueda exacta
          $tokenPrefix = strtoupper($tokenPrefix);
          $builder->where('token', $tokenPrefix);
      }

        //$builder->where("UPPER(SUBSTRING(token, 1, 8))", $tokenPrefix);
        $excuse = $builder->get()->getRow();
        
        if (!$excuse || $excuse->state != "approved") {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Excusa no encontrado'
            ]);
        }
        
        // Generar la URL para ver el PDF en modo inline (solo visualización)
        $pdfUrl = site_url("document/excuse/generatePdfByToken?token=" . $tokenPrefix . "");

         //Obtener logo de Clinicas
        $db = \Config\Database::connect();
        $builder = $db->table('crm_clinic_directory');
        $builder->select("*");
        $builder->where('id', $excuse->clinic_id);
        $query = $builder->get()->getRow();

        
        return $this->response->setJSON([
            'success'   => true,
            'message'   => 'Excusa válido',
            'pdfUrl'    => $pdfUrl,
            'excuse'    => $excuse,
            'logo'      => $query->logo,
            'isAliada'  =>  $query->is_aliada,
        ]);
    }

    function generateQrCodeBase64($text) {
        // Comprobar que el archivo existe
        $qrLib = APPPATH . 'ThirdParty/phpqrcode/qrlib.php';
        if (!file_exists($qrLib)) {
            throw new \Exception("El archivo qrlib.php no se encontró en " . $qrLib);
        }
        ob_start();
        require_once $qrLib;
        // Genera el QR y lo envía a la salida (tamaño de módulo 3)
        \QRcode::png($text, null, QR_ECLEVEL_L, 3);
        $imageString = ob_get_contents();
        ob_end_clean();
        return 'data:image/png;base64,' . base64_encode($imageString);

    }

    public function generatePdfByToken()
    {
        
        $token = $this->request->getGet('token');
        if (empty($token)) {
            echo "El token es requerido";
            return;
        }
        
        $db = \Config\Database::connect();
        $builder = $db->table($this->model->table); // usa la propiedad table del modelo
        
        if (strlen($token) == 8) {
            // Buscar por los 8 primeros caracteres (convertidos a mayúsculas)
            $token = strtoupper($token);
            $builder->where("UPPER(SUBSTRING(token, 1, 8))", $token);
        } else {
            // Búsqueda exacta
            $token = strtoupper($token);
            $builder->where('token', $token);
        }
        
        $excuse = $builder->get()->getRow();
        if (!$excuse || $excuse->state != "approved") {
            echo "Certificado no encontrado";
            return;
        }
        
        // Datos de la clínica (ajusta según tu sistema)
        $clinicName    = "Clínica Hispana Rubymed";
        $clinicLogo    = "https://www.clinicahispanarubymed.com/wp-content/uploads/2024/07/Logonuevo.png";
        
        // Convertir fechas a formato MM-DD-YYYY
        $dateToday      = date("m-d-Y h:i A", strtotime($excuse->date_attention));
        $currentDateTime= date("m-d-Y h:i:s A");
        $birthDate      = date('m-d-Y', strtotime($excuse->birth));
        $serviceDate    = date('m-d-Y', strtotime($excuse->date_start));
        $returnDate     = date('m-d-Y', strtotime($excuse->date_end));
        $type           = ($excuse->type == "medica_escolar" ? "Excusa Médica Escolar" : "Excusa Médica Laboral");

        // Generar el código QR en base64 (si lo requieres en el PDF)
        $qrDataUri = $this->generateQrCodeBase64($excuse->token);
        // Extraer la cadena base64 y decodificar
        $base64Data = str_replace('data:image/png;base64,', '', $qrDataUri);
        $qrBinary   = base64_decode($base64Data);
        
        // Formatear el token:
        // Para la referencia completa: primeros 8 en mayúsculas y el resto en minúsculas
        $tokenPrefix    = strtoupper(substr($excuse->token, 0, 8));
        $tokenRest      = strtolower(substr($excuse->token, 8));
        $tokenFormatted = $tokenPrefix . $tokenRest;
        $date_today = date("m-d-Y h:i A", strtotime($excuse->date_attention));

        // Construir el contenido HTML para el PDF (en formato párrafo)
        $html = '
        <html>
          <head>
            <style>
              body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; }
              .header img { max-width: 150px; }
              .header h1 { margin: 10px 0; font-size: 24px; }
              .header p { margin: 5px 0; font-size: 14px; }
              .content p { margin: 10px 0; }
              .signature { margin-top: 40px; }
              .signature div { display: inline-block; width: 45%; text-align: center; }
              .qr { text-align: center; margin-top: 30px; }
              .qr img { max-width: 120px; }
              .logo {width: 200px !important;}
              .signature {width: 100px !important;}
              .text-center{ text-align: center; }
              .img-firma{display:none !important; margin-top:500px !important;}
            </style>
          </head>
          <body>
            <div class="header">
              <img class="logo" src="'.$clinicLogo.'" alt="Logo Clínica"> <br/>
              <h3>Clinica Hispana Rubymed '.$excuse->clinic.'</h3>
              <span><b>Address : </b>'.$excuse->clinic_address.'</span> <br/>
              <span><b>Phone : </b>'.$excuse->clinic_phone.'</span><br/>
            </div>
            <div class="content">
              <p class="text-center"><strong>EXCUSE FORM:</strong></p>
              <p>This is to certify that <strong> '.htmlspecialchars($excuse->name).'</strong></p>
              <p>Was seen at this medical office for professional medical service on: <strong>'.htmlspecialchars($date_today).'</p>
              <p>Please excuse his/her absence.</p>';

              if($type == "Excusa Médica Escolar"){
                $html .= '<p>Patient may return to school (<strong>X</strong>) or work (<strong></strong>) on <strong>'.date('m-d-Y', strtotime($excuse->date_end)).'</strong></p>';
              }
              if($type == "Excusa Médica Laboral"){
                $html .= '<p>Patient may return to school (<strong></strong>) or work (<strong>X</strong>) on <strong>'.date('m-d-Y', strtotime($excuse->date_end)).'</strong></p>';
              }
              
              $firm = 'https://www.clinicahispanarubymed.com/crm/writable/firmas/' . basename($excuse->privider_signature);
             // Extrae los primeros 8 caracteres en mayúsculas y el resto en minúsculas
                $tokenPrefix    = strtoupper(substr($excuse->token, 0, 8));
                $tokenRest      = strtolower(substr($excuse->token, 8));
                $tokenFormatted = $tokenPrefix . $tokenRest;
              
                $html .= '
                <p><strong>Reason/Restriction:</strong></p>
                <p>' . htmlspecialchars($excuse->reason) . '</p>
                <p><b>Sincerely,</b></p> <br><br>
                <span><img class="signature" src="' . $firm . '"></span> <br>
                <span><small>Electronic Signature Ref <b>' . $tokenFormatted . '</b> ' . $currentDateTime . '</small></span> <br>
                <span><b>Provider: </b>' . $excuse->provider . '</span> <br>
                <span><b>NPI: </b>' . $excuse->provider_npi . '</span> <br>
                <span><b>Roles: </b>' . $excuse->privider_role . '</span> <br>
                <br/><br/><br/>
                <small>
                  This document has been electronically signed and digitized by the RUBYMED INC. system. If you wish to validate the veracity or validity of this document, please visit www.clinicahispanarubymed.com/excusevalidator and enter the Electronic Reference number: <b>' . $tokenPrefix . '</b>.
                </small>

              </div>
              </body>
              </html>';

        // Crear una instancia de la librería Pdf (que extiende TCPDF)
        $pdf = new Pdf();
        $pdf->AddPage('P', 'A4');
        $pdf->SetFont('helvetica', '', 10);
   

        // Definir posición y dimensiones para la imagen QR
        /*$x = 160; // posición X en el PDF (ajusta según tu diseño)
        $y = 200;  // posición Y en el PDF (ajusta según tu diseño)
        $w = 40;  // ancho
        $h = 40;  // alto

        // Insertar la imagen QR en el PDF usando el método Image() de TCPDF
        $pdf->Image('@' . $qrBinary, $x, $y, $w, $h, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);*/
        
  

              
        // Colocar Check
        // URL del sello
        $selloUrl = 'https://www.clinicahispanarubymed.com/wp-content/uploads/2024/07/sello3.png';
        // Obtener el contenido binario de la imagen
        $SelloBinary = file_get_contents($selloUrl);

        // Insertar la imagen en el PDF usando TCPDF
        $pdf->Image('@' . $SelloBinary, 10, 5, 40, 40, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false); 


        $pdf->writeHTML($html, true, false, true, false, '');
        //$pdf->Output("excusa_".$excuse->token.".pdf", 'D');
      
        
        // Limpiar el buffer de salida
        ob_end_clean();

            return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="excusa_'.$excuse->token.'.pdf"')
        ->setBody($pdf->Output('excusa_'.$excuse->token.'.pdf', 'S'));
    }
}
