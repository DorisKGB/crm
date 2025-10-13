<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;
use App\Models\StampTemplate_model;

class StampTemplate extends Security_Controller
{

    protected $stampTemplateModel;

    public function __construct()
    {
        parent::__construct();
        helper('clinics');
        $this->stampTemplateModel = new StampTemplate_model();
    }


    public function index()
    {
        return $this->template->rander('stamptemplate/create');
    }

    public function clinicsAjax()
    {
        $clinics = get_user_clinics($this->login_user->id);
        return $this->response->setJSON(['success' => true, 'clinics' => $clinics]);
    }

    public function create()
    {
        try {
            log_message('info', 'StampTemplate::create - Iniciando creación de plantilla');
            
            $json = $this->request->getJSON();
            if (!$json) {
                log_message('error', 'StampTemplate::create - No se recibieron datos JSON');
                return $this->response->setJSON(['success' => false, 'message' => 'No se recibieron datos.']);
            }
            
            log_message('info', 'StampTemplate::create - Datos recibidos: ' . json_encode([
                'name' => $json->name ?? 'N/A',
                'has_image' => isset($json->image) && !empty($json->image),
                'coordinates' => isset($json->coordinates) ? $json->coordinates : 'N/A',
                'page_size' => $json->page_size ?? 'N/A',
                'clinic_id' => $json->clinic_id ?? 'N/A'
            ]));
            $imageData = $json->image;
            if ($imageData) {
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                    $imageType = $type[1]; // por ejemplo, png, jpg, etc.
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $imageData = base64_decode($imageData);
                    if ($imageData === false) {
                        throw new \Exception('Error al decodificar la imagen.');
                    }
                } else {
                    throw new \Exception('Formato de imagen no válido.');
                }

                $uploadPath = WRITEPATH . 'uploads/stamp_templates/';
                log_message('info', 'StampTemplate::create - Ruta de upload: ' . $uploadPath);
                
                if (!is_dir($uploadPath)) {
                    log_message('info', 'StampTemplate::create - Creando directorio: ' . $uploadPath);
                    if (!mkdir($uploadPath, 0755, true)) {
                        throw new \Exception('No se pudo crear el directorio de uploads.');
                    }
                }
                
                $fileName = uniqid('stamp_', true) . '.' . $imageType;
                $filePath = $uploadPath . $fileName;
                
                log_message('info', 'StampTemplate::create - Guardando imagen: ' . $filePath);
                $bytesWritten = file_put_contents($filePath, $imageData);
                
                if ($bytesWritten === false) {
                    throw new \Exception('No se pudo guardar la imagen en el servidor.');
                }
                
                log_message('info', 'StampTemplate::create - Imagen guardada exitosamente: ' . $bytesWritten . ' bytes');

                $storedImage = 'uploads/stamp_templates/' . $fileName;
            } else {
                $storedImage = '';
            }

            $data = [
                'name'                           => $json->name,
                'image'                          => $storedImage, // se guarda la ruta
                'signature_x'                    => $json->coordinates->x,
                'signature_y'                    => $json->coordinates->y,
                'page_size'                      => $json->page_size,
                'clinic_id'                      => ($json->clinic_id === "") ? null : $json->clinic_id,
            ];

            // Agregar datos de orientación si están disponibles
            if (isset($json->orientation_data) && $json->orientation_data) {
                $orientationData = $json->orientation_data;
                
                // Manejar ambos formatos: camelCase y snake_case
                $isHorizontal = null;
                $aspectRatio = null;
                $rotation = null;
                $orientation = null;
                
                // Intentar camelCase primero (formato esperado)
                if (isset($orientationData->isHorizontal)) {
                    $isHorizontal = $orientationData->isHorizontal ? 1 : 0;
                } elseif (isset($orientationData->is_horizontal)) {
                    // Fallback a snake_case (formato enviado por JS)
                    $isHorizontal = $orientationData->is_horizontal === '1' || $orientationData->is_horizontal === 1 ? 1 : 0;
                }
                
                if (isset($orientationData->aspectRatio)) {
                    $aspectRatio = $orientationData->aspectRatio;
                } elseif (isset($orientationData->aspect_ratio)) {
                    $aspectRatio = $orientationData->aspect_ratio;
                }
                
                if (isset($orientationData->rotation)) {
                    $rotation = $orientationData->rotation;
                }
                
                if (isset($orientationData->orientation)) {
                    $orientation = $orientationData->orientation;
                }
                
                // Solo agregar si tenemos datos válidos
                if ($isHorizontal !== null) $data['is_horizontal'] = $isHorizontal;
                if ($aspectRatio !== null) $data['aspect_ratio'] = $aspectRatio;
                if ($rotation !== null) $data['rotation'] = $rotation;
                if ($orientation !== null) $data['orientation'] = $orientation;
                
                log_message('info', 'StampTemplate::create - Datos de orientación procesados: ' . json_encode([
                    'is_horizontal' => $isHorizontal,
                    'aspect_ratio' => $aspectRatio,
                    'rotation' => $rotation,
                    'orientation' => $orientation
                ]));
            }

            log_message('info', 'StampTemplate::create - Datos a guardar: ' . json_encode($data));
            
            $insert_id = $this->stampTemplateModel->ci_save($data);
            
            if ($insert_id) {
                log_message('info', 'StampTemplate::create - Plantilla guardada exitosamente con ID: ' . $insert_id);
                return $this->response->setJSON(['success' => true, 'message' => 'Plantilla guardada correctamente.', 'id' => $insert_id]);
            }
            
            log_message('error', 'StampTemplate::create - Error al guardar en base de datos');
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar la plantilla en la base de datos.']);
        } catch (\Exception $ex) {
            log_message('error', 'Error en StampTemplate::create - ' . $ex->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error interno del servidor.' . $ex->getMessage()]);
        }
    }
    /**
     * Retorna la lista de plantillas en formato JSON (para usarse en el modal de selección).
     */
    /*public function listAjax()
    {
        $userClinics = get_user_clinics($this->login_user->id);
        $clinicIds = array_map(fn($c) => $c->id, $userClinics);
        $templates = $this->stampTemplateModel->getAllByClinicsOrGlobal($clinicIds);
        //$templates = $this->stampTemplateModel->get_all()->getResult();
        return $this->response->setJSON(['success' => true, 'templates' => $templates]);
    }*/

