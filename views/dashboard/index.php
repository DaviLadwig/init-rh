<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Variáveis recebidas pelo DashboardController
|--------------------------------------------------------------------------
*/

/** @var array<string, mixed> $usuario */
/** @var int $totalPermissoes */
/** @var string|null $sucesso */

$usuario = isset($usuario) && is_array($usuario)
    ? $usuario
    : [];

$totalPermissoes = isset($totalPermissoes)
    ? (int) $totalPermissoes
    : 0;

$sucesso = isset($sucesso) && is_string($sucesso)
    ? $sucesso
    : null;

$nomeUsuario = trim(
    (string) ($usuario['nome'] ?? 'Usuário')
);

$nomeOrganizacao = trim(
    (string) (
        $usuario['organizacao_nome']
        ?? 'Organização'
    )
);

$nomePerfil = trim(
    (string) (
        $usuario['perfil_nome']
        ?? 'Perfil não informado'
    )
);
?>

<?php if ($sucesso !== null): ?>

    <div
        class="alert alert--success"
        role="status"
    >
        <?= escapar($sucesso) ?>
    </div>

<?php endif; ?>

<!-- APRESENTAÇÃO -->
<section class="dashboard-welcome">

    <div class="dashboard-welcome__content">

        <span class="dashboard-eyebrow">
            Visão geral
        </span>

        <h1>
            Olá, <?= escapar($nomeUsuario) ?>
        </h1>

        <p>
            Acompanhe as informações da organização e acesse
            os principais módulos de gestão de pessoas.
        </p>

    </div>

    <div
        class="dashboard-welcome__decoration"
        aria-hidden="true"
    >
        <svg viewBox="0 0 24 24">
            <path d="M3 3v18h18" />
            <path d="m7 16 4-5 4 3 5-7" />
        </svg>
    </div>

</section>

<!-- RESUMO DA CONTA -->
<section
    class="dashboard-summary"
    aria-label="Resumo da conta"
>

    <article class="dashboard-card">

        <div class="dashboard-card__header">

            <span class="dashboard-card__icon">
                <svg viewBox="0 0 24 24">
                    <circle
                        cx="12"
                        cy="8"
                        r="4"
                    />

                    <path
                        d="M4 21a8 8 0 0 1 16 0"
                    />
                </svg>
            </span>

            <span class="dashboard-card__label">
                Perfil de acesso
            </span>

        </div>

        <strong class="dashboard-card__value">
            <?= escapar($nomePerfil) ?>
        </strong>

        <span class="dashboard-card__description">
            Define os módulos e ações disponíveis para sua conta.
        </span>

    </article>

    <article class="dashboard-card">

        <div class="dashboard-card__header">

            <span class="dashboard-card__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M3 21h18" />
                    <path d="M6 21V7l6-4 6 4v14" />
                    <path d="M9 9h1" />
                    <path d="M14 9h1" />
                    <path d="M9 13h1" />
                    <path d="M14 13h1" />
                    <path d="M10 21v-4h4v4" />
                </svg>
            </span>

            <span class="dashboard-card__label">
                Organização
            </span>

        </div>

        <strong class="dashboard-card__value">
            <?= escapar($nomeOrganizacao) ?>
        </strong>

        <span class="dashboard-card__description">
            Ambiente organizacional atualmente selecionado.
        </span>

    </article>

    <article class="dashboard-card">

        <div class="dashboard-card__header">

            <span class="dashboard-card__icon">
                <svg viewBox="0 0 24 24">
                    <path
                        d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"
                    />

                    <path d="m9 12 2 2 4-4" />
                </svg>
            </span>

            <span class="dashboard-card__label">
                Permissões carregadas
            </span>

        </div>

        <strong class="dashboard-card__value">
            <?= $totalPermissoes ?>
        </strong>

        <span class="dashboard-card__description">
            Permissões vinculadas ao seu perfil de acesso.
        </span>

    </article>

</section>

