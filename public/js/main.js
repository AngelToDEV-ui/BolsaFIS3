/* ===================================
   JAVASCRIPT PRINCIPAL
   Bolsa de Trabajo FIS-UNCP
   =================================== */

// Utilidades generales
const Utils = {
    // Validar email
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validar DNI
    isValidDNI: function(dni) {
        const dniRegex = /^[0-9]{8}$/;
        return dniRegex.test(dni) && !dni.startsWith('00');
    },

    // Validar RUC
    isValidRUC: function(ruc) {
        const rucRegex = /^[0-9]{11}$/;
        return rucRegex.test(ruc) && (ruc.startsWith('10') || ruc.startsWith('20'));
    },

    // Validar nombres (solo letras y espacios)
    isValidName: function(name) {
        const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        return nameRegex.test(name);
    },

    // Calcular edad desde año
    calculateAge: function(birthYear) {
        const currentYear = new Date().getFullYear();
        return currentYear - birthYear;
    },

    // Mostrar mensaje de error
    showError: function(element, message) {
        element.classList.add('error');
        let errorDiv = element.parentNode.querySelector('.form-text.error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-text error';
            element.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    },

    // Limpiar errores
    clearError: function(element) {
        element.classList.remove('error');
        const errorDiv = element.parentNode.querySelector('.form-text.error');
        if (errorDiv) {
            errorDiv.remove();
        }
    },

    // Mostrar alerta
    showAlert: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-remove después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }
};

// Validación de formularios
const FormValidator = {
    // Validar formulario de registro de estudiante
    validateStudentForm: function(form) {
        let isValid = true;
        const formData = new FormData(form);

        // Validar nombres
        const nombres = formData.get('nombres');
        const nombresInput = form.querySelector('[name="nombres"]');
        if (!nombres || !Utils.isValidName(nombres)) {
            Utils.showError(nombresInput, 'Los nombres solo pueden contener letras y espacios');
            isValid = false;
        } else {
            Utils.clearError(nombresInput);
        }

        // Validar apellidos
        const apellidos = formData.get('apellidos');
        const apellidosInput = form.querySelector('[name="apellidos"]');
        if (!apellidos || !Utils.isValidName(apellidos)) {
            Utils.showError(apellidosInput, 'Los apellidos solo pueden contener letras y espacios');
            isValid = false;
        } else {
            Utils.clearError(apellidosInput);
        }

        // Validar DNI
        const dni = formData.get('dni');
        const dniInput = form.querySelector('[name="dni"]');
        if (!Utils.isValidDNI(dni)) {
            Utils.showError(dniInput, 'DNI debe tener 8 dígitos y no comenzar con 00');
            isValid = false;
        } else {
            Utils.clearError(dniInput);
        }

        // Validar email
        const correo = formData.get('correo');
        const correoInput = form.querySelector('[name="correo"]');
        if (!Utils.isValidEmail(correo)) {
            Utils.showError(correoInput, 'Ingrese un correo válido');
            isValid = false;
        } else {
            Utils.clearError(correoInput);
        }

        // Validar año de nacimiento y edad
        const anioNacimiento = parseInt(formData.get('anio_nacimiento'));
        const anioInput = form.querySelector('[name="anio_nacimiento"]');
        const edad = Utils.calculateAge(anioNacimiento);
        if (edad < 16) {
            Utils.showError(anioInput, 'Debe tener al menos 16 años');
            isValid = false;
        } else {
            Utils.clearError(anioInput);
        }

        // Validar contraseña
        const contrasena = formData.get('contrasena');
        const contrasenaInput = form.querySelector('[name="contrasena"]');
        if (contrasena.length < 6) {
            Utils.showError(contrasenaInput, 'La contraseña debe tener al menos 6 caracteres');
            isValid = false;
        } else {
            Utils.clearError(contrasenaInput);
        }

        return isValid;
    },

    // Validar formulario de registro de empresa
    validateCompanyForm: function(form) {
        let isValid = true;
        const formData = new FormData(form);

        // Validar nombre de empresa
        const nombre = formData.get('nombre');
        const nombreInput = form.querySelector('[name="nombre"]');
        if (!nombre || nombre.trim().length < 3) {
            Utils.showError(nombreInput, 'El nombre de la empresa debe tener al menos 3 caracteres');
            isValid = false;
        } else {
            Utils.clearError(nombreInput);
        }

        // Validar RUC
        const ruc = formData.get('ruc');
        const rucInput = form.querySelector('[name="ruc"]');
        if (!Utils.isValidRUC(ruc)) {
            Utils.showError(rucInput, 'RUC debe tener 11 dígitos y comenzar con 10 o 20');
            isValid = false;
        } else {
            Utils.clearError(rucInput);
        }

        // Validar email
        const correo = formData.get('correo');
        const correoInput = form.querySelector('[name="correo"]');
        if (!Utils.isValidEmail(correo)) {
            Utils.showError(correoInput, 'Ingrese un correo válido');
            isValid = false;
        } else {
            Utils.clearError(correoInput);
        }

        // Validar contraseña
        const contrasena = formData.get('contrasena');
        const contrasenaInput = form.querySelector('[name="contrasena"]');
        if (contrasena.length < 6) {
            Utils.showError(contrasenaInput, 'La contraseña debe tener al menos 6 caracteres');
            isValid = false;
        } else {
            Utils.clearError(contrasenaInput);
        }

        return isValid;
    }
};

