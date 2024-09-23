<header
    style="display:flex; flex-direction: column; justify-content:center; align-items: center; text-align: center; background-color: #3498db; padding: 10px;">
    <!-- Navegação de links com efeito de transição suave -->
    <div style="display:flex; flex-direction: row; text-align: center; margin-bottom: 10px;">
        <a href="javascript:history.back()" class="btn-header">Voltar</a>
        <a href="home.php" class="btn-header">Início</a>
        <a href="logout.php" class="btn-header">Sair</a>
    </div>

    <!-- Logo e título do sistema -->
    <div
        style="display:flex; flex-direction: row; justify-content:center; align-items: center; text-align: center; background-color: #3498db; padding: 10px;">
        <img class="logoprefeitura" src="imagens/sistema/logo/logo-prefeitura1.jpg"
            alt="Logo Prefeitura Municipal de Imaculada-PB" style="width: 300px; border-radius: 4%;">

        <!-- Títulos do sistema -->
        <div style="display:flex; flex-direction:column; margin-left: 50px;">
            <h1 style="font-size: 2em; color: white;">Adote um Pet</h1>
            <h1 style="font-size: 30px; color: white;">Vigilância Sanitária</h1>
            <h1 style="font-size: 20px; color: rgb(255, 174, 23);">Cuidados e adoção de pets</h1>
        </div>
    </div>
</header>

<style>
/* Estilo para os botões de navegação */
.btn-header {
    text-decoration: none;
    background-color: #007bff;
    color: white;
    padding: 5px 10px;
    border-radius: 4%;
    margin: 0 5px;
    transition: background-color 0.4s ease, transform 0.4s ease;
    /* Adiciona a transição suave */
}

/* Efeito ao passar o mouse nos botões */
.btn-header:hover {
    background-color: #0056b3;
    transform: translateY(-3px);
    /* Adiciona um efeito deslizante para cima ao passar o mouse */
}

/* Estilo para o botão de submit e cadastrar-se */
button[type="submit"],
.btn_cadastre_se {
    background-color: #3498db;
    color: white;
    padding: 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
    width: 100%;
    transition: background-color 0.4s ease, transform 0.4s ease;
}

/* Efeito hover nos botões de submit e cadastrar-se */
button[type="submit"]:hover,
.btn_cadastre_se:hover {
    background-color: #2980b9;
    transform: translateY(-3px);
    /* Efeito deslizante para cima */
}
</style>