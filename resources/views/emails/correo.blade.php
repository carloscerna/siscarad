<!DOCTYPE html>
<html>
<head>
    <title>Correo con Adjunto e Imagen</title>
</head>
<body>
    <p>Hola,</p>
    <p>Este es un correo con un archivo adjunto y una imagen embebida.</p>
    <p>Atentamente,</p>
    <p><b>{{ $nombre }}</b></p>
    <img src="{{ $cid }}" width="200" alt="Logo">
</body>
</html>
