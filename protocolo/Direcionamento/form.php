<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>form</title>
    <link rel="icon" href="../assets/prefeitura-logo.png">

    <style>
    @page {
        margin: 0;
        size: auto;
    }

    p {
        display: block;
        width: 37%;
        height: 35px;
        position: absolute;
        background-color: transparent;
        font-size: 14px;
        border-radius: 5px;
        transform: translate(0, -50%);
        padding: 0.5em;
        margin: 0;
        line-height: 1.6em;
    }

    #wrapper {
        position: relative;
        width: min-content;
        height: min-content;
    }

    #wrapper img {
        width: 800px;
    }

    #nome,
    #telefone,
    #UT,
    #DP {
        left: 11%;
    }

    #nprotocolo,
    #cargo,
    #vinculo,
    #matricula {
        left: 51%;
    }

    #email,
    #contexto,
    #contexto_txt {
        width: 77%;
        left: 11%;
    }

    #nome,
    #nprotocolo {
        top: 22%;
    }

    #email {
        top: 29.7%;
    }

    #telefone,
    #cargo {
        top: 37.5%;
    }

    #UT,
    #vinculo {
        top: 45.75%;
        white-space: nowrap;
    }

    #DP,
    #matricula {
        top: 54.125%;
    }

    #contexto {
        top: 66.875%;
    }

    #contexto_txt {
        height: 205px;
        top: 79.9%;
    }

    @media print {
        @page {
            margin: 0;
            padding: 0;
            size: auto;
        }

        #wrapper img {
            width: 776px;
        }
    }
    </style>
</head>

<body>
    <div id="wrapper">
        <img src="../assets/pdf.png" alt="PDF">
        <?php
      require_once '../env/config.php';

      $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }
      
        
      if (isset($_GET['id'])) {
    
        $id = $_GET['id'];
        $sql = "SELECT nome, unidadeTrabalho, id, gmail, telefone, vinculo, departamento_atual, matricula, requerimento, contexto, lotacao, data FROM protocolos WHERE id = $id";
        $result = $conn->query($sql);
    
    
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<p id="nome">' . $row["nome"] . '</p>';
            echo '<p id="nprotocolo">' . $row["id"] . '</p>';
            echo '<p id="email">' . $row["gmail"] . '</p>';
            echo '<p id="telefone">' . $row["telefone"] . '</p>';
            echo '<p id="UT">' . $row["lotacao"] . '</p>';
            echo '<p id="DP">' . $row["data"] . '</p>';
            echo '<p id="matricula">' . $row["matricula"] . '</p>';
            echo '<p id="contexto">' . $row["requerimento"] . '</p>';
            echo '<p id="contexto_txt">' . $row["contexto"] . '</p>';
            echo '<p id="cargo">' . $row["vinculo"] . '</p>';
          }
    
          $result->free();
        } else {
          echo "Nenhum resultado encontrado.";
        }
      } else {
        echo "ID não fornecido na URL.";
      }



      $conn->close();
    ?>
    </div>
    <script>
    // Função para imprimir a página
    function imprimirPagina() {
        window.print();
        // Adiciona temporizador para fechar após 4 segundos
        window.close();
    }

    // Chama a função de impressão quando a página é carregada
    window.onload = function() {
        imprimirPagina();
    };
    </script>
</body>

</html>