// Manejo de archivos
const FileHandler = {
    // Validar archivo de foto
    validatePhoto: function(file) {
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            return 'Solo se permiten archivos JPG, JPEG o PNG';
        }

        if (file.size > maxSize) {
            return 'El archivo no debe superar 5MB';
        }

        return null;
    },

    // Validar archivo PDF
    validatePDF: function(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB

        if (file.type !== 'application/pdf') {
            return 'Solo se permiten archivos PDF';
        }

        if (file.size > maxSize) {
            return 'El archivo no debe superar 10MB';
        }

        return null;
    },

    // Validar archivo de video
    validateVideo: function(file) {
        const allowedTypes = ['video/mp4', 'video/mpeg', 'video/quicktime'];
        const maxSize = 50 * 1024 * 1024; // 50MB

        if (!allowedTypes.includes(file.type)) {
            return 'Solo se permiten archivos MP4, MPEG o MOV';
        }

        if (file.size > maxSize) {
            return 'El archivo no debe superar 50MB';
        }

        return null;
    }
};

// Validaciones específicas para registro con verificación de duplicados
const RegistrationValidator = {
    // Verificar email en tiempo real
    checkEmailExists: function(email, userType) {
        return fetch('check_duplicates.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&type=${userType}`
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Error checking email:', error);
            return { exists: false };
        });
    },

    // Verificar DNI en tiempo real
    checkDniExists: function(dni) {
        return fetch('check_duplicates.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `dni=${encodeURIComponent(dni)}&type=student`
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Error checking DNI:', error);
            return { exists: false };
        });
    },

    // Verificar RUC en tiempo real
    checkRucExists: function(ruc) {
        return fetch('check_duplicates.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `ruc=${encodeURIComponent(ruc)}&type=company`
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Error checking RUC:', error);
            return { exists: false };
        });
    },

    // Validar formulario mejorado
    validateRegistrationForm: function(form) {
        const userType = form.querySelector('[name="user_type"]').value;
        let isValid = true;
        const promises = [];

        // Validar email
        const emailInput = form.querySelector('[name="correo"]');
        if (emailInput && emailInput.value) {
            const emailPromise = this.checkEmailExists(emailInput.value, userType)
                .then(result => {
                    if (result.exists) {
                        Utils.showError(emailInput, 'Este correo ya está registrado');
                        return false;
                    } else {
                        Utils.clearError(emailInput);
                        return true;
                    }
                });
            promises.push(emailPromise);
        }

        // Validar DNI (solo para estudiantes)
        if (userType === 'student') {
            const dniInput = form.querySelector('[name="dni"]');
            if (dniInput && dniInput.value) {
                const dniPromise = this.checkDniExists(dniInput.value)
                    .then(result => {
                        if (result.exists) {
                            Utils.showError(dniInput, 'Este DNI ya está registrado');
                            return false;
                        } else {
                            Utils.clearError(dniInput);
                            return true;
                        }
                    });
                promises.push(dniPromise);
            }
        }

        // Validar RUC (solo para empresas)
        if (userType === 'company') {
            const rucInput = form.querySelector('[name="ruc"]');
            if (rucInput && rucInput.value) {
                const rucPromise = this.checkRucExists(rucInput.value)
                    .then(result => {
                        if (result.exists) {
                            Utils.showError(rucInput, 'Este RUC ya está registrado');
                            return false;
                        } else {
                            Utils.clearError(rucInput);
                            return true;
                        }
                    });
                promises.push(rucPromise);
            }
        }

        return Promise.all(promises).then(results => {
            return results.every(result => result === true);
        });
    },

    // Inicializar validaciones en tiempo real
    initRealTimeValidation: function() {
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (!form) return;

            const userType = form.querySelector('[name="user_type"]');
            if (!userType) return;

            const currentType = userType.value;

            // Validación de email en tiempo real
            const emailInput = form.querySelector('[name="correo"]');
            if (emailInput) {
                let emailTimeout;
                emailInput.addEventListener('blur', function() {
                    clearTimeout(emailTimeout);
                    emailTimeout = setTimeout(() => {
                        if (this.value && Utils.isValidEmail(this.value)) {
                            RegistrationValidator.checkEmailExists(this.value, currentType)
                                .then(result => {
                                    if (result.exists) {
                                        Utils.showError(this, 'Este correo ya está registrado');
                                    } else {
                                        Utils.clearError(this);
                                    }
                                });
                        }
                    }, 500);
                });
            }

            // Validación de DNI en tiempo real (solo estudiantes)
            if (currentType === 'student') {
                const dniInput = form.querySelector('[name="dni"]');
                if (dniInput) {
                    let dniTimeout;
                    dniInput.addEventListener('blur', function() {
                        clearTimeout(dniTimeout);
                        dniTimeout = setTimeout(() => {
                            if (this.value && Utils.isValidDNI(this.value)) {
                                RegistrationValidator.checkDniExists(this.value)
                                    .then(result => {
                                        if (result.exists) {
                                            Utils.showError(this, 'Este DNI ya está registrado');
                                        } else {
                                            Utils.clearError(this);
                                        }
                                    });
                            }
                        }, 500);
                    });
                }
            }

            // Validación de RUC en tiempo real (solo empresas)
            if (currentType === 'company') {
                const rucInput = form.querySelector('[name="ruc"]');
                if (rucInput) {
                    let rucTimeout;
                    rucInput.addEventListener('blur', function() {
                        clearTimeout(rucTimeout);
                        rucTimeout = setTimeout(() => {
                            if (this.value && Utils.isValidRUC(this.value)) {
                                RegistrationValidator.checkRucExists(this.value)
                                    .then(result => {
                                        if (result.exists) {
                                            Utils.showError(this, 'Este RUC ya está registrado');
                                        } else {
                                            Utils.clearError(this);
                                        }
                                    });
                            }
                        }, 500);
                    });
                }
            }
        });
    }
};

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    
    // Manejar formularios de registro
    const studentForm = document.querySelector('#studentRegistrationForm');
    if (studentForm) {
        studentForm.addEventListener('submit', function(e) {
            if (!FormValidator.validateStudentForm(this)) {
                e.preventDefault();
            }
        });
    }

    const companyForm = document.querySelector('#companyRegistrationForm');
    if (companyForm) {
        companyForm.addEventListener('submit', function(e) {
            if (!FormValidator.validateCompanyForm(this)) {
                e.preventDefault();
            }
        });
    }

    // Manejar archivos de upload
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                let error = null;
                
                if (this.name === 'foto_perfil' || this.name === 'logo') {
                    error = FileHandler.validatePhoto(file);
                } else if (this.name === 'cv_archivo') {
                    error = FileHandler.validatePDF(file);
                } else if (this.name === 'video_presentacion') {
                    error = FileHandler.validateVideo(file);
                }

                if (error) {
                    Utils.showError(this, error);
                    this.value = '';
                } else {
                    Utils.clearError(this);
                    // Mostrar nombre del archivo seleccionado
                    const label = this.parentNode.querySelector('.file-upload-label');
                    if (label) {
                        label.textContent = `Archivo seleccionado: ${file.name}`;
                        label.classList.add('file-selected');
                    }
                }
            }
        });
    });

    // Auto-calcular edad
    const birthYearInputs = document.querySelectorAll('[name="anio_nacimiento"]');
    birthYearInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const year = parseInt(this.value);
            if (year && year > 1900) {
                const age = Utils.calculateAge(year);
                const ageDisplay = document.querySelector('#ageDisplay');
                if (ageDisplay) {
                    ageDisplay.textContent = `Edad: ${age} años`;
                    if (age < 16) {
                        ageDisplay.style.color = '#dc3545';
                    } else {
                        ageDisplay.style.color = '#28a745';
                    }
                }
            }
        });
    });

    // Confirmar eliminaciones
    const deleteButtons = document.querySelectorAll('.btn-danger[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Está seguro de que desea eliminar este elemento?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-ocultar alertas
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 300);
        }, 5000);
    });
});

