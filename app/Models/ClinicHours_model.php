<?php

namespace App\Models;

class ClinicHours_model extends Crud_model
{
    protected $table = null;

    function __construct()
    {
        $this->table = 'clinic_hours';
        parent::__construct($this->table);
    }

    public function getHoursWithDayName($clinic_id)
    {
        $rows = $this->get_all_where(['clinic_id' => $clinic_id])->getResult();
        $days = [
            'Domingo',
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
            'Sábado'
        ];
        foreach ($rows as &$r) {
            $r->day_name = $days[$r->day_of_week] ?? null;
        }
        return $rows;
    }

    public function deleteByClinic(int $clinic_id): bool
    {
        return (bool) $this->db_builder
            ->where('clinic_id', $clinic_id)
            ->delete();
    }

    public function getDefaultHours(int $clinic_id): array
    {
        // Inicializa con valores por defecto: Lunes a Sábado 09:00–18:00
        $defaults = [];
        for ($i = 0; $i <= 6; $i++) {
            if ($i >= 1 && $i <= 6) {
                $defaults[$i] = [
                    'enabled' => 1,
                    'open'    => '09:00:00',
                    'close'   => '18:00:00',
                ];
            } else {
                $defaults[$i] = [
                    'enabled' => 0,
                    'open'    => '',
                    'close'   => '',
                ];
            }
        }
        // Reemplaza con los horarios guardados en BD
        $rows = $this->get_all_where(['clinic_id' => $clinic_id])->getResult();
        foreach ($rows as $h) {
            $d = (int) $h->day_of_week;
            $defaults[$d] = [
                'enabled' => 1,
                'open'    => $h->opening_time,
                'close'   => $h->closing_time,
            ];
        }

        return $defaults;
    }
}
