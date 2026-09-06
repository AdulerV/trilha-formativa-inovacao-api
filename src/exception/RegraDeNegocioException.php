<?php
class RegraDeNegocioException extends Exception
{
    /*
     * O atributo #[Override] foi removido: a partir do PHP 8.3 ele é
     * validado em tempo de compilação e, aplicado a __construct, gera
     * "has #[\Override] attribute, but no matching parent method
     * exists" — um erro fatal em qualquer ponto que lance esta
     * exceção. Construtor não é método sobrescrevível para efeito do
     * atributo.
     */
    public function __construct(string $mensagem)
    {
        parent::__construct($mensagem);
    }
}
