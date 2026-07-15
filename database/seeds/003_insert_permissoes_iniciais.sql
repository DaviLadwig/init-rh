BEGIN;

-- =========================================================
-- CATÁLOGO INICIAL DE PERMISSÕES
-- As permissões são gerais e posteriormente serão
-- vinculadas aos perfis de cada organização.
-- =========================================================

INSERT INTO permissoes (
    nome,
    codigo,
    modulo,
    descricao
)
VALUES

-- Dashboard
(
    'Visualizar dashboard',
    'dashboard.visualizar',
    'DASHBOARD',
    'Permite visualizar o painel principal do sistema.'
),

-- Organização
(
    'Visualizar organização',
    'organizacoes.visualizar',
    'ORGANIZACOES',
    'Permite visualizar os dados da organização.'
),
(
    'Editar organização',
    'organizacoes.editar',
    'ORGANIZACOES',
    'Permite alterar os dados e configurações da organização.'
),

-- Usuários
(
    'Visualizar usuários',
    'usuarios.visualizar',
    'USUARIOS',
    'Permite consultar os usuários cadastrados.'
),
(
    'Criar usuários',
    'usuarios.criar',
    'USUARIOS',
    'Permite cadastrar novos usuários.'
),
(
    'Editar usuários',
    'usuarios.editar',
    'USUARIOS',
    'Permite alterar os dados dos usuários.'
),
(
    'Ativar ou desativar usuários',
    'usuarios.alterar-status',
    'USUARIOS',
    'Permite ativar ou desativar usuários.'
),
(
    'Redefinir senha de usuários',
    'usuarios.redefinir-senha',
    'USUARIOS',
    'Permite gerar ou definir uma nova senha para outro usuário.'
),

-- Perfis e permissões
(
    'Visualizar perfis',
    'perfis.visualizar',
    'PERFIS',
    'Permite consultar os perfis de acesso.'
),
(
    'Gerenciar perfis',
    'perfis.gerenciar',
    'PERFIS',
    'Permite criar, editar e configurar perfis de acesso.'
),

-- Estrutura organizacional
(
    'Visualizar estrutura organizacional',
    'estrutura.visualizar',
    'ESTRUTURA',
    'Permite consultar unidades, departamentos, setores, cargos e funções.'
),
(
    'Gerenciar estrutura organizacional',
    'estrutura.gerenciar',
    'ESTRUTURA',
    'Permite cadastrar e editar unidades, departamentos, setores, cargos e funções.'
),

-- Colaboradores
(
    'Visualizar colaboradores',
    'colaboradores.visualizar',
    'COLABORADORES',
    'Permite consultar os colaboradores cadastrados.'
),
(
    'Criar colaboradores',
    'colaboradores.criar',
    'COLABORADORES',
    'Permite cadastrar novos colaboradores.'
),
(
    'Editar colaboradores',
    'colaboradores.editar',
    'COLABORADORES',
    'Permite alterar informações dos colaboradores.'
),
(
    'Ativar ou desativar colaboradores',
    'colaboradores.alterar-status',
    'COLABORADORES',
    'Permite ativar ou desativar colaboradores.'
),

-- Documentos
(
    'Visualizar documentos',
    'documentos.visualizar',
    'DOCUMENTOS',
    'Permite visualizar documentos autorizados.'
),
(
    'Gerenciar documentos',
    'documentos.gerenciar',
    'DOCUMENTOS',
    'Permite cadastrar, atualizar e remover documentos.'
),

-- Férias
(
    'Visualizar férias',
    'ferias.visualizar',
    'FERIAS',
    'Permite consultar férias e períodos aquisitivos.'
),
(
    'Solicitar férias',
    'ferias.solicitar',
    'FERIAS',
    'Permite enviar uma solicitação de férias.'
),
(
    'Aprovar férias',
    'ferias.aprovar',
    'FERIAS',
    'Permite aprovar ou rejeitar solicitações de férias.'
),
(
    'Gerenciar férias',
    'ferias.gerenciar',
    'FERIAS',
    'Permite administrar períodos, saldos e programações de férias.'
),

-- Frequência
(
    'Visualizar frequência',
    'frequencia.visualizar',
    'FREQUENCIA',
    'Permite consultar registros de frequência.'
),
(
    'Registrar frequência',
    'frequencia.registrar',
    'FREQUENCIA',
    'Permite registrar entrada, saída e intervalos.'
),
(
    'Ajustar frequência',
    'frequencia.ajustar',
    'FREQUENCIA',
    'Permite corrigir ou justificar registros de frequência.'
),
(
    'Fechar frequência',
    'frequencia.fechar',
    'FREQUENCIA',
    'Permite realizar o fechamento mensal da frequência.'
),

-- Escalas
(
    'Visualizar escalas',
    'escalas.visualizar',
    'ESCALAS',
    'Permite consultar escalas, plantões e jornadas.'
),
(
    'Gerenciar escalas',
    'escalas.gerenciar',
    'ESCALAS',
    'Permite criar e alterar escalas, plantões e folgas.'
),

-- Avaliações
(
    'Visualizar avaliações',
    'avaliacoes.visualizar',
    'AVALIACOES',
    'Permite visualizar avaliações autorizadas.'
),
(
    'Gerenciar avaliações',
    'avaliacoes.gerenciar',
    'AVALIACOES',
    'Permite criar modelos, ciclos e registrar avaliações.'
),

-- Feedbacks
(
    'Visualizar feedbacks',
    'feedbacks.visualizar',
    'FEEDBACKS',
    'Permite visualizar feedbacks autorizados.'
),
(
    'Registrar feedbacks',
    'feedbacks.registrar',
    'FEEDBACKS',
    'Permite registrar feedbacks para colaboradores.'
),

-- Comunicação
(
    'Visualizar comunicados',
    'comunicados.visualizar',
    'COMUNICADOS',
    'Permite visualizar comunicados recebidos.'
),
(
    'Publicar comunicados',
    'comunicados.publicar',
    'COMUNICADOS',
    'Permite criar e publicar comunicados.'
),

-- Relatórios
(
    'Visualizar relatórios',
    'relatorios.visualizar',
    'RELATORIOS',
    'Permite acessar relatórios gerenciais.'
),
(
    'Exportar relatórios',
    'relatorios.exportar',
    'RELATORIOS',
    'Permite exportar relatórios em PDF, CSV ou outros formatos.'
),

-- Saúde ocupacional
(
    'Visualizar saúde ocupacional',
    'saude-ocupacional.visualizar',
    'SAUDE_OCUPACIONAL',
    'Permite visualizar informações ocupacionais autorizadas.'
),
(
    'Gerenciar saúde ocupacional',
    'saude-ocupacional.gerenciar',
    'SAUDE_OCUPACIONAL',
    'Permite cadastrar exames, ASOs, restrições e ocorrências ocupacionais.'
),

-- Auditoria
(
    'Visualizar logs',
    'logs.visualizar',
    'LOGS',
    'Permite consultar os registros de auditoria do sistema.'
),

-- Configurações
(
    'Gerenciar configurações',
    'configuracoes.gerenciar',
    'CONFIGURACOES',
    'Permite alterar configurações gerais do sistema.'
)

ON CONFLICT (codigo)
DO UPDATE SET
    nome = EXCLUDED.nome,
    modulo = EXCLUDED.modulo,
    descricao = EXCLUDED.descricao,
    ativo = TRUE,
    atualizado_em = CURRENT_TIMESTAMP;

COMMIT;