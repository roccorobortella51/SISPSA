<?php
// Script para actualizar los botones de acción en todos los archivos index.php

// Directorio base donde buscar los archivos index.php
$baseDir = __DIR__ . '/views/';

// Buscar todos los archivos index.php
$files = [];
$dir = new RecursiveDirectoryIterator($baseDir);
$iterator = new RecursiveIteratorIterator($dir);

foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getFilename()) === 'index.php') {
        $files[] = $file->getPathname();
    }
}

// Si no se encontraron archivos, salir
if (empty($files)) {
    echo "No se encontraron archivos index.php para actualizar.\n";
    exit(1);
}

// Contador de archivos actualizados
$updated = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Verificar si el archivo ya tiene las clases actualizadas
    if (strpos($content, 'action-buttons') !== false) {
        echo "Saltando $file (ya actualizado)\n";
        continue;
    }
    
    // Copia del contenido original para verificar cambios
    $originalContent = $content;
    
    // Reemplazar el contenedor de acciones
    $content = preg_replace(
        "/(['\"]class['\"]\s*=>\s*ActionColumn::class,[\s\S]*?'options'\s*=>\s*\[)[^\]]*\]/",
        "$1'class' => 'action-buttons']",
        $content
    );
    
    // Reemplazar botones con comillas simples
    $content = preg_replace(
        "/('class'\s*=>\s*'btn[^']*')\s*,\s*'style'\s*=>\s*'[^']*'/",
        "'class' => 'btn-action view'",
        $content
    );
    
    // Reemplazar botones con comillas dobles
    $content = preg_replace(
        '/("class"\s*=>\s*"btn[^"]*")\s*,\s*"style"\s*=>\s*"[^"]*"/',
        '"class" => "btn-action view"',
        $content
    );
    
    // Verificar si hubo cambios
    if ($content !== $originalContent) {
        // Hacer una copia de seguridad
        copy($file, $file . '.bak');
        
        // Guardar los cambios
        if (file_put_contents($file, $content) !== false) {
            echo "Actualizado: $file\n";
            $updated++;
        } else {
            echo "Error al actualizar: $file\n";
        }
    } else {
        echo "No se encontraron coincidencias en: $file\n";
    }
}

echo "\nProceso completado. Se actualizaron $updated archivos.\n";
