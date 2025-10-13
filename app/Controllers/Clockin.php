<?php

namespace App\Controllers;

use App\Models\Clinic_model;
use App\Models\Clock_in_model;
use App\Models\Clock_connect_model;
use App\Models\ClinicHours_model;
use App\Models\Team_member_job_info_model;
use Config\Services;
use Exception;


class Clockin extends Security_Controller
{

    protected $ClinicDirectory_model, $ClinicHours_model, $Clock_in_model, $Clock_connect_model, $JobInfo_model;

    public function __construct()
    {
        parent::__construct();
        helper('clinics_helper');
        // Cargamos el modelo del directorio de clínicas
        $this->ClinicDirectory_model = new Clinic_model();
        $this->Clock_in_model    = new Clock_in_model();
        $this->Clock_connect_model    = new Clock_connect_model();
        $this->ClinicHours_model = new ClinicHours_model();
        $this->JobInfo_model = new Team_member_job_info_model();
    }

    // Página principal: carga la vista única del CRUD
    public function index()
    {
        // Debug temporal para verificar permisos
        log_message('debug', 'Clockin index - Verificando permisos...');
        log_message('debug', 'Clockin index - is_admin: ' . ($this->login_user->is_admin ? 'true' : 'false'));
        log_message('debug', 'Clockin index - user_id: ' . $this->login_user->id);
        log_message('debug', 'Clockin index - user_type: ' . $this->login_user->user_type);
        log_message('debug', 'Clockin index - permissions: ' . json_encode($this->login_user->permissions));
        log_message('debug', 'Clockin index - can_access_clockin: ' . (can_access_clockin() ? 'true' : 'false'));
        
        // Verificar permisos de Clock-in
        if (!can_access_clockin()) {
            log_message('debug', 'Clockin index - Acceso denegado, redirigiendo a forbidden');
            app_redirect("forbidden");
        }
        
        log_message('debug', 'Clockin index - Acceso permitido, continuando...');

        $request = request(); // Obtener instancia del request
        $clinicId = $request->getGet('clinic');        // equivale a $_GET['clinic']
        $option   = $request->getGet('option') ?? 'attendance'; // valor por defecto
        $user_id   = $request->getGet('user_id'); // valor por defecto
        $date_request   = $request->getGet('date'); // valor por defecto

        $today = date('Y-m-d');
        $from_request = $request->getGet('from') ?? date('Y-m-d', strtotime('-1 month', strtotime($today)));
        $to_request   = $request->getGet('to')   ?? $today;

        if (isset($clinicId)) {
            $data['data_clinic'] = $this->ClinicDirectory_model->get_one($clinicId);
            
            // Solo obtener usuarios que tienen clockin habilitado para esta clínica
            $data['users'] = get_clinic_users_with_clockin($clinicId);
        }
        if (isset($user_id)) {
            $data['data_user'] = get_user_by_id($user_id);
        }

        if (isset($option) && $option == "staff"  && isset($clinicId) && isset($user_id)) {
            // Verificar permisos para ver registro personal
            if (!can_view_clinic_clockin()) {
                app_redirect("forbidden");
            }
            
            // Si solo puede ver su propio marcaje, verificar que sea su usuario
            if (can_view_own_clockin() && !can_view_clinic_clockin() && $user_id != $this->login_user->id) {
                app_redirect("forbidden");
            }

            $connect = $this->Clock_connect_model->get_one_where([
                'clinic_id' => $clinicId,
                'type' => 'connect'
            ]);

            $date = get_today_date();
            if (isset($date_request)) {
                $date = $date_request;
            }

            if (
                $connect &&
                !empty($connect->endpoint) &&
                !empty($connect->api_key) &&
                !empty($connect->api_secret) &&
                !empty($connect->requestid)
            ) {
                $this->syncClockInRecords($clinicId, $user_id, $date);
            }

            $data['request'] = $this->Clock_in_model->get_all_where(['user_id' => $user_id, 'clinic_id' => $clinicId, 'date' => $date])->getResult();
            $data['dataTable'] = $this->getDetalleAsistenciaUsuarioPorFecha($clinicId, $user_id, $date);
            
            // Calcular horas esperadas para el día actual
            $dayOfWeek = date('N'); // 1 = lunes, 7 = domingo
            $data['horas_esperadas_dia'] = get_clinic_hours_for_day($clinicId, $dayOfWeek);
        }

        if (isset($option) && $option == "attendance" && isset($clinicId)) {
            // Verificar permisos para ver registro de asistencia
            if (!can_view_clinic_clockin()) {
                app_redirect("forbidden");
            }
            
            $connect = $this->Clock_connect_model->get_one_where([
                'clinic_id' => $clinicId,
                'type' => 'connect'
            ]);
            log_message('debug', "Encontrando data..." . json_encode($connect));
            
            if (
                $connect &&
                !empty($connect->endpoint) &&
                !empty($connect->api_key) &&
                !empty($connect->api_secret) &&
                !empty($connect->requestid)
            ) {
                log_message('debug', "Log: Sincronizando data...");
                $this->syncClockInRecords($clinicId);
            };
            $date = get_today_date();
            if (isset($date_request)) {
                $date = $date_request;
            }
            $data['request'] = $this->Clock_in_model->get_all_where(['clinic_id' => $clinicId, 'date' => $date])->getResult();
        }

        if (isset($option) && $option == "nomina" && isset($clinicId) && isset($user_id)) {
            // Verificar permisos para ver nómina (solo administradores)
            if (!can_admin_clockin()) {
                app_redirect("forbidden");
            }
            
            $connect = $this->Clock_connect_model->get_one_where([
                'clinic_id' => $clinicId,
                'type' => 'connect'
            ]);

            if (
                $connect &&
                !empty($connect->endpoint) &&
                !empty($connect->api_key) &&
                !empty($connect->api_secret) &&
                !empty($connect->requestid)
            ) {
                $this->syncClockInRecords($clinicId);
            };

            $data['nominas'] = $this->nomina_calcular($user_id, $from_request, $to_request, $clinicId);
        }

        $data['clinics'] = get_user_clinics($this->login_user->id);
        $data['activeOption'] = $option ?? 'attendance';
        $data['login_user'] = $this->login_user;
        
        // Set the correct active option for the view
        if (isset($option) && $option == "staff") {
            $data['activeOption'] = 'staff';
        }

        return $this->template->rander("clockin/index", $data);
    }

