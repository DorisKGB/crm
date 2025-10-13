<?php

namespace App\Controllers;

use App\Models\Patient_model;
use Exception;

class Patients extends Security_Controller
{
    protected $Patient_model;

    public function __construct()
    {
        parent::__construct();
        $this->Patient_model = new Patient_model();
        $this->access_only_team_members();
    }

    public function index()
    {
        return $this->template->rander("patients/index");
    }

    public function list_data()
    {
        $patients = $this->Patient_model->get_all()->getResult();
        $result = [];

        if ($patients && is_iterable($patients)) {
            foreach ($patients as $p) {
                $reasons = json_decode($p->reason ?? '[]', true);
                $fecha_creacion = format_to_datetime($p->created_at); // 👈 Esto muestra “15 de julio de 2025”

                $result[] = [
                    $p->id,
                    $p->full_name,
                    $p->phone,
                    $p->email,
                    $fecha_creacion,
                    '<button class="btn-button btn-button-danger text-center" onclick="openDetail(' . $p->id . ')"><i class="fa fa-heartbeat"></i></button>',
                    '<button class="btn-button btn-button-outline-danger text-center" onclick="openReasonForm(' . $p->id . ')"><i class="fas fa-heartbeat" ></i></button>',
                    '<button class="btn-button btn-button-outline-secondary text-center" onclick="openExtraForm(' . $p->id . ')"><i class="fas fa-plus"></i></button>',
                    '<button class="btn-button btn-button-outline-success text-center" onclick="openEditModal(' . $p->id . ')"><i class="fas fa-pencil-alt"></i></button> <button class="btn-button btn-button-outline-danger text-center" onclick="openDeleteModal(' . $p->id . ')"><i class="fas fa-trash-alt"></i></button>'
                ];
            }
        }

        return $this->response->setJSON(['data' => $result]);
    }

    public function edit_modal()
    {
        $id = $this->request->getGet('id');
        $patient = $this->Patient_model->get_one($id);
        return $this->template->view('patients/modals/edit_modal', ['patient' => $patient]);
    }

    public function delete_modal()
    {
        $id = $this->request->getGet('id');
        $patient = $this->Patient_model->get_one($id);
        return $this->template->view('patients/modals/delete_modal', ['patient' => $patient]);
    }

    public function update_patient()
    {
        $id = $this->request->getPost("id");
        $data = [
            'full_name' => $this->request->getPost("full_name"),
            'email'     => $this->request->getPost("email"),
            'phone'     => $this->request->getPost("phone"),
        ];
        $this->Patient_model->update($id, $data);
        return $this->response->setJSON(["success" => true]);
    }

    public function delete_patient()
    {
        $id = $this->request->getPost("id");
        $this->Patient_model->delete($id);
        return $this->response->setJSON(["success" => true]);
    }



    public function new_modal()
    {
        return $this->template->view('patients/modals/new_modal');
    }

    public function add_reason_modal()
    {
        $id = $this->request->getGet('id');
        $patient = $this->Patient_model->get_one($id);
        return $this->template->view('patients/modals/add_reason_modal', ['id' => $id, 'patient' => $patient]);
    }

    public function edit_extra_data_modal()
    {
        $id = $this->request->getGet('id');
        $patient = $this->Patient_model->get_one($id);
        $extra_data = json_decode($patient->extra_data ?? '{}', true);

        return $this->template->view('patients/modals/edit_extra_data_modal', [
            'id' => $id,
            'extra_data' => $extra_data,
            'patient' => $patient
        ]);
    }

    public function view_modal()
    {
        $id = $this->request->getGet('id');
        $patient = $this->Patient_model->get_one($id);

        $reasons = json_decode($patient->reason ?? '[]', true);
        $extra_data = json_decode($patient->extra_data ?? '{}', true);

        return $this->template->view('patients/modals/view_modal', [
            'patient' => $patient,
            'reasons' => $reasons,
            'extra_data' => $extra_data
        ]);
    }

