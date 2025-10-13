<?php
// Variables esperadas:
//   $type          = 'created'|'approved'|'denied'
//   $stamp         = objeto con los campos del timbre
//   $provider      = objeto del proveedor (solo en approved/denied)
//   $creatorName   = nombre completo quien creó (solo en created)
//   $stampUrl      = URL para “Ver Timbre”

// Mapea colores y títulos según tipo

$providerName = isset($provider) ? $provider->name : '';

$map = [
    'created'  => ['color' => '#007bff', 'title' => 'Nueva solicitud de Timbre'],
    'approved' => ['color' => '#009933', 'title' => "{$providerName} ha APROBADO el Timbre #{$stamp->id}"],
    'denied'   => ['color' => '#ff1a1a', 'title' => "{$providerName} ha DENEGADO el Timbre #{$stamp->id}"],
];
$headerColor = $map[$type]['color'];
$headerTitle = $map[$type]['title'];
$introText   = $type === 'created'
    ? 'Se ha generado un nuevo timbre con estos datos:'
    : 'Información del Timbre:';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= esc($headerTitle) ?></title>
</head>

<body style="background:#f4f4f4;margin:0;padding:20px;font-family:Arial,sans-serif">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden">
                    <tr>
                        <td style="background:<?= $headerColor ?>;padding:20px;color:#fff;text-align:center">
                            <h1 style="margin:0;font-size:22px"><?= esc($headerTitle) ?></h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px;color:#333">
                            <p>Hola,</p>
                            <p><?= $introText ?></p>
                            <ul style="list-style:none;padding:0">
                                <li><strong>ID:</strong> <?= esc($stamp->id) ?></li>
                                <li><strong>Descripción:</strong> <?= esc($stamp->description) ?></li>
                                <?php if ($type === 'created'): ?>
                                    <li><strong>Creado por:</strong> <?= esc($creatorName) ?></li>
                                <?php endif ?>
                                <?php if (in_array($type, ['approved', 'denied'])): ?>
                                    <li><strong>Provider:</strong> <?= esc($providerName) ?></li>
                                <?php endif ?>
                                <li><strong>Clínica:</strong> <?= esc($stamp->clinic_select) ?></li>
                                <li><strong>Fecha:</strong> <?= esc($stamp->created_at) ?></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px;text-align:center">
                            <a href="<?= esc($stampUrl) ?>"
                                style="display:inline-block;padding:12px 24px;background:#28a745;color:#fff;
                      text-decoration:none;border-radius:4px;font-weight:bold">
                                Ver Timbre
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f4f4f4;padding:15px;text-align:center;
                     color:#777;font-size:12px">
                            <p>Si no solicitaste este correo, puedes ignorarlo.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>