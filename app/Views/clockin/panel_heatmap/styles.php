<style>
/* Estilos para el mapa de calor integrado en la intranet */
.heatmap-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
    margin-bottom: 10px;
}

.weekday {
    text-align: center;
    font-weight: bold;
    color: #6c757d;
    padding: 10px 5px;
    background: #f8f9fa;
    border-radius: 5px;
    font-size: 0.9em;
}

.heatmap-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
}

.heatmap-day {
    aspect-ratio: 1;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 8px;
    position: relative;
    border: 2px solid transparent;
    min-height: 80px;
}

.heatmap-day:hover {
    transform: scale(1.05);
    z-index: 10;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.heatmap-day .day-number {
    font-weight: bold;
    font-size: 14px;
    color: #333;
}

.heatmap-day .day-status {
    text-align: center;
    font-size: 16px;
}

.heatmap-day .day-hours {
    font-size: 10px;
    text-align: center;
    color: #666;
    font-weight: bold;
}

.heatmap-day .day-users {
    font-size: 8px;
    text-align: center;
    color: #555;
    font-weight: normal;
    margin-top: 2px;
    line-height: 1.1;
    max-height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Estados del día */
.heatmap-day.absent {
    background: #f8d7da;
    border-color: #f5c6cb;
}

.heatmap-day.partial {
    background: #fff3cd;
    border-color: #ffeaa7;
}

.heatmap-day.complete {
    background: #d4edda;
    border-color: #c3e6cb;
}

.heatmap-day.overtime {
    background: #d1ecf1;
    border-color: #bee5eb;
}

.heatmap-day.non-work {
    background: #e2e3e5;
    border-color: #d6d8db;
    cursor: default;
}

.heatmap-day.non-work:hover {
    transform: none;
    box-shadow: none;
}

.heatmap-day.empty {
    background: transparent;
    border: none;
    cursor: default;
    visibility: hidden;
}

.heatmap-day.empty:hover {
    transform: none;
    box-shadow: none;
}

/* Leyenda */
.heatmap-legend {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    display: inline-block;
    margin-right: 5px;
}

.heatmap-legend.absent {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
}

.heatmap-legend.partial {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
}

.heatmap-legend.complete {
    background: #d4edda;
    border: 1px solid #c3e6cb;
}

.heatmap-legend.overtime {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
}

.heatmap-legend.non-work {
    background: #e2e3e5;
    border: 1px solid #d6d8db;
}

/* Responsive */
@media (max-width: 768px) {
    .heatmap-grid {
        gap: 3px;
    }
    
    .heatmap-day {
        padding: 4px;
        min-height: 60px;
    }
    
    .heatmap-day .day-number {
        font-size: 12px;
    }
    
    .heatmap-day .day-status {
        font-size: 14px;
    }
    
    .heatmap-day .day-hours {
        font-size: 8px;
    }
    
    .weekday {
        padding: 5px 2px;
        font-size: 12px;
    }
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.heatmap-container {
    animation: fadeIn 0.5s ease-out;
}

/* Modal de pantalla completa */
.modal-fullscreen .modal-content {
    height: 100vh;
    border: none;
    border-radius: 0;
}

.modal-fullscreen .modal-body {
    overflow-y: auto;
    max-height: calc(100vh - 60px);
}

/* Controles en el modal */
.modal-fullscreen .heatmap-controls {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Grid más grande en modal de pantalla completa */
.modal-fullscreen .heatmap-grid {
    gap: 8px;
    padding: 20px;
}

.modal-fullscreen .heatmap-day {
    min-height: 100px;
    padding: 10px;
}

.modal-fullscreen .heatmap-day .day-number {
    font-size: 16px;
}

.modal-fullscreen .heatmap-day .day-status {
    font-size: 20px;
}

.modal-fullscreen .heatmap-day .day-hours {
    font-size: 12px;
}

.modal-fullscreen .heatmap-day .day-users {
    font-size: 10px;
    max-height: 30px;
}
</style>
