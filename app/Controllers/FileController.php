<?php

namespace App\Controllers;


class FileController extends Security_Controller
{
    public function download($filename)
    {
        //$session = session();
        echo "<h1>PDF</h1>";
        /*// Verificar si el usuario está autenticado
        if (!$session->get('isLoggedIn')) {
            // Si el usuario no está logueado, redirigir al login
            return redirect()->to('/login')->with('error', 'Acceso no autorizado');
        }

        // Ruta al archivo dentro de writable/uploads
        $filePath = WRITEPATH . 'uploads/' . basename($filename); // Usamos WRITEPATH para asegurarnos de que estamos en el directorio correcto

        // Verificar si el archivo existe
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Archivo no encontrado');
        }

        // Enviar el archivo al navegador
        return $this->response->download($filePath, null)->setFileName($filename);*/
    }
}