// Funciones específicas para el dashboard del administrador
const AdminDashboard = {
    // Cargar estadísticas
    loadStats: function() {
        // Esta función se puede extender para hacer llamadas AJAX
        // por ahora las estadísticas se cargan desde el servidor
    },

    // Confirmar eliminación de usuario
    confirmDelete: function(type, id, name) {
        const message = `¿Está seguro de que desea eliminar ${type === 'student' ? 'al estudiante' : 'a la empresa'} "${name}"?`;
        return confirm(message);
    },

    // Generar reportes
    generateReports: function() {
        console.log('🚀 Iniciando generación de reportes...');
        
        // Crear alerta de cargando
        const loadingAlert = document.createElement('div');
        loadingAlert.className = 'alert alert-info';
        loadingAlert.innerHTML = '⏳ Generando reportes... por favor espere.';
        loadingAlert.id = 'loading-alert';
        
        const container = document.querySelector('.container');
        if (container) {
            // Remover alertas anteriores
            const oldAlerts = container.querySelectorAll('.alert-success, .alert-error, .alert-info');
            oldAlerts.forEach(alert => alert.remove());
            
            container.insertBefore(loadingAlert, container.firstChild);
        }
        
        // Configurar fetch con timeout y mejores logs
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 segundos timeout
        
        console.log('📡 Enviando petición AJAX...');
        
        // Hacer llamada AJAX para generar reportes reales
        fetch('../reports/generate_reports_fixed.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=generate_reports',
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);
            console.log('📨 Respuesta recibida:', {
                status: response.status,
                statusText: response.statusText,
                headers: response.headers
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('📝 Texto de respuesta completo:', text);
            
            // Intentar parsear como JSON
            try {
                const data = JSON.parse(text);
                console.log('✅ JSON parseado exitosamente:', data);
                
                // Remover alerta de carga
                const loadingEl = document.getElementById('loading-alert');
                if (loadingEl) loadingEl.remove();
                
                if (data.success) {
                    console.log('🎉 Reporte generado exitosamente!');
                    
                    let reportHtml = `<div class="alert alert-success">
                        <h4>✅ Reportes Generados Exitosamente!</h4>
                        <p><strong>📊 Reporte completo generado:</strong></p>
                        <ul>
                            <li>📚 Lista completa de estudiantes registrados</li>
                            <li>🏢 Lista completa de empresas registradas</li>
                            <li>📈 Estadísticas generales del sistema</li>
                            <li>📋 Datos de postulaciones y ofertas</li>
                        </ul>`;
                    
                    // Agregar información de debug si está disponible
                    if (data.debug) {
                        reportHtml += `<p><small><strong>Debug info:</strong> ${data.debug.estudiantes} estudiantes, ${data.debug.empresas} empresas, ${data.debug.archivo_size} bytes</small></p>`;
                    }
                    
                    reportHtml += `<div style="margin-top: 15px;">
                            <a href="../${data.ruta}" target="_blank" class="btn" style="background-color: #28a745; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block;">
                                📄 Ver/Descargar Reporte PDF
                            </a>
                            <small style="display: block; margin-top: 10px; color: #6c757d;">
                                💡 Tip: Usa Ctrl+P en el reporte para guardarlo como PDF o imprimirlo
                            </small>
                        </div>
                    </div>`;
                    
                    // Mostrar resultado
                    if (container) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = reportHtml;
                        container.insertBefore(tempDiv.firstChild, container.firstChild);
                    }
                } else {
                    console.error('❌ Error en la respuesta:', data.message);
                    Utils.showAlert('Error al generar reportes: ' + data.message, 'error');
                }
            } catch (parseError) {
                console.error('❌ Error al parsear JSON:', parseError);
                console.error('📄 Respuesta que causó el error:', text);
                
                // Remover alerta de carga
                const loadingEl = document.getElementById('loading-alert');
                if (loadingEl) loadingEl.remove();
                
                Utils.showAlert('Error al procesar la respuesta del servidor. Ver consola para detalles.', 'error');
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.error('💥 Error en la petición:', error);
            
            // Remover alerta de carga
            const loadingEl = document.getElementById('loading-alert');
            if (loadingEl) loadingEl.remove();
            
            if (error.name === 'AbortError') {
                Utils.showAlert('La petición tardó demasiado tiempo (timeout)', 'error');
            } else {
                Utils.showAlert('Error al conectar con el servidor: ' + error.message, 'error');
            }
        });
    }
};

