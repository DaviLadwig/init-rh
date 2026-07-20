<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

require_once BASE_PATH
    . '/app/middleware/AuthMiddleware.php';

require_once BASE_PATH
    . '/app/middleware/PermissionMiddleware.php';

require_once BASE_PATH
    . '/app/repositories/UsuarioRepository.php';

require_once BASE_PATH
    . '/app/repositories/SetorRepository.php';

require_once BASE_PATH
    . '/app/repositories/CargoRepository.php';

require_once BASE_PATH
    . '/app/repositories/FuncaoRepository.php';

require_once BASE_PATH
    . '/app/repositories/TipoVinculoRepository.php';

require_once BASE_PATH
    . '/app/repositories/JornadaTrabalhoRepository.php';

require_once BASE_PATH
    . '/app/services/AutenticacaoService.php';

require_once BASE_PATH
    . '/app/services/SetorService.php';

require_once BASE_PATH
    . '/app/services/CargoService.php';

require_once BASE_PATH
    . '/app/services/FuncaoService.php';

require_once BASE_PATH
    . '/app/services/TipoVinculoService.php';

require_once BASE_PATH
    . '/app/services/JornadaTrabalhoService.php';

require_once BASE_PATH
    . '/app/controllers/AuthController.php';

require_once BASE_PATH
    . '/app/controllers/DashboardController.php';

require_once BASE_PATH
    . '/app/controllers/SetorController.php';

require_once BASE_PATH
    . '/app/controllers/CargoController.php';

require_once BASE_PATH
    . '/app/controllers/FuncaoController.php';

require_once BASE_PATH
    . '/app/controllers/TipoVinculoController.php';

require_once BASE_PATH
    . '/app/controllers/JornadaTrabalhoController.php';

/*
|--------------------------------------------------------------------------
| Conexão
|--------------------------------------------------------------------------
*/

$pdo = conectarBanco();

/*
|--------------------------------------------------------------------------
| Repositories
|--------------------------------------------------------------------------
*/

$usuarioRepository =
    new UsuarioRepository($pdo);

$setorRepository =
    new SetorRepository($pdo);

$cargoRepository =
    new CargoRepository($pdo);

$funcaoRepository =
    new FuncaoRepository($pdo);

$tipoVinculoRepository =
    new TipoVinculoRepository($pdo);

$jornadaTrabalhoRepository =
    new JornadaTrabalhoRepository($pdo);

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

$autenticacaoService =
    new AutenticacaoService(
        $usuarioRepository
    );

$setorService =
    new SetorService(
        $setorRepository
    );

$cargoService =
    new CargoService(
        $cargoRepository
    );

$funcaoService =
    new FuncaoService(
        $funcaoRepository
    );

$tipoVinculoService =
    new TipoVinculoService(
        $tipoVinculoRepository
    );

$jornadaTrabalhoService =
    new JornadaTrabalhoService(
        $jornadaTrabalhoRepository
    );

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

$authController =
    new AuthController(
        $autenticacaoService
    );

$dashboardController =
    new DashboardController();

$setorController =
    new SetorController(
        $setorService
    );

$cargoController =
    new CargoController(
        $cargoService
    );

$funcaoController =
    new FuncaoController(
        $funcaoService
    );

$tipoVinculoController =
    new TipoVinculoController(
        $tipoVinculoService
    );

$jornadaTrabalhoController =
    new JornadaTrabalhoController(
        $jornadaTrabalhoService
    );

/*
|--------------------------------------------------------------------------
| Rotas
|--------------------------------------------------------------------------
*/

