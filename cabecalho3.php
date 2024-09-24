<header class="header-pet">
    <div class="header-back">
        <a href="javascript:history.back()">
            <img src="imagens/sistema/icones/voltar.webp" alt="Voltar" class="icon">
            Voltar
        </a>
    </div>

    <div class="header-content">
        <img class="logoprefeitura" src="imagens/sistema/logo/logo-prefeitura1.jpg"
            alt="Logo Prefeitura Municipal de Imaculada-PB">
        <div class="header-titles">
            <h1>Adote um Pet</h1>
            <h1>Vigilância Sanitária</h1>
            <h1>Cuidados e adoção de pets</h1>
        </div>
    </div>
</header>

<style>
/* Estilo para o header */
.header-pet {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    background-color: #3498db;
    padding: 10px;
    transition: background-color 0.4s ease, transform 0.4s ease;
}

/* Efeito hover no header */
.header-pet:hover {
    background-color: #2980b9;
    transform: translateY(-3px);
}

/* Estilo para o botão de voltar */
.header-back {
    text-align: center;
    margin-bottom: 10px;
}

.header-back a {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background-color: #007bff;
    color: white;
    padding: 5px 10px;
    border-radius: 20%;
    margin: 0 5px;
    transition: background-color 0.4s ease, transform 0.4s ease;
}

/* Estilo para o ícone dentro do botão */
.header-back .icon {
    width: 20px;
    height: 20px;
    margin-right: 5px;
}

/* Efeito hover no botão de voltar */
.header-back a:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

/* Estilo para o conteúdo do header */
.header-content {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    text-align: center;
    background-color: #3498db;
    padding: 10px;
}

/* Estilo para a logo */
.logoprefeitura {
    width: 300px;
    border-radius: 4%;
    transition: transform 0.4s ease;
}

/* Efeito hover na logo */
.logoprefeitura:hover {
    transform: scale(1.05);
}

/* Estilo para os títulos */
.header-titles {
    display: flex;
    flex-direction: column;
    margin-left: 50px;
    transition: transform 0.4s ease;
}

.header-titles h1 {
    font-size: 2em;
    color: white;
}

.header-titles h1:nth-child(2) {
    font-size: 30px;
    color: white;
}

.header-titles h1:nth-child(3) {
    font-size: 20px;
    color: rgb(255, 174, 23);
}

/* Efeito hover nos títulos */
.header-titles:hover {
    transform: translateY(-3px);
}

@media (max-width: 768px) {
    .logoprefeitura {
        width: 70%;
    }
}
</style>