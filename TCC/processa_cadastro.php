<?php
session_start();
require_once 'conexao.php';

/** * Validação de CPF
 * Realiza o cálculo dos dígitos verificadores para garantir a autenticidade do documento informado.
 */
function validaCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) { $d += $cpf[$c] * (($t + 1) - $c); }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['nome_usuario']);
    $email = trim($_POST['email']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    
    /** * Tratamento de Senha
     * Nota: A senha está sendo armazenada em texto puro para compatibilidade com o limite de 
     * caracteres da coluna no banco de dados atual.
     */
    $senha = $_POST['senha']; 
    
    $telefone = $_POST['telefone'];
    $data_nasc = $_POST['data_nascimento'];
    
    // VALIDAÇÃO DE CAMPOS OBRIGATÓRIOS E REGRAS DE NEGÓCIO
    if (empty($data_nasc)) {
        $_SESSION['erro'] = "A data de nascimento é obrigatória!";
        header("Location: cadastro.php"); exit();
    }

    if (!validaCPF($cpf)) {
        $_SESSION['erro'] = "CPF inválido!";
        header("Location: cadastro.php"); exit();
    }

    /** * Verificação de Duplicidade
     * Consulta o banco para assegurar que e-mail, nome de usuário ou CPF não estejam em uso.
     */
    $sql_check = "SELECT id FROM usuarios WHERE email = ? OR nome_usuario = ? OR cpf = ?";
    $stmt_check = $con->prepare($sql_check);
    $stmt_check->bind_param("sss", $email, $usuario, $cpf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $_SESSION['erro'] = "Dados já cadastrados!";
        header("Location: cadastro.php"); exit();
    }

    /** * Processamento de Imagem (Upload)
     * Renomeia o arquivo utilizando o timestamp para evitar sobreposição e move para o diretório de destino.
     */
    $caminho_foto = NULL;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $novo_nome = $usuario . "_" . time() . "." . $extensao;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $novo_nome)) {
            $caminho_foto = "uploads/" . $novo_nome;
        }
    }

    /** * Persistência de Dados
     * Utiliza Prepared Statements para inserção segura dos dados do novo usuário.
     */
    $sql = "INSERT INTO usuarios (nome, nome_usuario, email, cpf, senha, foto, telefone, data_nascimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssssss", $nome, $usuario, $email, $cpf, $senha, $caminho_foto, $telefone, $data_nasc);

    if ($stmt->execute()) {
        $_SESSION['sucesso'] = "Cadastro realizado com sucesso!";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['erro'] = "Erro ao cadastrar: " . $con->error;
        header("Location: cadastro.php");
        exit();
    }
}