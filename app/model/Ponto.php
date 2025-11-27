<?php
/**
 * Ponto Active Record
 * @author  Jonny
 */
class Ponto extends TRecord
{
    const TABLENAME  = 'ponto';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial';

    private $usuario;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('usuario_id');
        parent::addAttribute('data_hora_entrada');
        parent::addAttribute('data_hora_entrada_2');
        parent::addAttribute('data_hora_saida');
        parent::addAttribute('data_hora_saida_1');
        parent::addAttribute('latitude');
        parent::addAttribute('longitude');
        parent::addAttribute('tipo');
    }

    public function get_usuario()
    {
        if (empty($this->usuario))
            $this->usuario = new SystemUser($this->usuario_id);
        return $this->usuario;
    }
}
