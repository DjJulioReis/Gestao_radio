# Explicação do Novo Sistema de Agendamento de Comerciais

Olá! Como estou enfrentando um problema técnico para enviar a explicação por mensagem, preparei este documento para você.

### O que foi feito? (Um Resumo das Novidades)

Eu criei um sistema completo e automático para gerenciar e agendar os comerciais da rádio. O grande objetivo é **automatizar o trabalho manual**, garantir que a programação vá para o ar corretamente e, principalmente, **proteger a rádio, tirando do ar os comerciais de clientes inadimplentes automaticamente**.

O sistema agora faz o seguinte:

1.  **Gerenciamento Central de Comerciais:**
    *   Há uma nova área no sistema onde você pode fazer o upload dos arquivos de áudio (MP3, WAV, etc.) e associar cada comercial a um cliente específico.

2.  **Verificação Automática de Pagamentos:**
    *   Todos os dias, de madrugada, o sistema verifica sozinho quais clientes estão com faturas vencidas e não pagas.

3.  **Agendamento Inteligente e Automático:**
    *   Com base nos contratos ativos, o sistema cria a programação de comerciais para o dia seguinte, mas **apenas para os clientes que estão com os pagamentos em dia**.
    *   Se um cliente estiver inadimplente, seus comerciais são **automaticamente bloqueados** da programação.

4.  **Distribuição Justa na Grade:**
    *   O sistema distribui as inserções dos comerciais de forma proporcional ao longo do horário de veiculação (das 6h da manhã à meia-noite), evitando que os comerciais de um mesmo cliente toquem todos juntos.

5.  **Integração com o RadioBOSS:**
    *   Ao final do processo, o sistema gera um **arquivo de playlist (`.m3u`)**, que é o formato que o RadioBOSS entende. A ideia é que o RadioBOSS seja configurado para "ler" este arquivo todo dia e montar a programação de comerciais automaticamente.

6.  **Relatórios para Conferência:**
    *   Criei um novo relatório onde você pode ver exatamente quais comerciais foram agendados para cada dia, o horário e o cliente. Isso facilita muito a conferência e a prestação de contas ao cliente.

### Como vamos usar no dia a dia?

O uso é bem simples depois de uma configuração inicial.

**Parte 1: Configuração Inicial (Você só precisa fazer isso uma vez)**

1.  **Configurar os Scripts Automáticos (Cron Jobs):** No servidor onde o sistema está hospedado, é preciso agendar a execução dos dois scripts que criei. As instruções detalhadas estão no arquivo `CRON_INSTRUCTIONS.md` (na pasta principal do projeto). Isso fará com que o agendamento inteligente rode todo dia, de madrugada, sem que você precise fazer nada.
2.  **Configurar o RadioBOSS:** No seu RadioBOSS, você precisará criar um "evento" agendado para, também de madrugada (depois que os scripts rodarem), importar a playlist `.m3u` que o sistema gerou. O caminho do arquivo será algo como `[pasta do sistema]/controle/uploads/playlists_radioboss/playlist_20240115.m3u` (o nome muda a cada dia).

**Parte 2: Uso Diário no Sistema**

Uma vez que a parte automática esteja configurada, seu trabalho será apenas gerenciar os comerciais e contratos:

1.  **Para Adicionar um Comercial Novo:**
    *   No Dashboard, clique no novo card **"Gestão de Comerciais"**.
    *   Clique em **"Adicionar Novo Comercial"**.
    *   Na tela, selecione o **Cliente**, escolha o **arquivo de áudio** do seu computador, informe a **duração** em segundos e clique em "Salvar".
    *   Pronto! O comercial já está no sistema, e o agendador automático já vai considerá-lo para as próximas programações.

2.  **Para Conferir a Programação:**
    *   Quer saber o que vai tocar amanhã? Ou o que tocou ontem?
    *   Vá ao Dashboard, na seção de "Relatórios", e clique em **"Agendamentos de Comerciais"**.
    *   Use o filtro para escolher a data e, se quiser, um cliente específico. A lista completa da programação aparecerá na tela.

O sistema foi desenhado para que, após a configuração inicial, ele funcione praticamente sozinho. Sua principal tarefa será manter os contratos dos clientes atualizados e subir os novos arquivos de comerciais quando eles chegarem.

Espero que esta explicação ajude! Se tiver qualquer dúvida, pode perguntar.
