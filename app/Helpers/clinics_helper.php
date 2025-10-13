<?php

use Config\Database;

if (! function_exists('get_user_clinics')) {
    /**
     * Retorna las clínicas asociadas a un usuario,
     * usando el prefijo que esté configurado en database.php
     *
     * @param int $userId
     * @return array
     */
    function get_user_clinics(int $userId): array
    {
        $db           = Database::connect();
        // CI4 usará el DBPrefix configurado en Config/Database.php
        $branchTable  = $db->prefixTable('branch');
        $clinicTable  = $db->prefixTable('clinic_directory');

        $builder = $db->table($branchTable);
        $builder->select("
            {$clinicTable}.id,
            {$clinicTable}.name,
            {$clinicTable}.address,
            {$clinicTable}.phone,
            {$clinicTable}.provider_id,
            {$clinicTable}.logo,
            {$clinicTable}.is_aliada,
            {$clinicTable}.extension,
            {$clinicTable}.fax,
            {$clinicTable}.email,
            {$clinicTable}.photo,
        ");
        $builder->join($clinicTable, "{$branchTable}.id_clinic = {$clinicTable}.id");
        $builder->where("{$branchTable}.id_user", $userId);

        return $builder->get()->getResult();
    }
}

if (! function_exists('get_clinic_users')) {


    function get_clinic_users(int $clinicId): array
    {
        $db          = Database::connect();
        $branchTable = $db->prefixTable('branch');           // e.g. crm_branch
        $usersTable  = $db->prefixTable('users');            // e.g. crm_users

        $builder = $db->table($branchTable);
        $builder->select(
            "{$usersTable}.id,
             {$usersTable}.first_name,
             {$usersTable}.last_name,
             {$usersTable}.email,
             {$usersTable}.job_title,
             {$usersTable}.image,
             "
        );
        $builder->join($usersTable, "{$branchTable}.id_user = {$usersTable}.id");
        $builder->where("{$branchTable}.id_clinic", $clinicId);

        return $builder->get()->getResult();
    }
}


if (! function_exists('get_clinic_users_with_clockin')) {
    /**
     * Obtiene los usuarios de una clínica que tienen clockin habilitado
     *
     * @param int $clinicId ID de la clínica
     * @return array Array de usuarios con clockin habilitado
     */
    function get_clinic_users_with_clockin(int $clinicId): array
    {
        $db          = Database::connect();
        $branchTable = $db->prefixTable('branch');           // e.g. crm_branch
        $usersTable  = $db->prefixTable('users');            // e.g. crm_users

        $builder = $db->table($branchTable);
        $builder->select(
            "{$usersTable}.id,
             {$usersTable}.first_name,
             {$usersTable}.last_name,
             {$usersTable}.email,
             {$usersTable}.job_title,
             {$usersTable}.image,
             "
        );
        $builder->join($usersTable, "{$branchTable}.id_user = {$usersTable}.id");
        $builder->where("{$branchTable}.id_clinic", $clinicId);
        $builder->where("{$branchTable}.clockin", 1); // Solo usuarios con clockin habilitado

        return $builder->get()->getResult();
    }
}


if (! function_exists('get_clinic_user_ids_with_clockin')) {
    /**
     * Obtiene solo los IDs de usuarios de una clínica que tienen clockin habilitado
     *
     * @param int $clinicId ID de la clínica
     * @return array Array de IDs de usuarios con clockin habilitado
     */
    function get_clinic_user_ids_with_clockin(int $clinicId): array
    {
        $db          = Database::connect();
        $branchTable = $db->prefixTable('branch');

        $builder = $db->table($branchTable);
        $builder->select('id_user');
        $builder->where("{$branchTable}.id_clinic", $clinicId);
        $builder->where("{$branchTable}.clockin", 1); // Solo usuarios con clockin habilitado

        $result = $builder->get()->getResult();
        return array_column($result, 'id_user');
    }
}


if(!function_exists('getClinicById')){

    function getClinicById(int $clinicId){
        $db          = Database::connect();
        $clinicTable = $db->prefixTable('clinic_directory');
        $builder = $db->table($clinicTable);
        $builder->where("id", $clinicId);
        return $builder->get()->getRow();
    }
}

if (!function_exists('merge_unique_ids')) {
    /**
     * Fusiona varios arreglos de IDs y elimina duplicados
     *
     * @param array ...$arrays  Arreglos de IDs
     * @return array            IDs únicos
     */
    function merge_unique_ids(array ...$arrays): array
    {
        $merged = [];
        foreach ($arrays as $arr) {
            foreach ($arr as $id) {
                $merged[] = (int) $id;
            }
        }
        return array_values(array_unique($merged));
    }
}
if (!function_exists('get_user_by_id')) {


    function get_user_by_id(int $userId)
    {
        $db = Database::connect();
        $usersTable = $db->prefixTable('users');

        return $db->table($usersTable)
                  ->where('id', $userId)
                  ->get()
                  ->getRow(); 
    }
}

if (!function_exists('get_clinic_hours')) {
    /**
     * Obtiene los horarios de una clínica para un día específico de la semana
     *
     * @param int $clinicId ID de la clínica
     * @param int $dayOfWeek Día de la semana (0=domingo, 1=lunes, ..., 6=sábado)
     * @return object|null Objeto con opening_time y closing_time, o null si no existe
     */
    function get_clinic_hours(int $clinicId, int $dayOfWeek)
    {
        $db = Database::connect();
        $clinicHoursTable = $db->prefixTable('clinic_hours');
        
        return $db->table($clinicHoursTable)
                  ->where('clinic_id', $clinicId)
                  ->where('day_of_week', $dayOfWeek)
                  ->get()
                  ->getRow();
    }
}

if (!function_exists('get_clinic_hours_for_date')) {
    /**
     * Obtiene los horarios de una clínica para una fecha específica
     *
     * @param int $clinicId ID de la clínica
     * @param string $date Fecha en formato Y-m-d
     * @return object|null Objeto con opening_time y closing_time, o null si no existe
     */
    function get_clinic_hours_for_date(int $clinicId, string $date)
    {
        $dayOfWeek = (int) date('w', strtotime($date));
        return get_clinic_hours($clinicId, $dayOfWeek);
    }
}

if (!function_exists('get_clinic_all_hours')) {
    /**
     * Obtiene todos los horarios de una clínica
     *
     * @param int $clinicId ID de la clínica
     * @return array Array de objetos con horarios por día
     */
    function get_clinic_all_hours(int $clinicId)
    {
        $db = Database::connect();
        $clinicHoursTable = $db->prefixTable('clinic_hours');
        
        return $db->table($clinicHoursTable)
                  ->where('clinic_id', $clinicId)
                  ->orderBy('day_of_week')
                  ->get()
                  ->getResult();
    }
}

if (!function_exists('calculate_expected_hours')) {
    /**
     * Calcula las horas esperadas de trabajo para una clínica en un rango de fechas
     *
     * @param int $clinicId ID de la clínica
     * @param string $fromDate Fecha inicio en formato Y-m-d
     * @param string $toDate Fecha fin en formato Y-m-d
     * @return float Total de horas esperadas
     */
    function calculate_expected_hours(int $clinicId, string $fromDate, string $toDate)
    {
        $totalHours = 0;
        $from = new DateTime($fromDate);
        $to = new DateTime($toDate);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($from, $interval, $to->modify('+1 day'));

        foreach ($dateRange as $date) {
            $dayOfWeek = (int) $date->format('w');
            $hours = get_clinic_hours($clinicId, $dayOfWeek);
            
            if ($hours && $hours->opening_time && $hours->closing_time) {
                $opening = new DateTime($hours->opening_time);
                $closing = new DateTime($hours->closing_time);
                $diff = $closing->diff($opening);
                $totalHours += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            }
        }

        return round($totalHours, 2);
    }
}

if (!function_exists('is_work_day')) {
    /**
     * Verifica si un día es laboral para una clínica
     *
     * @param int $clinicId ID de la clínica
     * @param string $date Fecha en formato Y-m-d
     * @return bool True si es día laboral, false en caso contrario
     */
    function is_work_day(int $clinicId, string $date)
    {
        $hours = get_clinic_hours_for_date($clinicId, $date);
        return $hours && $hours->opening_time && $hours->closing_time;
    }
}

if (!function_exists('get_clinic_hours_for_day')) {
    /**
     * Obtiene las horas esperadas de trabajo para un día específico de la semana
     *
     * @param int $clinicId ID de la clínica
     * @param int $dayOfWeek Día de la semana (1=lunes, 2=martes, ..., 7=domingo)
     * @return float Horas esperadas de trabajo
     */
    function get_clinic_hours_for_day(int $clinicId, int $dayOfWeek)
    {
        // Convertir de formato PHP (1=lunes, 7=domingo) a formato de base de datos (0=domingo, 1=lunes, ..., 6=sábado)
        $dbDayOfWeek = $dayOfWeek == 7 ? 0 : $dayOfWeek;
        
        $horario = get_clinic_hours($clinicId, $dbDayOfWeek);
        
        if (!$horario || !$horario->opening_time || !$horario->closing_time) {
            return 8; // Valor por defecto si no hay horario configurado
        }
        
        $openingTime = \DateTime::createFromFormat('H:i:s', $horario->opening_time);
        $closingTime = \DateTime::createFromFormat('H:i:s', $horario->closing_time);
        
        if (!$openingTime || !$closingTime) {
            return 8; // Valor por defecto si hay error en el formato
        }
        
        $horas = ($closingTime->getTimestamp() - $openingTime->getTimestamp()) / 3600;
        return round($horas, 2);
    }
}

if (!function_exists('get_clinic_hours_summary')) {
    /**
     * Obtiene un resumen de horarios de clínica para mostrar en la interfaz
     *
     * @param int $clinicId ID de la clínica
     * @return array Resumen de horarios por día
     */
    function get_clinic_hours_summary(int $clinicId)
    {
        $allHours = get_clinic_all_hours($clinicId);
        $days = [
            0 => 'Domingo',
            1 => 'Lunes', 
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
        
        $summary = [];
        foreach ($allHours as $hour) {
            $summary[] = [
                'day' => $days[$hour->day_of_week] ?? 'Día ' . $hour->day_of_week,
                'opening' => $hour->opening_time,
                'closing' => $hour->closing_time,
                'formatted' => convert_time_to_12hours_format($hour->opening_time) . ' - ' . convert_time_to_12hours_format($hour->closing_time)
            ];
        }
        
        return $summary;
    }
}

if (!function_exists('get_clinic_work_days_count')) {
    /**
     * Cuenta los días laborales de una clínica en un rango de fechas
     *
     * @param int $clinicId ID de la clínica
     * @param string $fromDate Fecha inicio en formato Y-m-d
     * @param string $toDate Fecha fin en formato Y-m-d
     * @return int Número de días laborales
     */
    function get_clinic_work_days_count(int $clinicId, string $fromDate, string $toDate)
    {
        $count = 0;
        $from = new DateTime($fromDate);
        $to = new DateTime($toDate);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($from, $interval, $to->modify('+1 day'));

        foreach ($dateRange as $date) {
            if (is_work_day($clinicId, $date->format('Y-m-d'))) {
                $count++;
            }
        }

        return $count;
    }
}