<header class="header-pet">
    <img class="logoprefeitura" src="imagens/sistema/logo/logo-prefeitura1.jpg"
        alt="Logo Prefeitura Municipal de Imaculada-PB">
    <div class="header-titles">
        <h1>Adote um Pet</h1>
        <h1>Vigilância Sanitária</h1>
        <h1>Cuidados e adoção de pets</h1>
    </div>
</header>

<style>
/* Estilo para o header */
.header-pet {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    text-align: center;
    background-color: #3498db;
    border-bottom: 2px solid #ccc;
    padding: 10px;
    transition: background-color 0.4s ease, transform 0.4s ease;
    /* Transição suave para o background */
}

/* Efeito hover no header */
.header-pet:hover {
    background-color: #2980b9;
    transform: translateY(-3px);
    /* Efeito deslizante para cima */
}

/* Estilo para a logo */
.logoprefeitura {
    width: 300px;
    border-radius: 4%;
    transition: transform 0.4s ease;
    /* Transição suave para a logo */
}

/* Efeito hover na logo */
.logoprefeitura:hover {
    transform: scale(1.05);
    /* Aumenta ligeiramente a logo ao passar o mouse */
}

/* Estilo para os títulos do header */
.header-titles {
    display: flex;
    flex-direction: column;
    margin-left: 50px;
    transition: transform 0.4s ease;
    /* Transição suave para os títulos */
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
    /* Desliza os títulos ao passar o mouse */
}

@media (max-width: 768px) {
    .logoprefeitura {
        width: 70%;
    }
}
</style>