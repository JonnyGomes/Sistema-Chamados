<?php
/**
 * ChamadoHistorico Active Record
 * Histórico de alterações de status dos chamados
 */
class ChamadoHistorico extends TRecord
{
    const TABLENAME  = 'chamado_historico';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial';

    private $chamado;
    private $usuario;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('chamado_id');
        parent::addAttribute('usuario_id');
        parent::addAttribute('status');
        parent::addAttribute('data_hora');
        parent::addAttribute('observacao');
    }

    public function get_chamado()
    {
        if (empty($this->chamado))
            $this->chamado = new Chamado($this->chamado_id);
        return $this->chamado;
    }

    public function get_usuario()
    {
        if (empty($this->usuario))
            $this->usuario = new SystemUsers($this->usuario_id);
        return $this->usuario;
    }
}
