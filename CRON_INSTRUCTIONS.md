# Instruções para Configuração de Cron Jobs (Tarefas Agendadas)

Este documento explica como configurar as tarefas agendadas (cron jobs) necessárias para o funcionamento do Scheduler de Comerciais.

## Visão Geral

O sistema de agendamento automático consiste em dois scripts principais que precisam ser executados diariamente:

1.  `gerar_agendamentos.php`: Este script é o "cérebro". Ele verifica os contratos ativos, a adimplência dos clientes e gera a grade de programação de comerciais para o dia seguinte, salvando-a no banco de dados.
2.  `gerar_playlist_radioboss.php`: Este script lê os agendamentos do banco de dados e gera o arquivo de playlist (`.m3u`) que o RadioBOSS irá consumir.

## Ordem de Execução

É **essencial** que os scripts sejam executados na seguinte ordem:
1.  Primeiro, `gerar_agendamentos.php`.
2.  Depois, `gerar_playlist_radioboss.php`.

Recomenda-se um intervalo de alguns minutos entre eles para garantir que o primeiro termine completamente antes que o segundo comece.

## Configuração no Servidor (Linux)

Você precisará editar a tabela de cron jobs do seu usuário no servidor. Abra o terminal e execute:

```bash
crontab -e
```

Adicione as duas linhas a seguir no final do arquivo. Este exemplo configura os scripts para serem executados uma vez por dia, de madrugada.

```crontab
# Gera a grade de programação de comerciais para o dia seguinte
# Executa todos os dias às 02:00 da manhã
0 2 * * * /usr/bin/php /caminho/completo/para/seu/projeto/controle/cron/gerar_agendamentos.php

# Gera a playlist para o RadioBOSS consumir
# Executa todos os dias às 02:05 da manhã
5 2 * * * /usr/bin/php /caminho/completo/para/seu/projeto/controle/cron/gerar_playlist_radioboss.php
```

### Notas Importantes:

*   **Caminho do PHP:** O caminho `/usr/bin/php` pode variar dependendo do seu servidor. Use o comando `which php` no terminal para descobrir o caminho correto.
*   **Caminho do Projeto:** Substitua `/caminho/completo/para/seu/projeto/` pelo caminho absoluto da pasta onde o sistema da rádio está instalado no seu servidor.
*   **Permissões:** Certifique-se de que os scripts no diretório `controle/cron/` tenham permissão de execução. Você pode garantir isso com o comando:
    ```bash
    chmod +x /caminho/completo/para/seu/projeto/controle/cron/*.php
    ```

Após salvar o arquivo `crontab`, o sistema operacional se encarregará de executar os scripts nos horários definidos.
