<?php

// Consulta para obter dados
$sql = "SELECT 
            (SELECT COUNT(*) FROM Pet) AS totalPets, 
            (SELECT COUNT(DISTINCT fk_Usuario_cpf) FROM Adocao) AS totalAdocoes";

$resultado = $pdo->query($sql);
$dados = $resultado->fetch(PDO::FETCH_ASSOC);
?>

<div
    style="max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9;">
    <h2 style="text-align: center;">Cadastros de Pets e Adoções</h2>
    <canvas id="grafico" width="600" height="400" style="max-width: 100%; margin: 20px auto; display: block;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var ctx = document.getElementById('grafico').getContext('2d');

var totalPets = <?php echo $dados['totalPets']; ?>;
var totalAdocoes = <?php echo $dados['totalAdocoes']; ?>;

// Gráfico de barras
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Total de Pets', 'Total de Adoções'],
        datasets: [{
            label: 'Cadastros',
            data: [totalPets, totalAdocoes],
            backgroundColor: [
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>