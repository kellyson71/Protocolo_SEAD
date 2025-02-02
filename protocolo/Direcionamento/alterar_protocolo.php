<!DOCTYPE html>
<html>

<head>
    // ...existing code...
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    // ...existing code...
    <div class="container mt-4">
        <div class="d-flex justify-content-end gap-3 mb-4">
            <button type="button" class="btn btn-primary" onclick="alterarProtocolo()">
                <i class="bi bi-save"></i> Salvar Alterações
            </button>
            <button type="button" class="btn btn-danger" onclick="cancelarAlteracao()">
                <i class="bi bi-x-circle"></i> Cancelar
            </button>
        </div>

        <div class="row">
            <div class="col">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary" onclick="anterior()">
                        <i class="bi bi-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="proximo()">
                        Próximo <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    // ...existing code...
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>