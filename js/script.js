// D'Juniors - Sistema de Gestión para Taller Mecánico - Script general

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.confirmar-eliminar').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const mensaje = el.getAttribute('data-mensaje') || '¿Está seguro de que desea eliminar este registro?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.alerta').forEach(function (alerta) {
        setTimeout(function () {
            alerta.style.transition = 'opacity 0.6s';
            alerta.style.opacity = '0';
            setTimeout(function () { alerta.remove(); }, 600);
        }, 4500);
    });

    document.querySelectorAll('.fila-refaccion').forEach(function (fila) {
        const cantidad = fila.querySelector('.input-cantidad');
        const precio = fila.querySelector('.input-precio');
        const subtotal = fila.querySelector('.input-subtotal');
        function recalcular() {
            if (cantidad && precio && subtotal) {
                const c = parseFloat(cantidad.value) || 0;
                const p = parseFloat(precio.value) || 0;
                subtotal.value = (c * p).toFixed(2);
                actualizarTotalGeneral();
            }
        }
        if (cantidad) cantidad.addEventListener('input', recalcular);
        if (precio) precio.addEventListener('input', recalcular);
    });

    actualizarTotalGeneral();

    document.querySelectorAll('.buscador-tabla').forEach(function (input) {
        input.addEventListener('keyup', function () {
            const texto = input.value.toLowerCase();
            const tablaId = input.getAttribute('data-tabla');
            const tabla = document.getElementById(tablaId);
            if (!tabla) return;
            tabla.querySelectorAll('tbody tr').forEach(function (fila) {
                fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
            });
        });
    });

    // Mostrar/ocultar contraseña en el login
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(btn.getAttribute('data-target'));
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    // Menú hamburguesa (navegación móvil)
    const navToggle = document.getElementById('navToggle');
    const navPrincipal = document.getElementById('navPrincipal');
    if (navToggle && navPrincipal) {
        navToggle.addEventListener('click', function () {
            const abierto = navPrincipal.classList.toggle('abierta');
            navToggle.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });
        navPrincipal.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                navPrincipal.classList.remove('abierta');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }
});

// ---------- Progressive Web App: Service Worker + instalación ----------
(function () {
    const base = (document.body && document.body.getAttribute('data-base')) || '';

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register(base + 'service-worker.js').catch(function () {
                // Silenciar: el sistema sigue funcionando normalmente sin modo sin conexión.
            });
        });
    }

    let eventoInstalacion = null;
    const btnInstalar = document.getElementById('btnInstalarApp');

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        eventoInstalacion = e;
        if (btnInstalar) btnInstalar.classList.add('visible');
    });

    if (btnInstalar) {
        btnInstalar.addEventListener('click', function () {
            if (!eventoInstalacion) return;
            eventoInstalacion.prompt();
            eventoInstalacion.userChoice.finally(function () {
                eventoInstalacion = null;
                btnInstalar.classList.remove('visible');
            });
        });
    }

    window.addEventListener('appinstalled', function () {
        if (btnInstalar) btnInstalar.classList.remove('visible');
    });
})();

function actualizarTotalGeneral() {
    let total = 0;
    document.querySelectorAll('.input-subtotal').forEach(function (el) {
        total += parseFloat(el.value) || 0;
    });
    const totalEl = document.getElementById('total-general');
    if (totalEl) {
        totalEl.textContent = total.toFixed(2);
    }
}
