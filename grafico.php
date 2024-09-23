<?php
// Verifique se a inclusão foi feita corretamente
if (!isset($totalUsuarios, $totalVigilantes, $totalPetsCadastrados, $totalPetsAdotados, $totalPetsDisponiveis))
{
    die("Dados insuficientes para gerar gráficos.");
}
?>

<section class="graficos">
    <canvas id="graficoEstatisticas" width="400" height="200"></canvas>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoEstatisticas').getContext('2d');
    const graficoEstatisticas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Adotantes', 'Vigilantes', 'Pets Resgatados', 'Pets Adotados', 'Pets Disponíveis'],
            datasets: [{
                label: 'Quantidade',
                data: [<?= $totalUsuarios; ?>, <?= $totalVigilantes; ?>, <?= $totalPetsCadastrados; ?>,
                    <?= $totalPetsAdotados; ?>, <?= $totalPetsDisponiveis; ?>
                ],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
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