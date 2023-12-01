<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    |
    | Payment related response messages.
    |
    */

    'checkout_failed' => 'Não foi possível processar a solicitação de pagamento, por favor, tente novamente.',
    'checkout_customer_bad_request' => 'A conta do cliente está incompleta.',
    'checkout_price_bad_request' => 'O preço é inválido.',
    'subscription_invalid' => 'Não há assinatura ativa.',
    'subscription_cancelled' => 'A assinatura foi cancelada com sucesso.',
    'subscription_refunded' => 'A assinatura foi reembolsada com sucesso.',
    'subscription_not_cancelled_for_refund' => 'A assinatura ainda não foi cancelada.',
    'subscription_already_active' => 'Já existe uma assinatura ativa associada ao usuário.',

];