// Funciones para el dashboard de empresa
const CompanyDashboard = {
    // Filtrar postulaciones por estado
    filterApplications: function(status) {
        const cards = document.querySelectorAll('.applications-table .card');
        const buttons = document.querySelectorAll('[onclick*="filterApplications"]');
        
        // Actualizar estilos de botones
        buttons.forEach(btn => {
            btn.classList.remove('btn');
            btn.classList.add('btn-secondary');
        });
        
        // Marcar botón activo
        const activeButton = document.querySelector(`[onclick*="'${status}'"]`);
        if (activeButton) {
            activeButton.classList.remove('btn-secondary');
            activeButton.classList.add('btn');
        }
        
        // Filtrar tarjetas
        let visibleCount = 0;
        cards.forEach(card => {
            const statusElement = card.querySelector('.status-cell');
            if (status === 'all' || statusElement.textContent.trim() === status) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Actualizar contador si existe
        const countElement = document.querySelector('.mb-3 p strong');
        if (countElement) {
            countElement.textContent = visibleCount;
        }
    }
};

// Funciones para el dashboard de estudiante
const StudentDashboard = {
    // Filtrar ofertas
    filterOffers: function(modality) {
        const cards = document.querySelectorAll('.offer-card');
        const buttons = document.querySelectorAll('[onclick*="filterOffers"]');
        
        // Actualizar estilos de botones
        buttons.forEach(btn => {
            btn.classList.remove('btn');
            btn.classList.add('btn-secondary');
        });
        
        // Marcar botón activo
        const activeButton = document.querySelector(`[onclick*="'${modality}'"]`);
        if (activeButton) {
            activeButton.classList.remove('btn-secondary');
            activeButton.classList.add('btn');
        }
        
        // Filtrar tarjetas
        let visibleCount = 0;
        cards.forEach(card => {
            const modalityElement = card.querySelector('.offer-modality');
            if (modality === 'all' || modalityElement.textContent.trim() === modality) {
                card.parentElement.style.display = '';
                visibleCount++;
            } else {
                card.parentElement.style.display = 'none';
            }
        });
        
        // Actualizar contador
        const countElement = document.querySelector('.card-header p');
        if (countElement) {
            countElement.textContent = `Total de ofertas: ${visibleCount}`;
        }
    }
};

// Funciones para gestión de usuarios en admin
const AdminUserManagement = {
    // Confirmar cambio de estado de usuario
    confirmToggleStatus: function(form, userName, isActive) {
        const action = isActive ? 'desactivar' : 'reactivar';
        const message = `¿Está seguro de que desea ${action} a ${userName}?`;
        
        if (confirm(message)) {
            form.submit();
        }
    },

    // Inicializar confirmaciones automáticas
    initConfirmations: function() {
        document.addEventListener('DOMContentLoaded', function() {
            // Buscar todos los botones con data-confirm
            const confirmButtons = document.querySelectorAll('[data-confirm]');
            
            confirmButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const confirmMessage = this.getAttribute('data-confirm');
                    if (confirm(confirmMessage)) {
                        // Si es un botón de formulario, enviar el formulario
                        const form = this.closest('form');
                        if (form) {
                            form.submit();
                        }
                    }
                });
            });
        });
    }
};

// Inicializar confirmaciones
AdminUserManagement.initConfirmations();
