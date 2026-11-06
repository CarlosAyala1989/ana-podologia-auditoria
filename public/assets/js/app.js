/**
 * Lógica de Cliente y Experiencia Táctil (UI/UX)
 * Podología Ana Ramírez
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Prevención de doble envío accidental en pantallas táctiles
    const formulario = document.getElementById('formRegistroPaciente');
    const btnGuardar = document.getElementById('btnGuardar');

    if (formulario && btnGuardar) {
        formulario.addEventListener('submit', () => {
            btnGuardar.disabled = true;
            btnGuardar.classList.add('opacity-75', 'cursor-not-allowed');
            btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Procesando Ficha...';
        });
    }

    // 2. Ejecutar chequeo inicial de riesgo clínico (en caso de recarga o back del navegador)
    if (typeof actualizarAlertaRiesgo === 'function') {
        actualizarAlertaRiesgo();
    }
});
