<?php
// utilidades.php

if (!function_exists('exibirMensagem'))
{
    function exibirMensagem($tipo, $mensagem, $instrucoes)
    {
        $tipoClasse = '';
        switch ($tipo)
        {
            case 'sucesso':
                $tipoClasse = 'toast-sucesso';
                break;
            case 'erro':
                $tipoClasse = 'toast-erro';
                break;
            default:
                $tipoClasse = 'toast-info';
        }

        echo '
        <div id="toast" class="toast ' . $tipoClasse . '">
            <div class="toast-conteudo">
                <p>' . htmlspecialchars($mensagem) . '</p>
                <p>' . htmlspecialchars($instrucoes) . '</p>
            </div>
            <button class="toast-fechar" onclick="fecharToast()">X</button>
        </div>

        <script>
            function mostrarToast() {
                var toast = document.getElementById("toast");
                if (toast) {
                    toast.style.opacity = 1;
                    toast.style.visibility = "visible";
                    setTimeout(function() {
                        toast.style.opacity = 0;
                        toast.style.visibility = "hidden";
                    }, 7000);
                }
            }

            function fecharToast() {
                var toast = document.getElementById("toast");
                if (toast) {
                    toast.style.opacity = 0;
                    toast.style.visibility = "hidden";
                }
            }

            // Chame essa função quando quiser exibir o toast
            window.onload = function() {
                mostrarToast();
            };
        </script>

        <style>
            /* CSS para o Toast */
            .toast {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: #333;
                color: #fff;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.5s, visibility 0.5s;
                z-index: 1000;
                text-align: center;
            }

            .toast-sucesso {
                background-color: #4CAF50;
            }

            .toast-erro {
                background-color: #F44336;
            }

            .toast-info {
                background-color: #2196F3;
            }

            .toast-conteudo {
                margin-bottom: 10px;
            }

            .toast-fechar {
                background: none;
                border: none;
                color: #fff;
                font-size: 18px;
                cursor: pointer;
            }
        </style>
        ';
    }
}
