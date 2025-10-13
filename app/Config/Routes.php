<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Dashboard::index');
$routes->post('heartbeat', 'Heartbeat::index');
$routes->post('heartbeat/check', 'Heartbeat::check');
$routes->get('heartbeat/services', 'Heartbeat::check_services');
$routes->post('heartbeat/cleanup', 'Heartbeat::cleanup');

//custom routing for custom pages
//this route will move 'about/any-text' to 'domain.com/about/index/any-text'
$routes->add('about/(:any)', 'About::index/$1');

//add routing for controllers
$excluded_controllers = array("About", "App_Controller", "Security_Controller");
$controller_dropdown = array();
$dir = "./app/Controllers/";
if (is_dir($dir)) {
  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      $controller_name = substr($file, 0, -4);
      if ($file && $file != "." && $file != ".." && $file != "index.html" && $file != ".gitkeep" && !in_array($controller_name, $excluded_controllers)) {
        $controller_dropdown[] = $controller_name;
      }
    }
    closedir($dh);
  }
}

foreach ($controller_dropdown as $controller) {
  $routes->get(strtolower($controller), "$controller::index");
  $routes->get(strtolower($controller) . '/(:any)', "$controller::$1");
  $routes->post(strtolower($controller) . '/(:any)', "$controller::$1");
}

$routes->get('getViewReport/(:any)', 'Daily_report::getViewReport/$1');
//add uppercase links
$routes->get("Plugins", "Plugins::index");
$routes->get("Plugins/(:any)", "Plugins::$1");
$routes->post("Plugins/(:any)", "Plugins::$1");

$routes->get("Updates", "Updates::index");
$routes->get("Updates/(:any)", "Updates::$1");
$routes->post("Updates/(:any)", "Updates::$1");
$routes->post('/Daily_report/save', 'Daily_report::saveReport');
$routes->get('store/process_order/(:any)/ssKey', 'Store::process_order/$1/ssKey');

//$routes->get('excuse/create', 'Excuse::create');
$routes->get('excuse/listAjax', 'Excuse::listAjax');
$routes->get('excuse/showAjax/(:num)', 'Excuse::showAjax/$1');
$routes->post('excuse/storeAjax', 'Excuse::storeAjax');
$routes->post('excuse/updateAjax/(:num)', 'Excuse::updateAjax/$1');
$routes->get('excuse/approveAjax/(:num)', 'Excuse::approveAjax/$1');
$routes->get('excuse/denyAjax/(:num)', 'Excuse::denyAjax/$1');


$routes->get('excuse/approvePdfAjax/(:num)', 'Excuse::approvePdfAjax/$1');
$routes->get('excuse/generatePdf/(:num)', 'Excuse::generatePdf/$1');
$routes->get('excuse/clinicsAjax', 'Excuse::clinicsAjax');
$routes->get('api/excuse/validateByPrefix', 'PublicExcuseApi::validateByPrefix');
$routes->get('document/excuse/generatePdfByToken', 'PublicExcuseApi::generatePdfByToken');

$routes->get('directory', 'Directory::index');
$routes->post('directory/loadClinics', 'Directory::loadClinics');
$routes->get('directory/getClinic/(:num)', 'Directory::getClinic/$1');
$routes->post('directory/save', 'Directory::save');
$routes->get('directory/delete/(:num)', 'Directory::delete/$1');
$routes->post('webhooks/crosschex/(:segment)', 'Webhooks_listener::crosschex_attendance/$1');
$routes->get('api/asistencia/historico', 'Clockin::getHorasPorDia');
$routes->get('alertas/marcajes', 'Cron::alertaMarcajes');

$routes->post('stamp/create', 'Stamp::create');
$routes->post('stamptemplate/create', 'StampTemplate::create');
$routes->get('api/get-conference/(:any)/(:any)', 'Api_Controller::apiGetConference/$1/$2');
$routes->options('api/get-conference/(:any)/(:any)', 'Api_Controller::apiGetConference/$1/$2');
$routes->get('api/get-us-states', 'Api_Controller::get_us_states');
$routes->options('api/get-us-states', 'Api_Controller::get_us_states');
$routes->get('api/validate-us-address', 'Api_Controller::validate_us_address');
$routes->options('api/validate-us-address', 'Api_Controller::validate_us_address');
$routes->get('api/save-service-data', 'Api_Controller::save_service_data');
$routes->options('api/save-service-data', 'Api_Controller::save_service_data');

$routes->get('api/end_call_confirmed', 'Api_Controller::end_call_confirmed');
$routes->options('api/end_call_confirmed', 'Api_Controller::end_call_confirmed');

$routes->get('api/get_user_by_vsee_username', 'Api_Controller::get_user_by_vsee_username');
$routes->options('api/get_user_by_vsee_username', 'Api_Controller::get_user_by_vsee_username');

$routes->get('api/set_user_in_call', 'Api_Controller::set_user_in_call');
$routes->options('api/set_user_in_call', 'Api_Controller::set_user_in_call');

$routes->get('api/set_user_available', 'Api_Controller::set_user_available');
$routes->options('api/set_user_available', 'Api_Controller::set_user_available');

$routes->get('api/check_call_status_by_meeting', 'Api_Controller::check_call_status_by_meeting');
$routes->options('api/check_call_status_by_meeting', 'Api_Controller::check_call_status_by_meeting');

$routes->get('api/set_call_failed', 'Api_Controller::set_call_failed');
$routes->options('api/set_call_failed', 'Api_Controller::set_call_failed');

$routes->get('api/send_heartbeat', 'Api_Controller::send_heartbeat');
$routes->options('api/send_heartbeat', 'Api_Controller::send_heartbeat');

$routes->get('api/get_active_participants', 'Api_Controller::get_active_participants');
$routes->options('api/get_active_participants', 'Api_Controller::get_active_participants');

$routes->get('api/clear_heartbeat', 'Api_Controller::clear_heartbeat');
$routes->options('api/clear_heartbeat', 'Api_Controller::clear_heartbeat');

// FCM
$routes->get('api/save-fcm-token', 'Api_Controller::save_fcm_token'); 
$routes->post('api/save-fcm-token', 'Api_Controller::save_fcm_token');
$routes->options('api/save-fcm-token', 'Api_Controller::save_fcm_token');

// Push Notifications
$routes->post('api/send-push-notification', 'Api_Controller::send_push_notification');
$routes->options('api/send-push-notification', 'Api_Controller::send_push_notification');

$routes->get('firmas/(:any)', function($filename) {
  $path = WRITEPATH . 'firmas/' . $filename;

  if (!file_exists($path)) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
  }

  $mime = mime_content_type($path);
  $response = service('response');
  return $response->setHeader('Content-Type', $mime)->setBody(file_get_contents($path));
});


$routes->get('uploads/(:any)', function($filepath) {
  // Construimos la ruta completa: asumimos que "uploads" está en la raíz pública (FCPATH)
  $path = WRITEPATH . 'uploads/' . $filepath;

  if (!file_exists($path)) {
      throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
  }

  $mime = mime_content_type($path);
  $response = service('response');
  return $response->setHeader('Content-Type', $mime)
                  ->setBody(file_get_contents($path));
});
$routes->get('stamp/listAjax2/(:any)/(:num)/(:num)', 'Stamp::listAjax2/$1/$2/$3');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
  require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
