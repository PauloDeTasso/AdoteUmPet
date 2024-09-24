<header
    style="display:flex; flex-direction: column; justify-content:center; align-items: center; text-align: center; background-color: #3498db; padding: 10px;">
    <!-- Navegação de links com efeito de transição suave -->
    <div style="display:flex; flex-direction: row; text-align: center; margin-bottom: 10px;">
        <a href="javascript:history.back()" class="btn-header">
            <img src="imagens/sistema/icones/voltar.webp" alt="Ícone Voltar" class="icon-header">
            Voltar
        </a>
        <a href="home.php" class="btn-header">
            <img src="imagens/sistema/icones/home.webp" alt="Ícone Menu" class="icon-header">
            Menu
        </a>
        <a href="logout.php" class="btn-header">
            <img src="imagens/sistema/icones/sair.png" alt="Ícone Sair" class="icon-header">
            Sair
        </a>
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
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    background-color: #007bff;
    color: white;
    padding: 10px;
    border-radius: 10px;
    margin: 0 5px;
    transition: background-color 0.4s ease, transform 0.4s ease;
    width: 80px;
    text-align: center;
}

/* Estilo para os ícones dos botões */
.icon-header {
    width: 24px;
    height: 24px;
    margin-bottom: 5px;
}

/* Efeito ao passar o mouse nos botões */
.btn-header:hover {
    background-color: #0056b3;
    transform: translateY(-3px);
}

@media (max-width: 768px) {
    .logoprefeitura {
        width: 70%;
    }
}

/* Responsividade para telas menores */
@media (max-width: 600px) {

    .logoprefeitura {
        width: 50%;
    }

    .btn-header {
        width: 100px;
    }

    .icon-header {
        width: 20px;
        height: 20px;
    }
}
</style>