<?php

namespace App\Controllers;

use App\Models\Clinic_model;
use App\Models\ClinicHours_model;
use Exception;


class Directory extends Security_Controller
{

    protected $ClinicDirectory_model, $ClinicHours_model;

    public function __construct()
    {
        parent::__construct();
        helper('clinics_helper');
        // Cargamos el modelo del directorio de clínicas
        $this->ClinicDirectory_model = new Clinic_model();
        $this->ClinicHours_model    = new ClinicHours_model();
        
    }

    // Página principal: carga la vista única del CRUD
    public function index()
    {
        $now         = new \DateTime('now');
        $todayDow    = (int)$now->format('w');          // 0=Domingo … 6=Sábado
        $currentTime = $now->format('H:i:s');

        $listClinic = $this->ClinicDirectory_model->get_all();
        foreach ($listClinic as $clinic) {
            $clinic->members = get_clinic_users($clinic->id);
            $clinic->member_count = count($clinic->members);
            $clinic->hours = $this->ClinicHours_model->getHoursWithDayName($clinic->id);
            $clinic->is_open = false;
            foreach ($clinic->hours as $h) {
                if (
                    $h->day_of_week == $todayDow
                    && $currentTime >= $h->opening_time
                    && $currentTime <= $h->closing_time
                ) {
                    $clinic->is_open = true;
                    break;
                }
            }
        }
        $data['listClinic'] = $listClinic;


        return $this->template->rander("directory/index", $data);
    }

    // Carga el listado de clínicas con paginación vía AJAX (retorna vista parcial)
    public function loadClinics()
    {
        $page = $this->request->getPost('page') ?? $this->request->getGet('page') ?? 1;
        $perPage = 6; // Ajustable según necesidad
        $offset = ($page - 1) * $perPage;

        $clinics = $this->ClinicDirectory_model->getClinics($perPage, $offset);
        $totalClinics = $this->ClinicDirectory_model->countAllClinics();
        $totalPages = ceil($totalClinics / $perPage);

        $data = [
            'clinics'     => $clinics,
            'currentPage' => $page,
            'totalPages'  => $totalPages
        ];

        return view('directory/list', $data);
    }

    // Obtiene los datos de una clínica para editar (respuesta en JSON)
    public function getClinic($id)
    {
        // 1) Datos generales
        $clinic = $this->ClinicDirectory_model->get_one($id);

        // 2) Horarios con predeterminados y BD
        $days = $this->ClinicHours_model->getDefaultHours((int) $id);

        // 3) Respuesta JSON
        $response = (array) $clinic;
        $response['days'] = $days;

        return $this->response->setJSON($response);
    }



    // Elimina una clínica y retorna respuesta JSON
    public function delete($id)
    {
        $this->ClinicDirectory_model->delete($id);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Clínica eliminada exitosamente.']);
    }

     public function save()
    {
        try {
            $post = $this->request->getPost();
            $id   = $post['id'] ?? null;

            $data = [
                'name'      => $post['name'],
                'phone'     => $post['phone'],
                'address'   => $post['address'],
                'email'     => $post['email'],
                'extension' => $post['extension'],
                'fax'       => $post['fax'],
            ];

            // Manejo de foto
            $file = $this->request->getFile('photo');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(WRITEPATH . '../public/uploads/clinics/', $newName);
                $data['photo'] = '/uploads/clinics/' . $newName;
            }

            // Guardar o actualizar clínica
            if ($id) {
                $clinic_id = $this->ClinicDirectory_model->ci_save($data, $id);
            } else {
                // Generar ID único de 8 dígitos
                $uniqueId = $this->ClinicDirectory_model->generateUnique8DigitId();
                $data['id'] = $uniqueId;
                $clinic_id = $this->ClinicDirectory_model->ci_save($data);
            }
            // Eliminar horarios previos
            $this->ClinicHours_model->deleteByClinic((int)$clinic_id);

            // Insertar nuevos horarios
            $daysPost = $post['days'] ?? [];
            foreach ($daysPost as $dow => $vals) {
                if (! empty($vals['enabled'])) {
                    // Pasar datos por referencia a una variable
                    $hourEntry = [
                        'clinic_id'    => $clinic_id,
                        'day_of_week'  => $dow,
                        'opening_time' => $vals['open'],
                        'closing_time' => $vals['close'],
                    ];
                    $this->ClinicHours_model->ci_save($hourEntry);
                }
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $id ? 'Clínica actualizada.' : 'Clínica creada.'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Directory::save Error – ' . $e->getMessage());
            return $this->response
                        ->setStatusCode(500)
                        ->setJSON([
                            'status'  => 'error',
                            'message' => 'Error interno. Revise logs.'
                        ]);
        }
    }
}
