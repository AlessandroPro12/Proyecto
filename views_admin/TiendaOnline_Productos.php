<?php
session_start();
    //include("../controllers/controller_consultas_backend.php");
    include ("../models/models_tools.php");
    $objWT = new Web_Tools();
    $_SESSION["bomberng_rol"] = 2;

?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Tienda Online | Productos</title>
    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="Support " />
    <meta name="author" content="ASDADvanced" />
    <meta name="description" content="Support ."  />
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../templates/adminlte4/css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous" media="print"  onload="this.media='all'"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../templates/adminlte4/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

<?php 
if($objWT->IsAuthorized(2, $_SESSION["bomberng_rol"]) ) { //
?>

    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <?php
        include ("../templates/includes/navbar.php");
      ?>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php
        include ("../templates/includes/sidebar.php");
      ?>
      <!--end::Sidebar-->

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Formulario</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Formulario</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->

        <!--*****************************************************-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            

            <!-- CONTROLES DE FORMULARIO Y TABLA -->
            <div class="row"><!-- fila contenedora -->

                <!-- COLUMNA DE FORMULARIO  -->
                <div class="col-md-6"><!-- columna de contenido -->
                
                    <!-- /.card-header -->
                    <div class="card">
                        <div class="card-header bg-indigo">
                            <h3 class="card-title">Datos de Contactos</h3>
                        </div>
                        <!-- Para controles de formularios siempre usar etiqueta FORM -->
                        <form role="form">
                            <div class="card-body">

                                <div class="row">

                                    <!-- Control Inputbox ejemplo -->
                                    <div class="col-md-12 mb-3">
                                        <label for="txtNombre" class="form-label">Nombres y Apellidos</label>
                                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" placeholder="Nombres y Apellidos">
                                    </div>  

                                    <!-- Control de Lista Desplegable -->
                                    <div class="col-md-12 mb-3">
                                        <div class="form-group">
                                            <label for="lstTipoc" class="form-label">Tipo de Documento (Seleccionar)</label>
                                            <select class="form-select" name="lstTipoc" id="lstTipoc">
                                                <option value="0">Seleccionar...</option>
                                                <option value="1">Cedula</option>
                                                <option value="2">Tarjeta Identidad</option>
                                                <option value="3">Cedula extranjeria</option>                    
                                                <option value="4">Otro</option>                    
                                            </select>
                                        </div> 
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="txtNombre" class="form-label">Correo</label>
                                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" placeholder="Correo electronico">
                                    </div>  

                                    <div class="col-md-12 mb-3">
                                        <label for="txtNombre" class="form-label">Celular</label>
                                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" placeholder="Número de Celular">
                                    </div>  

                                    <!-- /.card-header -->
                    <div class="card">
                        <div class="card-header bg-indigo">
                            <h3 class="card-title">Datos de Envio</h3>
                        </div>
                        <!-- Para controles de formularios siempre usar etiqueta FORM -->
                        <form role="form">
                            <div class="card-body">

                                <div class="row">

                                    <!-- Control Inputbox ejemplo -->
                                    <div class="col-md-12 mb-3">
                                        <label for="txtNombre" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" placeholder="Dirección Completa">
                                    </div>  

                                    <div class="col-md-12 mb-3">
                                        <label for="txtNombre" class="form-label">Codigo Postal</label>
                                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" placeholder="Codigo Postal">
                                    </div>  

                                    <div class="col-md-12 mb-3">
                                        <label for="txtNombre" class="form-label">Casa</label>
                                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" placeholder="Descripción de Casa">
                                    </div>  

                                    <!-- Control FileUpload ejemplo -->                
                                    <div class="col-md-12 mb-3">
                                        <label for="txtFile" class="form-label">Subir Documento de Identidad</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="txtFile" name="txtFile">
                                            <label class="custom-file-label" for="txtFile">Seleccionar</label>
                                            </div>                    
                                        </div>
                                    </div>

                                    

                                    <!-- Control RadioButton ejemplo -->
                                    <div class="col-md-12 mb-3">                    
                                        <label class="form-label">Sexo (Radio button)</label>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="rdbSexo1" name="rdbSexo">
                                            <label for="rdbSexo1" class="custom-control-label">Hombre</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="rdbSexo2" name="rdbSexo">
                                            <label for="rdbSexo2" class="custom-control-label">Mujer</label>
                                        </div>
                                    </div>   

                                    

                                </div>  <!-- /.fin row -->                           
                            </div>  <!-- /.fin card-body -->

                            <div class="card-footer">
                                <button type="submit" class="btn btn-success">Enviar</button>
                                <button type="reset" class="btn btn-default">Limpiar</button>
                            </div>

                        </form> <!-- /.fin Form -->

                    </div>

                </div><!-- Fin contenido formulario -->

                

            </div>

          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
        <!--*****************************************************-->

      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <?php
        include ("../templates/includes/footer.php");
      ?>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->


<?php 
}else{ // valida Autorizacion
  ?>
 <div class="callout callout-danger">
    <h5>Acceso Indebido!</h5>
    <p>Usted no tiene permisos para acceder a esta área. </p>
  </div> 
<?php   
}
?>



    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script  src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous" ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script  src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"  crossorigin="anonymous" ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"  crossorigin="anonymous"  ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../templates/adminlte4/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>

    

  </body>
  <!--end::Body-->
</html>
