<?php
/**
 * ChamadoMapa
 * Exibe no mapa os chamados externos com coordenadas
 * @author Jonny
 */
class ChamadoMapa extends TPage
{
    private $html;

    public function __construct()
    {
        parent::__construct();

        // Cria container HTML
        $this->html = new THtmlRenderer('app/resources/chamado_mapa.html');

        // Painel principal
        $panel = new TPanelGroup('🌍 Mapa de Chamados Externos');
        $panel->add($this->html);

        // Adiciona ao layout
        parent::add($panel);
    }

    /**
     * Carrega os dados e atualiza o mapa
     */
    public function onReload($param = NULL)
    {
        try {
            TTransaction::open('permision');

            $repository = new TRepository('Chamado');
            $criteria = new TCriteria;
            $criteria->add(new TFilter('tipo', '=', 'externo'));
            $criteria->add(new TFilter('status', '=', 'aberto'));
            $objects = $repository->load($criteria);

            $markers = [];

            if ($objects)
            {
                foreach ($objects as $chamado)
                {
                    if (!empty($chamado->latitude) && !empty($chamado->longitude))
                    {
                        $markers[] = [
                            'titulo' => $chamado->titulo,
                            'descricao' => substr($chamado->descricao, 0, 200),
                            'lat' => (float) $chamado->latitude,
                            'lng' => (float) $chamado->longitude,
                            'status' => $chamado->status
                        ];
                    }
                }
            }

            TTransaction::close();

            // Passa os dados para o HTML
            $this->html->enableSection('main', [
                'markers_json' => json_encode($markers)
            ]);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