<!-- ACESSOS RÁPIDOS -->
<section class="dashboard-section">

    <header class="dashboard-section-header">

        <div>
            <span class="dashboard-eyebrow">
                Acessos rápidos
            </span>

            <h2>
                Gestão de pessoas
            </h2>
        </div>

        <p>
            Os módulos serão ativados conforme avançarmos no desenvolvimento.
        </p>

    </header>

    <div class="dashboard-modules__grid">

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                        />

                        <circle
                            cx="9"
                            cy="7"
                            r="4"
                        />

                        <path
                            d="M22 21v-2a4 4 0 0 0-3-3.87"
                        />

                        <path
                            d="M16 3.13a4 4 0 0 1 0 7.75"
                        />
                    </svg>
                </span>

                <span class="module-card__status">
                    Próximo módulo
                </span>

            </div>

            <h3>Colaboradores</h3>

            <p>
                Cadastros pessoais, vínculos, lotações, jornadas
                e histórico funcional.
            </p>

            <span class="module-card__action">
                Em desenvolvimento
            </span>

        </article>

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 21h18" />
                        <path d="M6 21V7l6-4 6 4v14" />
                        <path d="M9 9h1" />
                        <path d="M14 9h1" />
                        <path d="M9 13h1" />
                        <path d="M14 13h1" />
                    </svg>
                </span>

                <span class="module-card__status">
                    Em breve
                </span>

            </div>

            <h3>Unidades e setores</h3>

            <p>
                Organização das unidades, departamentos, setores
                e responsáveis.
            </p>

            <span class="module-card__action">
                Aguardando desenvolvimento
            </span>

        </article>

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />

                        <path d="M12 7v5l3 2" />
                    </svg>
                </span>

                <span class="module-card__status">
                    Em breve
                </span>

            </div>

            <h3>Frequência</h3>

            <p>
                Registros de ponto, atrasos, justificativas
                e fechamento mensal.
            </p>

            <span class="module-card__action">
                Aguardando desenvolvimento
            </span>

        </article>

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <rect
                            x="3"
                            y="5"
                            width="18"
                            height="16"
                            rx="2"
                        />

                        <path d="M16 3v4" />
                        <path d="M8 3v4" />
                        <path d="M3 10h18" />
                    </svg>
                </span>

                <span class="module-card__status">
                    Em breve
                </span>

            </div>

            <h3>Férias</h3>

            <p>
                Períodos aquisitivos, solicitações, aprovações
                e programação de férias.
            </p>

            <span class="module-card__action">
                Aguardando desenvolvimento
            </span>

        </article>

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <rect
                            x="3"
                            y="4"
                            width="18"
                            height="17"
                            rx="2"
                        />

                        <path d="M8 2v4" />
                        <path d="M16 2v4" />
                        <path d="M3 9h18" />
                        <path d="M8 13h3" />
                        <path d="M13 13h3" />
                        <path d="M8 17h3" />
                    </svg>
                </span>

                <span class="module-card__status">
                    Em breve
                </span>

            </div>

            <h3>Escalas</h3>

            <p>
                Jornadas, plantões, folgas, substituições
                e trocas de escala.
            </p>

            <span class="module-card__action">
                Aguardando desenvolvimento
            </span>

        </article>

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                        />

                        <path d="M14 2v6h6" />
                        <path d="M8 13h8" />
                        <path d="M8 17h8" />
                    </svg>
                </span>

                <span class="module-card__status">
                    Em breve
                </span>

            </div>

            <h3>Documentos</h3>

            <p>
                Contratos, documentos funcionais, certificados
                e controle de validade.
            </p>

            <span class="module-card__action">
                Aguardando desenvolvimento
            </span>

        </article>

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4" />
                        <path
                            d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"
                        />
                    </svg>
                </span>

                <span class="module-card__status">
                    Em breve
                </span>

            </div>

            <h3>Avaliações</h3>

            <p>
                Avaliações de desempenho, critérios, ciclos
                e acompanhamento profissional.
            </p>

            <span class="module-card__action">
                Aguardando desenvolvimento
            </span>

        </article>

        <article class="module-card">

            <div class="module-card__top">

                <span class="module-card__icon">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M21 15a4 4 0 0 1-4 4H8l-5 3v-3a4 4 0 0 1-2-4V7a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4z"
                        />
                    </svg>
                </span>

                <span class="module-card__status">
                    Em breve
                </span>

            </div>

            <h3>Comunicação</h3>

            <p>
                Comunicados internos, avisos por unidade
                e confirmação de leitura.
            </p>

            <span class="module-card__action">
                Aguardando desenvolvimento
            </span>

        </article>

    </div>

</section>

<!-- SITUAÇÃO DO SISTEMA -->
<section class="dashboard-section">

    <header class="dashboard-section-header">

        <div>
            <span class="dashboard-eyebrow">
                Implantação
            </span>

            <h2>
                Situação inicial do sistema
            </h2>
        </div>

        <p>
            A estrutura técnica inicial está funcionando e pronta
            para receber os próximos módulos.
        </p>

    </header>

    <div class="dashboard-status-grid">

        <article class="status-card status-card--success">

            <span class="status-card__indicator"></span>

            <div>
                <strong>Banco de dados</strong>

                <p>
                    PostgreSQL conectado e estrutura inicial criada.
                </p>
            </div>

        </article>

        <article class="status-card status-card--success">

            <span class="status-card__indicator"></span>

            <div>
                <strong>Autenticação</strong>

                <p>
                    Login, sessão, CSRF e permissões funcionando.
                </p>
            </div>

        </article>

        <article class="status-card status-card--success">

            <span class="status-card__indicator"></span>

            <div>
                <strong>Organização</strong>

                <p>
                    Secretaria de Saúde cadastrada e ativa.
                </p>
            </div>

        </article>

        <article class="status-card status-card--pending">

            <span class="status-card__indicator"></span>

            <div>
                <strong>Cadastros estruturais</strong>

                <p>
                    Unidades, setores, cargos e funções serão os próximos.
                </p>
            </div>

        </article>

    </div>

</section>