    public function requestToken($clinicId)
    {
        $cache = Services::cache();
        $cacheKey = 'crosschex_token_' . $clinicId;

        // 1. Intenta recuperar token de cache
        /*$cachedToken = $cache->get($cacheKey);
        if ($cachedToken && isset($cachedToken['token']) && isset($cachedToken['expires_at'])) {
            // Verifica si sigue vigente
            if (time() < $cachedToken['expires_at']) {
                return [
                    'token'  => $cachedToken['token'],
                    'cached' => true
                ];
            }
        }*/

        $cached = $cache->get($cacheKey);
        if ($cached && isset($cached['token'], $cached['expires_at'])) {
            // Si expiró, lo borramos para forzar nuevo
            if (time() >= $cached['expires_at']) {
                $cache->delete($cacheKey);
            } else {
                return ['token'=>$cached['token'],'cached'=>true];
            }
        }

        
        // 2. Si no hay token o expiró, solicita uno nuevo
        $connect = $this->Clock_connect_model->get_one_where(['clinic_id' => $clinicId, 'type' => 'connect']);
        $client = Services::curlrequest();

        $url = $connect->endpoint;
        $headers = ['Content-Type' => 'application/json'];

        $body = [
            'header' => [
                'nameSpace'  => 'authorize.token',
                'nameAction' => 'token',
                'version'    => '1.0',
                'requestId'  => $connect->requestid,
                'timestamp'  => gmdate('Y-m-d\TH:i:s\Z')
            ],
            'payload' => [
                'api_key'    => $connect->api_key,
                'api_secret' => $connect->api_secret,
            ]
        ];

        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json'    => $body
            ]);

            $result = json_decode($response->getBody(), true);
            $token  = $result['payload']['token'] ?? null;
            $expiresRaw = $result['payload']['expires'] ?? null;

            if (!$token || !$expiresRaw) {
                return ['error' => true, 'message' => 'Token o expiración no presentes'];
            }

            // 3. Calcular segundos hasta expiración
            $expiresAt = strtotime($expiresRaw);
            $ttl = $expiresAt - time(); // tiempo en segundos

            if ($ttl > 0) {
                // Guardar en cache
                $cache->save($cacheKey, [
                    'token'       => $token,
                    'expires_at'  => $expiresAt
                ], $ttl);
            }

            return [
                'token'  => $token,
                'cached' => false
            ];
        } catch (\Exception $e) {
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAttendanceRecords($clinicId, $fecha = null)
    {
        $fecha = $fecha ?? get_today_date();
        $tokenData = $this->requestToken($clinicId);
        log_message('debug', "TokenData: " . json_encode($tokenData));

        if (!isset($tokenData['token'])) {
            return [
                'error'   => true,
                'message' => 'Token no disponible',
                'detail'  => $tokenData
            ];
        }
        $connect = $this->Clock_connect_model->get_one_where(['clinic_id' => $clinicId, 'type' => 'record']);
        log_message('debug', "Connect record: " . json_encode($connect));

        if (!$connect) {
            // No hay configuración de endpoint, devolvemos sin error para no romper la vista
            log_message('error', "No existe configuración record para clínica $clinicId");
            return ['payload' => ['list' => []]];
        }

        $client = Services::curlrequest();
        $url = $connect->endpoint;

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            log_message('error', "Endpoint inválido para clínica {$clinicId}: {$url}");
            return ['payload' => ['list' => []]];
        }

        $headers = [
            'Content-Type' => 'application/json'
        ];

        // Fechas formateadas
        /*$begin = $fecha . 'T00:00:00+00:00';
        $end   = $fecha . 'T23:59:59+00:00';
        log_message('debug', "Rango local: $begin — $end");*/

        // calcular rango de los últimos 5 días (incluye hoy)
        $fechaFinal  = $fecha;
        $fechaInicio = date('Y-m-d', strtotime("$fechaFinal -4 days"));

        // Fechas en UTC (00:00 inicio, 23:59 fin)
        $begin = $fechaInicio . 'T00:00:00+00:00';
        $end   = $fechaFinal   . 'T23:59:59+00:00';

        log_message('debug', "Rango últimos 5 días: $begin — $end");

        // requestId único
        $requestId = bin2hex(random_bytes(16)); // puedes usar tu lógica personalizada

        $body = [
            'header' => [
                'nameSpace'  => 'attendance.record',
                'nameAction' => 'getrecord',
                'version'    => '1.0',
                'requestId'  => $requestId,
                'timestamp'  => gmdate('Y-m-d\TH:i:s\Z')
            ],
            'authorize' => [
                'type'  => 'token',
                'token' => $tokenData['token']
            ],
            'payload' => [
                'begin_time' => $begin,
                'end_time'   => $end,
                'order'      => 'asc',
                'page'       => '1',
                'per_page'   => '100'
            ]
        ];

        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json'    => $body,
                'timeout'     => 10,
                'http_errors' => false,
            ]);
            log_message('debug', "HTTP {$response->getStatusCode()} — Body: " . $response->getBody());
            $decoded = json_decode($response->getBody(), true);
            log_message('debug', "Decoded payload: " . json_encode($decoded['payload']['list'] ?? []));

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            log_message('error', "Excepción cURL: " . $e->getMessage());
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }

    
    public function syncClockInRecords($clinicId, $userId = null, $fecha = null)
    {
        $fecha = $fecha ?? get_today_date();
        $records = $this->getAttendanceRecords($clinicId, $fecha);

        if (!isset($records['payload']['list']) || !is_array($records['payload']['list'])) {
            /*return $this->response->setJSON([
                'success' => false,
                'message' => 'No se encontraron registros o hubo un error',
                'data' => $records
            ]);*/
            log_message('error', 'syncClockInRecords: no payload for clinic ' . $clinicId);
            return false;
        }

        $insertados = 0;

        foreach ($records['payload']['list'] as $entry) {
            $workno = $entry['employee']['workno'] ?? null;

            if ($userId != null) {
                // Filtra solo si el workno coincide con el user_id dado
                if ((string)$workno !== (string)$userId) {
                    continue;
                }
            }
            $clinicOne = $this->ClinicDirectory_model->get_one($clinicId);
            $checktime = $entry['checktime'];
            log_message('error', "Asi me llega el datetime: ".$entry['checktime']);
            $localDateTime = convert_date($checktime,"Y-m-d H:i:s",$clinicOne->schedule);
            $date = get_date_from_datetime($localDateTime);
            $time = get_time_from_datetime($localDateTime);


            // Verifica si ya existe en la tabla (incluyendo registros eliminados para evitar duplicados)
            $existe = $this->Clock_in_model->get_one_where_including_deleted([
                'user_id'   => ($userId === null) ? $workno : $userId ,
                'clinic_id' => $clinicId,
                'date'      => $date,
                'time'      => $time
            ]);

            if (empty($existe->id)) {
                $data = [
                    'user_id'   => ($userId === null) ? $workno : $userId,
                    'clinic_id' => $clinicId,
                    'date'      => $date,
                    'time'      => $time,
                    'remark'    => 'Importado desde CrossChex'
                ];
                $this->Clock_in_model->ci_save($data);
            }

            /*if (!empty($existe->id)) {
                continue;
            }
            
            $data = [
                'user_id'   => ($userId === null) ? $workno : $userId,
                'clinic_id' => $clinicId,
                'date'      => $date,
                'time'      => $time,
                'remark'    => 'Importado desde CrossChex'
            ];
            // Insertar nuevo registro
            $this->Clock_in_model->ci_save($data);*/

            //ATTENDANCE
            $datetime = date(
                'Y-m-d H:i:s',
                strtotime("{$date} {$time}")
            );

            $existsClockIn = $this->Attendance_model
                ->findApprovedByUserOnDate(($userId === null) ? $workno : $userId, $date);


            $CantidadMarcajes = $this->Clock_in_model->count_where_including_deleted([
                'user_id'   => ($userId === null) ? $workno : $userId,
                'clinic_id' => $clinicId,
                'date'      => $date
            ]);

            if (isset($existsClockIn->id)) {
                if ($CantidadMarcajes === 1) {
                    $dataNew = [
                        'in_time' => $datetime,
                    ];
                    $this->Attendance_model->ci_save($dataNew, $existsClockIn->id);
                } else if ($CantidadMarcajes > 1) {
                    $dataNew = [
                        'status' => 'pending',
                        'out_time'      => $datetime,
                        'note'    => 'Salida'
                    ];
                    $this->Attendance_model->ci_save($dataNew, $existsClockIn->id);
                }
            } else {
                $dataNew = [
                    'status' => 'incomplete',
                    'user_id'   => ($userId === null) ? $workno : $userId,
                    'in_time'      => $datetime,
                ];
                $this->Attendance_model->ci_save($dataNew);
            }

            $insertados++;
        }
        return true;
        //return json_decode($insertados, true);
        /*return $this->response->setJSON([
            'success'    => true,
            'insertados' => $insertados
        ]);*/
    }

    public function ajaxSyncLastDays()
{
    $request  = service('request');
    $clinicId = $request->getGet('clinic');
    $days     = (int) ($request->getGet('days') ?? 15);

    if (! $clinicId) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Falta el parámetro clinic'
        ]);
    }

    $today = date('Y-m-d');

    // Iteramos los últimos $days días
    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', strtotime("-{$i} days", strtotime($today)));

        try {
            $ok = $this->syncClockInRecords($clinicId, null, $date);

            if (! $ok) {
                // No hubo registros ese día (no es un error fatal)
                log_message('warning', "ajaxSyncLastDays: sin registros para $date");
            }
        } catch (\Throwable $e) {
            // Si cualquier día lanza excepción, la capturamos y seguimos con el siguiente
            log_message('error', "ajaxSyncLastDays: error sincronizando $date -> ".$e->getMessage());
        }
    }

    return $this->response->setJSON([
        'success' => true,
        'message' => "Sincronizados últimos {$days} días"
    ]);
}

    public function getHorasPorDia()
    {
        $request = service('request');
        $userId = $request->getGet('user_id');
        $clinicId = $request->getGet('clinic_id');
        $from = $request->getGet('from');
        $to = $request->getGet('to');

        if (!$userId || !$clinicId || !$from || !$to) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan parámetros.'
            ]);
        }

        $fromDate = new \DateTime($from);
        $toDate = new \DateTime($to);
        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

        $result = [];

        foreach ($dateRange as $date) {
            $day = $date->format('Y-m-d');
            $dayOfWeek = $date->format('N'); // 1 = lunes, 7 = domingo

            // Obtener horarios de la clínica para este día
            $expectedHours = get_clinic_hours_for_day($clinicId, $dayOfWeek);

            $registros = $this->Clock_in_model->get_all_where([
                'user_id' => $userId,
                'clinic_id' => $clinicId,
                'date' => $day
            ])->getResult();

            $segundos = 0;
            $accion = 'Entrada';
            $ultimaEntrada = null;

            foreach ($registros as $registro) {
                $hora = \DateTime::createFromFormat('H:i:s', $registro->time);
                if ($accion === 'Entrada') {
                    $ultimaEntrada = $hora;
                    $accion = 'Salida';
                } else {
                    if ($ultimaEntrada) {
                        $diff = $hora->getTimestamp() - $ultimaEntrada->getTimestamp();
                        $segundos += $diff;
                    }
                    $accion = 'Entrada';
                }
            }

            $horas = round($segundos / 3600, 2);
            $result[] = [
                'fecha' => $day,
                'horas' => $horas,
                'horas_esperadas' => $expectedHours
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $result
        ]);
    }

    public function getResumenAsistenciaPorClinica()
    {
        $request  = service('request');
        $clinicId = $request->getGet('clinic_id');
        $from     = $request->getGet('from');
        $to       = $request->getGet('to');

        if (! $clinicId || ! $from || ! $to) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parámetros incompletos.'
            ]);
        }

        // Obtener solo usuarios que tienen clockin habilitado para esta clínica
        $usuarios = get_clinic_users_with_clockin($clinicId);

        // 3) Preparar el rango de fechas
        $fromDate  = new \DateTime($from);
        $toDate    = new \DateTime($to);
        $interval  = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($fromDate, $interval, $toDate->modify('+1 day'));

        $datos = [];

        // 4) Calcular horas para cada usuario filtrado
        foreach ($usuarios as $user) {
            $horasTotales = 0;

            foreach ($dateRange as $date) {
                $fecha    = $date->format('Y-m-d');
                $registros = $this->Clock_in_model
                    ->get_all_where([
                        'user_id'   => $user->id,
                        'clinic_id' => $clinicId,
                        'date'      => $fecha
                    ])
                    ->getResult();

                $segundos      = 0;
                $accion        = 'Entrada';
                $ultimaEntrada = null;

                foreach ($registros as $registro) {
                    $hora = \DateTime::createFromFormat('H:i:s', $registro->time);
                    if ($accion === 'Entrada') {
                        $ultimaEntrada = $hora;
                        $accion = 'Salida';
                    } else {
                        if ($ultimaEntrada) {
                            $segundos += $hora->getTimestamp() - $ultimaEntrada->getTimestamp();
                        }
                        $accion = 'Entrada';
                    }
                }

                $horasTotales += round($segundos / 3600, 2);
            }

            // Calcular horas esperadas para este usuario en el rango de fechas
            $horasEsperadas = calculate_expected_hours($clinicId, $from, $to);
            $eficiencia = $horasEsperadas > 0 ? round(($horasTotales / $horasEsperadas) * 100, 2) : 0;

            $datos[] = [
                'nombre' => $user->first_name . ' ' . $user->last_name,
                'rol'    => $user->job_title,
                'horas'  => round($horasTotales, 2),
                'horas_esperadas' => $horasEsperadas,
                'eficiencia' => $eficiencia,
                'foto'   => $user->image
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $datos
        ]);
    }



    public function getDetalleAsistenciaUsuarioPorFecha($clinicId, $userId, $fecha)
    {
        $detalle = [];

        // Usar el helper para obtener horarios de la clínica
        $horario = get_clinic_hours_for_date($clinicId, $fecha);

        if (!$horario) return [];

        $opening = $horario->opening_time;
        $closing = $horario->closing_time;

        $registros = $this->Clock_in_model->get_all_where([
            'user_id' => $userId,
            'clinic_id' => $clinicId,
            'date' => $fecha
        ])->getResult();

        if (count($registros) === 0) return [];

        $horas = array_map(fn($r) => $r->time, $registros);
        sort($horas);

        $entradaReal = $horas[0];
        $entradaDiff = strtotime($entradaReal) - strtotime($opening);

        // Mostrar salida solo si número de registros es par
        $salidaReal = (count($horas) % 2 === 0) ? $horas[count($horas) - 1] : '--';

        // Calcular desfase salida solo si hay al menos 2 registros
        if (count($horas) >= 2 && $salidaReal !== '--') {
            $salidaDiff = strtotime($salidaReal) - strtotime($closing);
            $outOffset = $this->formatearDesfase($salidaDiff);
            $outColor = $salidaDiff > 0 ? '#f8d7da' : '#d4edda';
        } else {
            $outOffset = '--';
            $outColor = '#eeeeee';
        }


        // Calcular horas esperadas basadas en el horario de la clínica
        $horasEsperadas = 0;
        if ($opening && $closing) {
            $openingTime = \DateTime::createFromFormat('H:i:s', $opening);
            $closingTime = \DateTime::createFromFormat('H:i:s', $closing);
            $horasEsperadas = round(($closingTime->getTimestamp() - $openingTime->getTimestamp()) / 3600, 2);
        }

        $detalle[] = [
            'date' => $fecha,
            'check_in' => $entradaReal,
            'check_out' => $salidaReal,
            'late' => $entradaDiff > 0
                ? '<button class="rounded-circle" style="background-color: #f8d7da; color: #721c24; width: 40px; height: 40px; border: none;"><i class="fas fa-times"></i></button><span class="ms-2 text-danger fw-bold">Llegó Tarde</span>'
                : '<button class="rounded-circle" style="background-color: #d4edda; color: #155724; width: 40px; height: 40px; border: none;"><i class="fas fa-check"></i></button><span class="ms-2 text-success fw-bold">Puntual</span>',
            'in_offset' => $this->formatearDesfase($entradaDiff),
            'out_offset' => $outOffset,
            'in_color' => $entradaDiff > 0 ? '#f8d7da' : '#d4edda',
            'out_color' => $outColor,
            'horas_esperadas' => $horasEsperadas
        ];

        return $detalle;
    }


    private function formatearDesfase($segundos)
    {
        $minutos = round(abs($segundos) / 60);
        if ($segundos === 0) return '0 min';
        return ($segundos > 0 ? "+$minutos min" : "-$minutos min");
    }

    public function nomina_calcular($userId, $from, $to, $clinicId = null)
    {
        if (!$userId || !$from || !$to) {
            return $this->response->setJSON(['success' => false, 'message' => 'Faltan datos.']);
        }

        // Obtener clínica del usuario si no se proporciona
        if (!$clinicId) {
            $clinicId = $this->get_user_clinic_id($userId);
        }

        $registros = $this->Clock_in_model->get_by_range($userId, $from, $to);
        $horas = 0;
        $accion = 'Entrada';
        $last = null;

        foreach ($registros as $r) {
            $time = \DateTime::createFromFormat('H:i:s', $r->time);
            if ($accion === 'Entrada') {
                $last = $time;
                $accion = 'Salida';
            } else {
                if ($last) {
                    $diff = $time->getTimestamp() - $last->getTimestamp();
                    $horas += $diff;
                    $last = null;
                }
                $accion = 'Entrada';
            }
        }

        $horasTrabajadas = round($horas / 3600, 2);
        
        // Calcular horas esperadas según horarios de la clínica
        $horasEsperadas = 0;
        if ($clinicId) {
            $horasEsperadas = calculate_expected_hours($clinicId, $from, $to);
        }
        
        $job = $this->JobInfo_model->get_one_where(['user_id' => $userId]);
        $salario = $job ? $job->salary : 0;
        $salarioHora = $salario;

        return [
            'success' => true,
            'total_horas' => $horasTrabajadas,
            'horas_esperadas' => $horasEsperadas,
            'salario_hora' => $salarioHora,
            'total' => $horasTrabajadas * $salarioHora,
            'eficiencia' => $horasEsperadas > 0 ? round(($horasTrabajadas / $horasEsperadas) * 100, 2) : 0
        ];
    }

    private function get_user_clinic_id($userId)
    {
        // Obtener la primera clínica del usuario
        $clinics = get_user_clinics($userId);
        return !empty($clinics) ? $clinics[0]->id : null;
    }

    public function getHeatmapData()
    {
        // Verificar permisos para ver mapa de calor (solo administradores)
        if (!can_admin_clockin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para acceder al mapa de calor.'
            ]);
        }
        
        $request = service('request');
        $clinicId = $request->getGet('clinic_id');
        $month = $request->getGet('month'); // formato Y-m
        $userId = $request->getGet('user_id'); // opcional, para filtrar por usuario específico

        if (!$clinicId || !$month) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan parámetros requeridos.'
            ]);
        }

        // Validar formato del mes
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Formato de mes inválido. Use YYYY-MM'
            ]);
        }

        // Obtener el primer y último día del mes
        $firstDay = $month . '-01';
        $lastDay = date('Y-m-t', strtotime($firstDay));
        
        // Log de debug
        log_message('debug', "getHeatmapData - Mes solicitado: $month");
        log_message('debug', "getHeatmapData - Primer día: $firstDay");
        log_message('debug', "getHeatmapData - Último día: $lastDay");

        // 1) Obtener solo usuarios que tienen clockin habilitado para esta clínica
        $allUsers = get_clinic_users_with_clockin($clinicId);
        $userIds = array_column($allUsers, 'id');

        // Log de debug después de obtener los usuarios
        log_message('debug', "getHeatmapData - Usuarios encontrados: " . count($userIds));
        log_message('debug', "getHeatmapData - IDs de usuarios: " . json_encode($userIds));

        // Si se especifica un usuario específico, filtrar solo ese usuario
        if ($userId) {
            if (in_array($userId, $userIds)) {
                $userIds = [$userId];
            } else {
                // Si el usuario no pertenece a la clínica, devolver array vacío
                $userIds = [];
            }
        }

        $heatmapData = [];

        // Obtener TODOS los registros del mes de una sola vez para optimizar
        $allRegistros = $this->Clock_in_model->get_all_where([
            'clinic_id' => $clinicId,
            'date >=' => $firstDay,
            'date <=' => $lastDay
        ])->getResult();

        // Log de debug para verificar registros encontrados
        log_message('debug', "getHeatmapData - Total registros encontrados: " . count($allRegistros));
        if (!empty($allRegistros)) {
            log_message('debug', "getHeatmapData - Primer registro: " . json_encode($allRegistros[0]));
        }

        // Agrupar registros por fecha y usuario para acceso rápido
        $registrosPorFecha = [];
        foreach ($allRegistros as $registro) {
            $fecha = $registro->date;
            $userId = $registro->user_id;
            if (!isset($registrosPorFecha[$fecha])) {
                $registrosPorFecha[$fecha] = [];
            }
            if (!isset($registrosPorFecha[$fecha][$userId])) {
                $registrosPorFecha[$fecha][$userId] = [];
            }
            $registrosPorFecha[$fecha][$userId][] = $registro;
        }

        // Log de debug para verificar agrupación
        log_message('debug', "getHeatmapData - Fechas con registros: " . implode(', ', array_keys($registrosPorFecha)));
        foreach ($registrosPorFecha as $fecha => $usuarios) {
            log_message('debug', "getHeatmapData - Fecha $fecha tiene " . count($usuarios) . " usuarios");
        }

        // Generar datos para cada día del mes
        $current = new \DateTime($firstDay);
        $end = new \DateTime($lastDay);
        $interval = new \DateInterval('P1D');

        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $dayOfWeek = (int) $current->format('w');
            
            // Obtener horarios esperados para este día
            $expectedHours = get_clinic_hours($clinicId, $dayOfWeek);
            $isWorkDay = $expectedHours && $expectedHours->opening_time && $expectedHours->closing_time;

            $dayData = [
                'date' => $date,
                'day' => (int) $current->format('j'),
                'day_name' => $current->format('l'),
                'is_work_day' => $isWorkDay,
                'expected_hours' => $expectedHours ? $expectedHours->opening_time . ' - ' . $expectedHours->closing_time : null,
                'users' => []
            ];

            // Para cada usuario, obtener sus registros del día (usando datos pre-cargados)
            foreach ($userIds as $userId) {
                $registrosDelDia = $registrosPorFecha[$date][$userId] ?? [];
                
                // Log específico para el 18 de agosto
                if ($date === '2025-08-18') {
                    log_message('debug', "getHeatmapData - 18 de agosto - Usuario $userId tiene " . count($registrosDelDia) . " registros");
                    if (!empty($registrosDelDia)) {
                        log_message('debug', "getHeatmapData - 18 de agosto - Registros: " . json_encode($registrosDelDia));
                    }
                }
                
                $userData = $this->getUserDayDataOptimized($userId, $clinicId, $date, $expectedHours, $registrosDelDia);
                
                // Log específico para el 18 de agosto
                if ($date === '2025-08-18') {
                    log_message('debug', "getHeatmapData - 18 de agosto - Usuario $userId - Attended: " . ($userData['attended'] ? 'true' : 'false') . ", Hours: " . $userData['hours_worked']);
                }
                
                $dayData['users'][] = $userData;
            }

            $heatmapData[] = $dayData;
            $current->add($interval);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $heatmapData,
            'month' => $month,
            'clinic_id' => $clinicId
        ]);
    }

    private function getUserDayDataOptimized($userId, $clinicId, $date, $expectedHours, $registros)
    {
        // Obtener información del usuario
        $user = get_user_by_id($userId);

        $userData = [
            'user_id' => $userId,
            'user_name' => $user ? $user->first_name . ' ' . $user->last_name : 'Usuario ' . $userId,
            'user_avatar' => $user ? $user->image : null,
            'attended' => false,
            'check_in' => null,
            'check_out' => null,
            'hours_worked' => 0,
            'efficiency' => 0,
            'status' => 'absent', // absent, partial, complete, overtime
            'records' => []
        ];

        if (empty($registros)) {
            return $userData;
        }

        // Ordenar registros por hora (igual que en getDetalleAsistenciaUsuarioPorFecha)
        $horas = array_map(fn($r) => $r->time, $registros);
        sort($horas);

        $userData['attended'] = true;
        $userData['check_in'] = $horas[0];
        $userData['check_out'] = count($horas) % 2 === 0 ? $horas[count($horas) - 1] : null;

        // Calcular horas trabajadas usando EXACTAMENTE la misma lógica que nomina_calcular
        $totalSeconds = 0;
        $accion = 'Entrada';
        $last = null;

        foreach ($registros as $registro) {
            $time = \DateTime::createFromFormat('H:i:s', $registro->time);
            $userData['records'][] = [
                'time' => $registro->time,
                'action' => $accion
            ];

            if ($accion === 'Entrada') {
                $last = $time;
                $accion = 'Salida';
            } else {
                if ($last) {
                    $diff = $time->getTimestamp() - $last->getTimestamp();
                    $totalSeconds += $diff;
                    $last = null; // IMPORTANTE: resetear last después de calcular
                }
                $accion = 'Entrada';
            }
        }

        $userData['hours_worked'] = round($totalSeconds / 3600, 2);

        // Calcular eficiencia si hay horarios esperados
        if ($expectedHours && $expectedHours->opening_time && $expectedHours->closing_time) {
            $opening = new \DateTime($expectedHours->opening_time);
            $closing = new \DateTime($expectedHours->closing_time);
            $expectedSeconds = $closing->getTimestamp() - $opening->getTimestamp();
            $expectedHours = $expectedSeconds / 3600;
            
            $userData['efficiency'] = $expectedHours > 0 ? round(($userData['hours_worked'] / $expectedHours) * 100, 2) : 0;
        }

        // Determinar status
        if (!$userData['check_out']) {
            $userData['status'] = 'partial';
        } elseif ($userData['efficiency'] >= 100) {
            $userData['status'] = $userData['efficiency'] > 110 ? 'overtime' : 'complete';
        } else {
            $userData['status'] = 'partial';
        }

        return $userData;
    }

    /**
     * Elimina lógicamente un registro de clockin
     */
    public function delete_clockin_record($id)
    {
        // Verificar que el usuario es administrador
        if (!$this->login_user->is_admin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('access_denied')
            ]);
        }

        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('record_id_required')
            ]);
        }

        // Verificar que el registro existe y no está ya eliminado
        $record = $this->Clock_in_model->get_one_where_including_deleted(['id' => $id]);
        
        if (!$record || empty($record->id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('record_not_found')
            ]);
        }

        if ($record->deleted == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('record_already_deleted')
            ]);
        }

        // Realizar eliminación lógica
        $result = $this->Clock_in_model->soft_delete($id);
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => app_lang('record_deleted_successfully')
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('error_deleting_record')
            ]);
        }
    }

    /**
     * Restaura un registro de clockin eliminado
     */
    public function restore_clockin_record($id)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de registro requerido'
            ]);
        }

        $result = $this->Clock_in_model->restore($id);
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registro restaurado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al restaurar el registro'
            ]);
        }
    }

    private function getUserDayData($userId, $clinicId, $date, $expectedHours)
    {
        // Obtener registros del usuario para este día
        $registros = $this->Clock_in_model->get_all_where([
            'user_id' => $userId,
            'clinic_id' => $clinicId,
            'date' => $date
        ])->getResult();

        // Log de debug para verificar registros
        log_message('debug', "getUserDayData - Usuario: $userId, Fecha: $date, Registros encontrados: " . count($registros));
        if (!empty($registros)) {
            log_message('debug', "getUserDayData - Primer registro: " . json_encode($registros[0]));
            log_message('debug', "getUserDayData - Todos los registros: " . json_encode($registros));
        } else {
            log_message('debug', "getUserDayData - NO HAY REGISTROS para usuario $userId en fecha $date");
        }

        // Obtener información del usuario
        $user = get_user_by_id($userId);

        $userData = [
            'user_id' => $userId,
            'user_name' => $user ? $user->first_name . ' ' . $user->last_name : 'Usuario ' . $userId,
            'user_avatar' => $user ? $user->image : null,
            'attended' => false,
            'check_in' => null,
            'check_out' => null,
            'hours_worked' => 0,
            'efficiency' => 0,
            'status' => 'absent', // absent, partial, complete, overtime
            'records' => []
        ];

        if (empty($registros)) {
            return $userData;
        }

        // Ordenar registros por hora
        $times = array_map(fn($r) => $r->time, $registros);
        sort($times);

        $userData['attended'] = true;
        $userData['check_in'] = $times[0];
        $userData['check_out'] = count($times) % 2 === 0 ? end($times) : null;

        // Calcular horas trabajadas
        $totalSeconds = 0;
        $accion = 'Entrada';
        $lastEntrada = null;

        foreach ($registros as $registro) {
            $time = \DateTime::createFromFormat('H:i:s', $registro->time);
            $userData['records'][] = [
                'time' => $registro->time,
                'action' => $accion
            ];

            if ($accion === 'Entrada') {
                $lastEntrada = $time;
                $accion = 'Salida';
            } else {
                if ($lastEntrada) {
                    $diff = $time->getTimestamp() - $lastEntrada->getTimestamp();
                    $totalSeconds += $diff;
                }
                $accion = 'Entrada';
            }
        }

        $userData['hours_worked'] = round($totalSeconds / 3600, 2);

        // Calcular eficiencia si hay horarios esperados
        if ($expectedHours && $expectedHours->opening_time && $expectedHours->closing_time) {
            $opening = new \DateTime($expectedHours->opening_time);
            $closing = new \DateTime($expectedHours->closing_time);
            $expectedSeconds = $closing->getTimestamp() - $opening->getTimestamp();
            $expectedHours = $expectedSeconds / 3600;
            
            $userData['efficiency'] = $expectedHours > 0 ? round(($userData['hours_worked'] / $expectedHours) * 100, 2) : 0;
        }

        // Determinar status
        if (!$userData['check_out']) {
            $userData['status'] = 'partial';
        } elseif ($userData['efficiency'] >= 100) {
            $userData['status'] = $userData['efficiency'] > 110 ? 'overtime' : 'complete';
        } else {
            $userData['status'] = 'partial';
        }

        return $userData;
    }

    public function heatmapStyles()
    {
        $this->response->setContentType('text/css');
        $this->response->setHeader('Content-Type', 'text/css; charset=utf-8');
        
        // Leer el archivo CSS puro
        $cssFile = APPPATH . 'Views/clockin/heatmap/heatmap.css';
        
        if (file_exists($cssFile)) {
            return $this->response->setBody(file_get_contents($cssFile));
        } else {
            return $this->response->setBody('/* Archivo CSS no encontrado */');
        }
    }

    public function heatmap()
    {
        // Redirigir al panel principal con la opción de mapa de calor
        $request = request();
        $clinicId = $request->getGet('clinic');
        
        if ($clinicId) {
            return redirect()->to(site_url("clockin?clinic={$clinicId}&option=heatmap"));
        } else {
            return redirect()->to(site_url("clockin"));
        }
    }
}