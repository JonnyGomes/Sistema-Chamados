<?php
/**
 * Chamado Active Record
 * @author  Jonny
 */
class Chamado extends TRecord
{
    const TABLENAME  = 'chamado';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial'; // auto_increment

    private $usuario_abertura;
    private $responsavel;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('titulo');
        parent::addAttribute('descricao');
        parent::addAttribute('tipo');
        parent::addAttribute('status');
        parent::addAttribute('solucao');
        parent::addAttribute('usuario_abertura_id');
        parent::addAttribute('responsavel_id');
        parent::addAttribute('data_abertura');
        parent::addAttribute('data_fechamento');
        parent::addAttribute('latitude');
        parent::addAttribute('longitude');
        parent::addAttribute('observacoes');
    }

    // ---- Relações ----

    public function get_usuario_abertura()
    {
        if (empty($this->usuario_abertura))
            $this->usuario_abertura = new SystemUsers($this->usuario_abertura_id);
        return $this->usuario_abertura;
    }

    public function get_responsavel()
    {
        if (empty($this->responsavel) && $this->responsavel_id)
            $this->responsavel = new SystemUsers($this->responsavel_id);
        return $this->responsavel;
    }

    public function get_historicos()
    {
        return ChamadoHistorico::where('chamado_id', '=', $this->id)->load();
    }

    public function get_anexos()
    {
        return ChamadoAnexo::where('chamado_id', '=', $this->id)->load();
    }
}