    public function save()
    {
        $data = [
            "full_name" => $this->request->getPost("full_name"),
            "email"     => $this->request->getPost("email"),
            "phone"     => $this->request->getPost("phone"),
            "reason"    => json_encode([
                [
                    "fecha"  => date('Y-m-d'),
                    "motivo" => $this->request->getPost("reason")
                ]
            ])
        ];

        $id = $this->Patient_model->ci_save($data);
        return $this->response->setJSON(["success" => true, "id" => $id]);
    }

    public function add_reason()
    {
        $id = $this->request->getPost("id");
        $new_reason = $this->request->getPost("motivo");

        $patient = $this->Patient_model->get_one($id);
        $history = json_decode($patient->reason, true) ?? [];
        $history[] = ["fecha" => date("Y-m-d"), "motivo" => $new_reason];

        $this->Patient_model->ci_save(["reason" => json_encode($history)], $id);

        return $this->response->setJSON(["success" => true]);
    }

    public function update_extra_data()
    {
        $id = $this->request->getPost("id");
        $extra_data = $this->request->getPost("extra_data");

        // Ya debe venir como JSON del frontend
        $this->Patient_model->ci_save(["extra_data" => $extra_data], $id);

        return $this->response->setJSON(["success" => true]);
    }

    public function create()
    {
        $full_name = $this->request->getPost('full_name');
        $email     = $this->request->getPost('email');
        $phone     = $this->request->getPost('phone');
        $reasonTxt = $this->request->getPost('reason');

        if (!$full_name) {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es obligatorio']);
        }

        $reasons = [];
        if (!empty($reasonTxt)) {
            $reasons[] = [
                'fecha' => date('Y-m-d'),
                'motivo' => $reasonTxt
            ];
        }
        try {
            $data = [
                'full_name' => $full_name,
                'email'     => $email,
                'phone'     => $phone,
                'reason'    => json_encode($reasons),
                'extra_data' => json_encode(new \stdClass()), // vacío
                'source'    => 'manual'
            ];
        } catch (Exception $e) {
            log_message('debug', $e->getMessage());
        }


        $this->Patient_model->save($data);

        return $this->response->setJSON(['success' => true]);
    }

    public function save_reason()
    {
        $id         = $this->request->getPost('id');
        $newReason  = $this->request->getPost('reason');

        $patient = $this->Patient_model->get_one($id);
        if (!$patient) {
            return $this->response->setJSON(['success' => false, 'message' => 'Paciente no encontrado']);
        }

        $reasons = json_decode($patient->reason ?? '[]', true);
        $reasons[] = [
            'fecha' => date('Y-m-d'),
            'motivo' => $newReason
        ];

        $this->Patient_model->update($id, [
            'reason' => json_encode($reasons)
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function save_extra_data()
    {
        $id    = $this->request->getPost('id');
        $keys  = $this->request->getPost('keys');
        $values = $this->request->getPost('values');

        if (!$id || !is_array($keys) || !is_array($values)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos inválidos']);
        }

        $extra_data = [];
        foreach ($keys as $i => $key) {
            $key = trim($key);
            $val = trim($values[$i] ?? '');
            if ($key !== '') {
                $extra_data[$key] = $val;
            }
        }

        $this->Patient_model->update($id, [
            'extra_data' => json_encode($extra_data)
        ]);

        return $this->response->setJSON(['success' => true]);
    }


    public function search_patients()
    {
        try {
            $term = trim($this->request->getGet('q') ?? '');

            if ($term === '') {
                return $this->response->setJSON([]);
            }

            $db = db_connect(); // ✅ conexión manual
            $table = $db->prefixTable('patients'); // usa crm_patients si tienes prefijo
            $builder = $db->table($table);

            $builder->select('id, full_name');
            $builder->where('deleted', 0);

            $builder->groupStart()
                ->like('full_name', $term)
                ->orLike('email', $term)
                ->orLike('phone', $term)
                ->groupEnd();

            $results = $builder->limit(20)->get()->getResult();

            $data = [];
            foreach ($results as $p) {
                $data[] = [
                    'id' => $p->id,
                    'name' => $p->full_name // 👈 como espera el JS
                ];
            }

            return $this->response->setJSON($data);
        } catch (\Throwable $e) {
            log_message('error', 'search_patients error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function get_info()
    {
        $id = $this->request->getGet('id');
        $info = $this->Patient_model->get_one($id);
        return $this->response->setJSON($info);
    }
}
