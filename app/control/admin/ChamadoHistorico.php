<?php
/**
 * ChamadoHistorico Active Record
 * Registra alterações de status e observações dos chamados
 */
class ChamadoHistorico extends TRecord
{
    const TABLENAME  = 'chamado_historico';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial';

    private $usuario;
    private $chamado;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('chamado_id');
        parent::addAttribute('usuario_id');
        parent::addAttribute('status');
        parent::addAttribute('data_hora');
        parent::addAttribute('observacao');
    }

    public function get_usuario()
    {
        if (empty($this->usuario))
            $this->usuario = new SystemUser($this->usuario_id);
        return $this->usuario;
    }

    public function get_chamado()
    {
        if (empty($this->chamado))
            $this->chamado = new Chamado($this->chamado_id);
        return $this->chamado;
    }
}
