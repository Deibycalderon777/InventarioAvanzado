<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .main-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card.pink { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        
        .table-hover tbody tr:hover { 
            background-color: #f8f9fa; 
            cursor: pointer;
        }
        .badge-stock-bajo { background-color: #dc3545; }
        .badge-stock-ok { background-color: #28a745; }
        
        .alert-stock {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            animation: slideIn 0.5s;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in {
            animation: fadeIn 0.5s;
        }
        
        .modal-backdrop.show {
            opacity: 0.5;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .action-btn {
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-boxes"></i> Sistema de Inventario <span class="badge bg-success">Abarrotes B</span></h1>
                 
                </div>
                <div>
                    <button class="btn btn-primary btn-lg" onclick="abrirModalProducto()">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                    <button class="btn btn-success btn-lg" onclick="exportarInventario()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <button class="btn btn-info btn-lg" onclick="cargarDatos()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4" id="estadisticas">
                <!-- Se cargan dinámicamente -->
            </div>

            <!-- Alertas de Stock Bajo -->
            <div id="alertasStock"></div>

            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <input type="text" id="buscar" class="form-control" 
                           placeholder="🔍 Buscar por nombre o código..." 
                           onkeyup="filtrarProductos()">
                </div>
                <div class="col-md-4">
                    <select id="categoriaFiltro" class="form-select" onchange="filtrarProductos()">
                        <option value="">Todas las Categorías</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div class="loading" id="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando datos...</p>
            </div>

            <!-- Tabla de Productos -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Mínimo</th>
                            <th>P. Compra</th>
                            <th>P. Venta</th>
                            <th>Valor Total</th>
                            <th>Proveedor</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProductos">
                        <!-- Se carga dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar/Editar Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">
                        <i class="fas fa-plus-circle"></i> Nuevo Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formProducto">
                        <input type="hidden" id="productoId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código *</label>
                                <input type="text" id="codigo" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" id="nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría *</label>
                                <input type="text" id="categoria" class="form-control" list="listaCategorias" required>
                                <datalist id="listaCategorias"></datalist>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Cantidad *</label>
                                <input type="number" id="cantidad" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Stock Mínimo *</label>
                                <input type="number" id="minimo" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio de Compra *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="precio_compra" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio de Venta *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="precio_venta" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Proveedor</label>
                                <input type="text" id="proveedor" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ubicación</label>
                                <input type="text" id="ubicacion" class="form-control" placeholder="A-1">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarProducto()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let productos = [];
        let categorias = [];
        let modal;

        // Inicializar al cargar la página
        $(document).ready(function() {
            modal = new bootstrap.Modal(document.getElementById('modalProducto'));
            cargarDatos();
        });

        // Cargar todos los datos
        function cargarDatos() {
            mostrarLoading(true);
            
            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: { accion: 'obtener_todo' },
                dataType: 'json',
                success: function(response) {
                    productos = response.productos;
                    categorias = response.categorias;
                    
                    actualizarEstadisticas(response.estadisticas);
                    actualizarAlertasStock(response.productos_bajo_stock);
                    actualizarCategorias();
                    mostrarProductos(productos);
                    
                    mostrarLoading(false);
                },
                error: function() {
                    mostrarToast('Error al cargar los datos', 'danger');
                    mostrarLoading(false);
                }
            });
        }

        // Mostrar/Ocultar Loading
        function mostrarLoading(mostrar) {
            if (mostrar) {
                $('#loading').addClass('active');
                $('#tablaProductos').html('');
            } else {
                $('#loading').removeClass('active');
            }
        }

        // Actualizar estadísticas
        function actualizarEstadisticas(stats) {
            const html = `
                <div class="col-md-3">
                    <div class="stat-card purple fade-in">
                        <h5><i class="fas fa-box"></i> Total Productos</h5>
                        <h2>${stats.total_productos || 0}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card pink fade-in">
                        <h5><i class="fas fa-dollar-sign"></i> Valor Inventario</h5>
                        <h2>$${parseFloat(stats.valor_inventario || 0).toFixed(2)}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card blue fade-in">
                        <h5><i class="fas fa-chart-line"></i> Ventas Potenciales</h5>
                        <h2>$${parseFloat(stats.ventas_potenciales || 0).toFixed(2)}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card orange fade-in">
                        <h5><i class="fas fa-exclamation-triangle"></i> Stock Bajo</h5>
                        <h2>${stats.productos_bajo_stock || 0}</h2>
                    </div>
                </div>
            `;
            $('#estadisticas').html(html);
        }

        // Actualizar alertas de stock
        function actualizarAlertasStock(productosBajo) {
            if (productosBajo && productosBajo.length > 0) {
                let html = '<div class="alert-stock"><h5><i class="fas fa-exclamation-triangle"></i> ⚠️ Productos con Stock Bajo</h5><ul class="mb-0">';
                productosBajo.forEach(p => {
                    html += `<li><strong>${p.nombre}</strong> - Stock: ${p.cantidad} (Mínimo: ${p.minimo})</li>`;
                });
                html += '</ul></div>';
                $('#alertasStock').html(html);
            } else {
                $('#alertasStock').html('');
            }
        }

        // Actualizar categorías en el filtro
        function actualizarCategorias() {
            let html = '<option value="">Todas las Categorías</option>';
            categorias.forEach(cat => {
                html += `<option value="${cat}">${cat}</option>`;
            });
            $('#categoriaFiltro').html(html);
            
            // También actualizar datalist
            let datalistHtml = '';
            categorias.forEach(cat => {
                datalistHtml += `<option value="${cat}">`;
            });
            $('#listaCategorias').html(datalistHtml);
        }

        // Mostrar productos en la tabla
        function mostrarProductos(listaProductos) {
            let html = '';
            
            if (listaProductos.length === 0) {
                html = '<tr><td colspan="11" class="text-center py-4">No hay productos para mostrar</td></tr>';
            } else {
                listaProductos.forEach(p => {
                    const stockBadge = p.cantidad <= p.minimo ? 'badge-stock-bajo' : 'badge-stock-ok';
                    const valorTotal = (p.cantidad * p.precio_compra).toFixed(2);
                    
                    html += `
                        <tr class="fade-in">
                            <td><code>${p.codigo}</code></td>
                            <td><strong>${p.nombre}</strong></td>
                            <td><span class="badge bg-info">${p.categoria}</span></td>
                            <td><span class="badge ${stockBadge}">${p.cantidad}</span></td>
                            <td>${p.minimo}</td>
                            <td>$${parseFloat(p.precio_compra).toFixed(2)}</td>
                            <td><strong>$${parseFloat(p.precio_venta).toFixed(2)}</strong></td>
                            <td>$${valorTotal}</td>
                            <td><small>${p.proveedor || '-'}</small></td>
                            <td><code>${p.ubicacion || '-'}</code></td>
                            <td>
                                <button class="btn btn-sm btn-warning action-btn" onclick="editarProducto(${p.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger action-btn" onclick="eliminarProducto(${p.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            
            $('#tablaProductos').html(html);
        }

        // Filtrar productos
        function filtrarProductos() {
            const busqueda = $('#buscar').val().toLowerCase();
            const categoria = $('#categoriaFiltro').val();
            
            let productosFiltrados = productos.filter(p => {
                const matchBusqueda = p.nombre.toLowerCase().includes(busqueda) || 
                                     p.codigo.toLowerCase().includes(busqueda);
                const matchCategoria = !categoria || p.categoria === categoria;
                return matchBusqueda && matchCategoria;
            });
            
            mostrarProductos(productosFiltrados);
        }

        // Limpiar filtros
        function limpiarFiltros() {
            $('#buscar').val('');
            $('#categoriaFiltro').val('');
            mostrarProductos(productos);
        }

        // Abrir modal para nuevo producto
        function abrirModalProducto() {
            $('#modalTitulo').html('<i class="fas fa-plus-circle"></i> Nuevo Producto');
            $('#formProducto')[0].reset();
            $('#productoId').val('');
            modal.show();
        }

        // Editar producto
        function editarProducto(id) {
            const producto = productos.find(p => p.id == id);
            if (producto) {
                $('#modalTitulo').html('<i class="fas fa-edit"></i> Editar Producto');
                $('#productoId').val(producto.id);
                $('#codigo').val(producto.codigo);
                $('#nombre').val(producto.nombre);
                $('#categoria').val(producto.categoria);
                $('#cantidad').val(producto.cantidad);
                $('#minimo').val(producto.minimo);
                $('#precio_compra').val(producto.precio_compra);
                $('#precio_venta').val(producto.precio_venta);
                $('#proveedor').val(producto.proveedor);
                $('#ubicacion').val(producto.ubicacion);
                modal.show();
            }
        }

        // Guardar producto (crear o actualizar)
        function guardarProducto() {
            const id = $('#productoId').val();
            const accion = id ? 'actualizar' : 'crear';
            
            const datos = {
                accion: accion,
                id: id,
                codigo: $('#codigo').val(),
                nombre: $('#nombre').val(),
                categoria: $('#categoria').val(),
                cantidad: $('#cantidad').val(),
                minimo: $('#minimo').val(),
                precio_compra: $('#precio_compra').val(),
                precio_venta: $('#precio_venta').val(),
                proveedor: $('#proveedor').val(),
                ubicacion: $('#ubicacion').val()
            };
            
            $.ajax({
                url: 'https://inventarioavanzado-1.onrender.com/api.php',
                method: 'POST',
                data: datos,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        modal.hide();
                        cargarDatos();
                        mostrarToast(response.message, 'success');
                    } else {
                        mostrarToast(response.message || 'Error al guardar', 'danger');
                    }
                },
                error: function() {
                    mostrarToast('Error de conexión', 'danger');
                }
            });
        }

        // Eliminar producto
        function eliminarProducto(id) {
            if (confirm('¿Estás seguro de eliminar este producto?')) {
                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    data: { accion: 'eliminar', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            cargarDatos();
                            mostrarToast(response.message, 'success');
                        } else {
                            mostrarToast(response.message || 'Error al eliminar', 'danger');
                        }
                    },
                    error: function() {
                        mostrarToast('Error de conexión', 'danger');
                    }
                });
            }
        }

        // Exportar inventario
        function exportarInventario() {
            window.location.href = 'api.php?accion=exportar';
            mostrarToast('Descargando inventario...', 'info');
        }

        // Mostrar toast de notificación
        function mostrarToast(mensaje, tipo = 'info') {
            const iconos = {
                success: 'check-circle',
                danger: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            
            const toast = `
                <div class="toast align-items-center text-white bg-${tipo} border-0 fade show" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${iconos[tipo]}"></i> ${mensaje}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            $('.toast-container').append(toast);
            
            setTimeout(() => {
                $('.toast').first().remove();
            }, 3000);
        }
    </script>
</body>
</html>
