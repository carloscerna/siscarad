<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Envío de Boleta Escolar</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #1a56db;">Control de Calificaciones SISCARAD</h2>
    <p>Estimado(a) estudiante,</p>
    <p>Se te hace entrega de tu boleta de calificaciones oficial en formato digital de manera adjunta a este mensaje.</p>
    <p><strong>Detalles del receptor:</strong></p>
    <ul>
        <li><strong>NIE:</strong> {{ $alumno->codigo_nie }}</li>
    </ul>
    <br>
    <p><em>Por favor no respondas a este correo automático.</em></p>
    <hr>
    <p style="font-size: 12px; color: #777;">Enviado desde la cuenta institucional del Centro Escolar.</p>
</body>
</html>