<?php
class RegraDeNegocioException extends Exception
{
    #[Override]
    public function __construct(string $mensagem)
    {
        return parent::__construct($mensagem);
    }
}
