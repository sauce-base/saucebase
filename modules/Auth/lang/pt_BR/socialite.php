<?php

/*
    |--------------------------------------------------------------------------
    | Autenticação Social
    |--------------------------------------------------------------------------
    |
    | As seguintes linhas de idioma são usadas para recursos de autenticação
    | social, incluindo conectar e desconectar contas sociais, tratamento de
    | erros e fornecimento de mensagens de feedback para vários cenários de
    | login social.
    |
    */

return [
    'connect_with' => 'Conectar com :Provider',
    'disconnect' => 'Desconectar',
    'not_connected' => 'O provedor :Provider não está conectado',
    'connected' => 'Conectado',
    'account_connected' => 'Conta :Provider conectada com sucesso',
    'account_disconnected' => 'Conta :Provider desconectada com sucesso',
    'error' => 'Ocorreu um erro ao processar sua conta social',
    'invalid_user' => 'Dados de conta social inválidos recebidos',
    'invalid_provider' => 'O provedor social selecionado é inválido ou não é suportado',
    'cannot_disconnect_only_method' => 'Não é possível desconectar seu único método de login. Defina uma senha primeiro ou conecte outro provedor e tente novamente',
    'missing_social_accounts_relation' => 'O modelo User está faltando o relacionamento socialAccounts necessário para autenticação social',
    'account_already_linked' => 'Esta conta :provider já está vinculada a outro usuário',
    'unsupported_provider' => 'O provedor social :provider não é suportado',
];
