<!-- Modal -->
<?php
// Crear un objeto DateTime a partir de la fecha
function transformDate ($date){
    $fecha = new DateTime($date);
    // Arreglos con los días de la semana y los meses en español
    $dias = array('domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado');
    $meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

    // Obtener el día de la semana y el mes en español
    $diaSemana = $dias[$fecha->format('w')];
    $mes = $meses[$fecha->format('n') - 1];

    // Formatear la fecha en el formato deseado
    $fechaFormateada = $diaSemana . ', ' . $fecha->format('d') . ' de ' . $mes . ' de ' . $fecha->format('Y') . ' a las ' . $fecha->format('h:iA');
    return $fechaFormateada;
}

?>

<div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" data-bs-focus="false" aria-modal="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo app_lang('log_edit') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div>
            <input type="text" id="searchInputLog" class="form-control mb-2" placeholder="<?php echo app_lang('search'); ?>">
        </div>
        <ul class="list-group" id="logList">

            <?php foreach ($logs as $log): ?>
            <li class="list-group-item mb-1" style="background-color: #e5faff !important;">
                <?php echo  $log->comment . " <br/> <small> Fecha Edición: " . transformDate($log->date)."</small>"; ?>
            </li>
            <?php endforeach; ?>
        </ul>        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Obtener el input y la lista
  const searchInput = document.getElementById('searchInputLog');
  const logList = document.getElementById('logList');

  // Evento para filtrar los elementos según la búsqueda
  searchInput.addEventListener('input', function() {
    const filter = searchInput.value.toLowerCase();  // Obtener el texto de búsqueda
    const items = logList.getElementsByTagName('li');  // Obtener todos los elementos de la lista

    // Recorrer los elementos de la lista
    for (let i = 0; i < items.length; i++) {
      const text = items[i].textContent || items[i].innerText;  // Obtener el texto de cada item
      if (text.toLowerCase().includes(filter)) {
        items[i].style.display = '';  // Mostrar el item si contiene el texto de búsqueda
      } else {
        items[i].style.display = 'none';  // Ocultar el item si no contiene el texto de búsqueda
      }
    }
  });
</script>