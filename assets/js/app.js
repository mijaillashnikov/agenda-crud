/* ==========================================================
   Agenda de Contactos - Interacciones del cliente
   ========================================================== */
document.addEventListener('DOMContentLoaded', () => {

    // 1. Validación de Bootstrap en el navegador (la validación definitiva se hace en PHP)
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // 2. Confirmación visual antes de eliminar
    document.querySelectorAll('form.form-eliminar').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            const nombre = form.dataset.nombre || 'este contacto';

            if (typeof Swal === 'undefined') {
                // Respaldo si el CDN de SweetAlert2 no está disponible
                if (confirm(`¿Eliminar a "${nombre}"? Esta acción no se puede deshacer.`)) form.submit();
                return;
            }

            Swal.fire({
                title: '¿Eliminar contacto?',
                html: `Se eliminará a <strong></strong> de forma permanente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash3"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
                didOpen: popup => {
                    // Insertar el nombre como texto (evita inyección de HTML)
                    popup.querySelector('.swal2-html-container strong').textContent = nombre;
                }
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // 3. Ocultar automáticamente las alertas de éxito
    document.querySelectorAll('.alert-success').forEach(alerta => {
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 4000);
    });
});
