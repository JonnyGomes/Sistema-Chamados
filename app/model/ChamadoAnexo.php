<?php
/**
 * ChamadoAnexo Active Record
 * Armazena anexos vinculados aos chamados
 */
class ChamadoAnexo extends TRecord
{
    const TABLENAME  = 'chamado_anexo';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial';

    private $chamado;
    private $usuario;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('chamado_id');
        parent::addAttribute('arquivo');
        parent::addAttribute('data_upload');
        parent::addAttribute('usuario_id');
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