    public function listAjax()
    {
        // 1) Traigo las clínicas del usuario
        $userClinics = get_user_clinics($this->login_user->id);
        $clinicIds   = array_map(fn($c) => $c->id, $userClinics);

        // 2) ¿Hay al menos una clínica NO aliada?
        $hasNonAliada = false;
        foreach ($userClinics as $clinic) {
            if (isset($clinic->is_aliada) && intval($clinic->is_aliada) === 0) {
                $hasNonAliada = true;
                break;
            }
        }

        // 3) Obtengo plantillas usando el helper de Crud_model
        if ($hasNonAliada) {
            // → Propias + globales (usa tu método personalizado)
            $templates = $this->stampTemplateModel->getAllByClinicsOrGlobal($clinicIds);
        } else {
            // → Solo propias, SIN globales
            // aquí usamos get_all_where() pasando el where_in:
            $query = $this->stampTemplateModel->get_all_where(
                ['where_in' => ['clinic_id' => $clinicIds]],
                0,      // sin límite
                0,      // sin offset
                'name'  // orden por name ASC
            );
            $templates = $query->getResult();
        }

        return $this->response->setJSON([
            'success'   => true,
            'templates' => $templates
        ]);
    }

    public function updateAjax($id)
    {
        // Leer el JSON enviado por fetch()
        $payload = $this->request->getJSON(true);

        // Mapear sólo los campos que quieres actualizar
        $data = [
            'name'           => $payload['name'],
            'signature_x'    => $payload['signature_x'],
            'signature_y'    => $payload['signature_y'],
        ];

        // Usar ci_save para hacer UPDATE (el segundo parámetro es el ID)
        $this->stampTemplateModel->ci_save($data, (int)$id);

        return $this->response
            ->setStatusCode(200)
            ->setJSON(['success' => true, 'message' => 'Plantilla actualizada']);
    }

    public function getAjax($id)
    {
        $template = $this->stampTemplateModel->get_one_with_clinic($id);
        if (empty($template->id)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Plantilla no encontrada'
                ]);
        }

        // Si la encontramos, devolvemos success + datos
        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'success'  => true,
                'template' => $template
            ]);
    }

    public function deleteAjax($id)
    {
        // El método delete() de Crud_model marca deleted = 1
        $this->stampTemplateModel->delete((int)$id);

        return $this->response
            ->setStatusCode(200)
            ->setJSON(['success' => true, 'message' => 'Plantilla eliminada']);
    }
}
