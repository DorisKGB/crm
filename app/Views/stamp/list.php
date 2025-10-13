 <div class="col-md-12">
     <div class="card">
         <div class="card-title d-flex align-items-center">
             <a href="<?= site_url('stamp/main') ?>" style="margin-left:20px !important;" class="text-decoration-none fs-3 me-3 pl-5" aria-label="Volver">
                 <i class="fas fa-arrow-left ml-5"></i>
             </a>
             <div class="text-center flex-grow-1">
                 <h3>
                     <span class="badge badge-primary">Ver Historial</span>
                     Lista de Timbres
                 </h3>
             </div>

         </div>

         <div class="card-body" id="list-stamp">

             <div class="listStamp">
                 <div class="p-2">
                     <input type="text" id="search" class="form-control" placeholder="Busca el timbre...">
                 </div>
                 <!-- NAV DE ESTADOS -->
                 <div class="d-flex align-items-center justify-content-between mb-3">
                     <!-- NAV DE ESTADOS -->
                     <ul class="nav nav-tabs mb-0" id="stampStatusTabs">
                         <li class="nav-item">
                             <a class="nav-link active" href="#" data-status="approved">Aprobadas (0)</a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link" href="#" data-status="pending">Pendientes (0)</a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link" href="#" data-status="denied">Negadas (0)</a>
                         </li>
                     </ul>
                     <!-- Indicador de página -->
                     <div id="pageIndicator" >
                         Página 1 de 1
                     </div>
                 </div>
                 <div class="cards-container"></div>
             </div>

             <!-- PAGINACIÓN en toda la fila -->
             <div class="row">
                 <div class="col-md-12">
                     <nav aria-label="Paginación de timbres" class="d-flex justify-content-end my-3">
                         <ul id="stampPagination" class="pagination pagination-lg"></ul>
                     </nav>

                 </div>
             </div>

         </div>
     </div>
 </div>