return [

    'GET' => [

        '/' => static function (): void {
            redirecionar('login');
        },

        '/login' => static function () use (
            $authController
        ): void {
            $authController->login();
        },

        '/alterar-senha' => static function () use (
            $authController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $authController
                ): void {
                    $authController->alterarSenha();
                },
                true
            );
        },

        '/dashboard' => static function () use (
            $dashboardController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $dashboardController
                ): void {
                    $dashboardController->index();
                }
            );
        },

        /*
        |------------------------------------------------------------------
        | Setores
        |------------------------------------------------------------------
        */

        '/setores' => static function () use (
            $setorController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $setorController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.visualizar',
                        static function () use (
                            $setorController
                        ): void {
                            $setorController->index();
                        }
                    );
                }
            );
        },

        '/setores/criar' => static function () use (
            $setorController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $setorController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $setorController
                        ): void {
                            $setorController->criar();
                        }
                    );
                }
            );
        },

        '/setores/editar' => static function () use (
            $setorController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $setorController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $setorController
                        ): void {
                            $setorController->editar();
                        }
                    );
                }
            );
        },

        /*
        |------------------------------------------------------------------
        | Cargos
        |------------------------------------------------------------------
        */

        '/cargos' => static function () use (
            $cargoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $cargoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.visualizar',
                        static function () use (
                            $cargoController
                        ): void {
                            $cargoController->index();
                        }
                    );
                }
            );
        },

        '/cargos/criar' => static function () use (
            $cargoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $cargoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $cargoController
                        ): void {
                            $cargoController->criar();
                        }
                    );
                }
            );
        },

        '/cargos/editar' => static function () use (
            $cargoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $cargoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $cargoController
                        ): void {
                            $cargoController->editar();
                        }
                    );
                }
            );
        },

        /*
        |------------------------------------------------------------------
        | Funções
        |------------------------------------------------------------------
        */

        '/funcoes' => static function () use (
            $funcaoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $funcaoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.visualizar',
                        static function () use (
                            $funcaoController
                        ): void {
                            $funcaoController->index();
                        }
                    );
                }
            );
        },

        '/funcoes/criar' => static function () use (
            $funcaoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $funcaoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $funcaoController
                        ): void {
                            $funcaoController->criar();
                        }
                    );
                }
            );
        },

        '/funcoes/editar' => static function () use (
            $funcaoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $funcaoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $funcaoController
                        ): void {
                            $funcaoController->editar();
                        }
                    );
                }
            );
        },


        /*
        |------------------------------------------------------------------
        | Tipos de vínculo
        |------------------------------------------------------------------
        */

        '/tipos-vinculo' => static function () use (
            $tipoVinculoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $tipoVinculoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.visualizar',
                        static function () use (
                            $tipoVinculoController
                        ): void {
                            $tipoVinculoController->index();
                        }
                    );
                }
            );
        },

        '/tipos-vinculo/criar' => static function () use (
            $tipoVinculoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $tipoVinculoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $tipoVinculoController
                        ): void {
                            $tipoVinculoController->criar();
                        }
                    );
                }
            );
        },

        '/tipos-vinculo/editar' => static function () use (
            $tipoVinculoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $tipoVinculoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $tipoVinculoController
                        ): void {
                            $tipoVinculoController->editar();
                        }
                    );
                }
            );
        },

        /*
        |------------------------------------------------------------------
        | Jornadas de trabalho
        |------------------------------------------------------------------
        */

        '/jornadas' => static function () use (
            $jornadaTrabalhoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $jornadaTrabalhoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.visualizar',
                        static function () use (
                            $jornadaTrabalhoController
                        ): void {
                            $jornadaTrabalhoController->index();
                        }
                    );
                }
            );
        },

        '/jornadas/criar' => static function () use (
            $jornadaTrabalhoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $jornadaTrabalhoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $jornadaTrabalhoController
                        ): void {
                            $jornadaTrabalhoController->criar();
                        }
                    );
                }
            );
        },

        '/jornadas/editar' => static function () use (
            $jornadaTrabalhoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $jornadaTrabalhoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $jornadaTrabalhoController
                        ): void {
                            $jornadaTrabalhoController->editar();
                        }
                    );
                }
            );
        },

        /*
         * Diagnóstico temporário.
         */
        '/teste' => static function (): void {
            header(
                'Content-Type: application/json; charset=UTF-8'
            );

            echo json_encode(
                [
                    'sucesso' => true,
                    'mensagem' =>
                        'O roteamento está funcionando.',
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_PRETTY_PRINT
            );
        },

    ],

    'POST' => [

        '/login' => static function () use (
            $authController
        ): void {
            $authController->autenticar();
        },

        '/alterar-senha' => static function () use (
            $authController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $authController
                ): void {
                    $authController->salvarNovaSenha();
                },
                true
            );
        },

        '/logout' => static function () use (
            $authController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $authController
                ): void {
                    $authController->logout();
                },
                true
            );
        },

        /*
        |------------------------------------------------------------------
        | Setores
        |------------------------------------------------------------------
        */

        '/setores/criar' => static function () use (
            $setorController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $setorController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $setorController
                        ): void {
                            $setorController->salvar();
                        }
                    );
                }
            );
        },

        '/setores/editar' => static function () use (
            $setorController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $setorController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $setorController
                        ): void {
                            $setorController->atualizar();
                        }
                    );
                }
            );
        },

        '/setores/alterar-status' => static function () use (
            $setorController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $setorController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $setorController
                        ): void {
                            $setorController->alterarStatus();
                        }
                    );
                }
            );
        },

        /*
        |------------------------------------------------------------------
        | Cargos
        |------------------------------------------------------------------
        */

        '/cargos/criar' => static function () use (
            $cargoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $cargoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $cargoController
                        ): void {
                            $cargoController->salvar();
                        }
                    );
                }
            );
        },

        '/cargos/editar' => static function () use (
            $cargoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $cargoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $cargoController
                        ): void {
                            $cargoController->atualizar();
                        }
                    );
                }
            );
        },

        '/cargos/alterar-status' => static function () use (
            $cargoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $cargoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $cargoController
                        ): void {
                            $cargoController->alterarStatus();
                        }
                    );
                }
            );
        },

        /*
        |------------------------------------------------------------------
        | Funções
        |------------------------------------------------------------------
        */

        '/funcoes/criar' => static function () use (
            $funcaoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $funcaoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $funcaoController
                        ): void {
                            $funcaoController->salvar();
                        }
                    );
                }
            );
        },

        '/funcoes/editar' => static function () use (
            $funcaoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $funcaoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $funcaoController
                        ): void {
                            $funcaoController->atualizar();
                        }
                    );
                }
            );
        },

        '/funcoes/alterar-status' => static function () use (
            $funcaoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $funcaoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $funcaoController
                        ): void {
                            $funcaoController->alterarStatus();
                        }
                    );
                }
            );
        },


        /*
        |------------------------------------------------------------------
        | Tipos de vínculo
        |------------------------------------------------------------------
        */

        '/tipos-vinculo/criar' => static function () use (
            $tipoVinculoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $tipoVinculoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $tipoVinculoController
                        ): void {
                            $tipoVinculoController->salvar();
                        }
                    );
                }
            );
        },

        '/tipos-vinculo/editar' => static function () use (
            $tipoVinculoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $tipoVinculoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $tipoVinculoController
                        ): void {
                            $tipoVinculoController->atualizar();
                        }
                    );
                }
            );
        },

        '/tipos-vinculo/alterar-status' => static function () use (
            $tipoVinculoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $tipoVinculoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $tipoVinculoController
                        ): void {
                            $tipoVinculoController->alterarStatus();
                        }
                    );
                }
            );
        },

        /*
        |------------------------------------------------------------------
        | Jornadas de trabalho
        |------------------------------------------------------------------
        */

        '/jornadas/criar' => static function () use (
            $jornadaTrabalhoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $jornadaTrabalhoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $jornadaTrabalhoController
                        ): void {
                            $jornadaTrabalhoController->salvar();
                        }
                    );
                }
            );
        },

        '/jornadas/editar' => static function () use (
            $jornadaTrabalhoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $jornadaTrabalhoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $jornadaTrabalhoController
                        ): void {
                            $jornadaTrabalhoController->atualizar();
                        }
                    );
                }
            );
        },

        '/jornadas/alterar-status' => static function () use (
            $jornadaTrabalhoController
        ): void {
            AuthMiddleware::executar(
                static function () use (
                    $jornadaTrabalhoController
                ): void {
                    PermissionMiddleware::executar(
                        'estrutura.gerenciar',
                        static function () use (
                            $jornadaTrabalhoController
                        ): void {
                            $jornadaTrabalhoController->alterarStatus();
                        }
                    );
                }
            );
        },

    ],

];