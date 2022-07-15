<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists("archivo.txt")) {
    //si el archivo existe carga los datos a la variable
    $strJson = file_get_contents("archivo.txt"); 
    $aClientes = json_decode($strJson, true);
} else {
    //si el archivo no existe es porque no hay datos o clientes nuevos
    $aClientes = array();
}

//editor de clientes
if(isset($_GET["id"])){
    $id = $_GET["id"];
} else {
    $id="";
}

//eliminar clientes
if(isset($_GET["do"]) && $_GET["do"] == "eliminar"){
    unset($aClientes[$id]);

    $strJson = json_encode($aClientes);

    file_put_contents("archivo.txt", $strJson);

    header("Location: index.php");
}

if ($_POST) {
    $dni = $_POST["txtDni"];
    $nombre = $_POST["txtNombre"];
    $telefono = $_POST["txtTelefono"];
    $correo = $_POST["txtCorreo"];
    $nombreImagen ="";

    //Insertar imagen y almacenamiento en carpeta
    if ($_FILES["archivo"]["error"] === UPLOAD_ERR_OK) {
        $nombreAzar = date("Ymdhmsi") . rand(1000, 2000);
        $archivo_tmp = $_FILES["archivo"]["tmp_name"];
        $extension = pathinfo($_FILES["archivo"]["name"], PATHINFO_EXTENSION);
        if($extension == "jpg" || $extension == "png" || $extension == "jpeg"){
            $nombreImagen = "$nombreAzar.$extension";
            move_uploaded_file($archivo_tmp, "imagenes/$nombreImagen");
        }
    }

    if($id >= 0){
            
        if ($_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) {
            $nombreImagen = $aClientes[$id]["imagen"];
         } else { //elimina la imagen anterior , si esta fue editada o cambiada
            if(file_exists("imagenes/". $aClientes[$id]["imagen"])){ 
                unlink("imagenes/". $aClientes[$id]["imagen"]);
            }
         }

        //reescribir la informacion
        $aClientes[$id] = array("dni" => $dni,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "correo" => $correo,
            "imagen" => $nombreImagen,
        );
    } else{
        //insercion de un cliente
        $aClientes[] = array("dni" => $dni,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "correo" => $correo,
            "imagen" => $nombreImagen,
        );

    }

    //Convierto el array en json
    $strJson = json_encode($aClientes);

    //Almaceno en txt el json
    file_put_contents("archivo.txt", $strJson);
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABM Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/40e341f8f7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body id="fondo">
    <main class="container">
        <div class="row">
            <div class="col-12 py-5 text-center">
                <h1>Registro de Clientes</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <form action="" method="POST" enctype="multipart/form-data">
                <div>
                        <label for=""><b>Nombre:</b></label>
                        <input type="text" name="txtNombre" id="txtNombre" class="form-control" required value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["nombre"] : ""; ?>">
                    </div>
                    <div>
                        <label for=""><b>DNI:</b></label>
                        <input type="text" name="txtDni" id="txtDni" class="form-control" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["dni"] : ""; ?>">
                    </div>
                    <div>
                        <label for=""><b>Teléfono:</b></label>
                        <input type="text" name="txtTelefono" id="txtTelefono" class="form-control" value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["telefono"] : "";?>">
                    </div>
                    <div>
                        <label for=""><b>Correo:</b></label>
                        <input type="text" name="txtCorreo" id="txtCorreo" class="form-control" required value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["correo"] : "";?>">
                    </div>
                    <div class="py-2 pt-3 file">
                        <label for="archivo">Adjuntar imagen</label>
                        <input type="file" name="archivo" id="archivo" accept=".jpg, .jpeg, .png">
                        <small class="d-block" style="text-align: center;">Archivos admitidos: .jpg, .jpeg, .png</small>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success">Guardar</button>
                        <a href="index.php" class="btn btn-primary my-2">Nuevo</a>
                    </div>
                </form>
            </div>
            <div class="col-8">
                <table class="table table-hover border">
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Telefono</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach($aClientes as $pos => $cliente): ?>
                        <tr>
                            <td><img src="imagenes/<?php echo $cliente["imagen"]; ?>" style="width: 80px;height: 80px;" class="img-thumbnail"></td>
                            <td><?php echo $cliente["nombre"]; ?></td>
                            <td><?php echo $cliente["dni"]; ?></td>
                            <td><?php echo $cliente["correo"]; ?></td>
                            <td><?php echo $cliente["telefono"]; ?></td>

                            
                            <td class="acciones ps-3">
                                <a href="?id=<?php echo $pos; ?>" title="Editar"><i class="fa-solid fa-user-gear pe-2"></i></a> 
                                <a href="?id=<?php echo $pos; ?>&do=eliminar" title="Eliminar"><i class="fa-solid fa-trash-can" style="color: red;"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </main>

</body>

</html>