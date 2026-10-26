</main>
<?php if (function_exists('estaAutenticado') && estaAutenticado()): ?>
<button type="button" class="pwa-install-btn" id="btnInstalarApp">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
    Instalar app
</button>
<?php endif; ?>
<footer class="footer">
    <p>D'Juniors — Sistema de Gestión para Taller Mecánico &copy; <?php echo date('Y'); ?>. Todos los derechos reservados.</p>
</footer>
<script src="<?php echo isset($base) ? $base : ''; ?>js/script.js"></script>
</body>
</html>
