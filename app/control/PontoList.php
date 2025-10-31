<?php
/**
 * PontoList
 * Lista os registros de ponto com localização
 * @author Jonny
 */
class PontoList extends TPage
{
    private $form;
    private $datagrid;
    private $pageNavigation;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_ponto_search');
        $this->form->setFormTitle('⏱️ Controle de Ponto');

        $usuario_id = new TDBCombo('usuario_id', 'permission', 'SystemUser', 'id', 'name');
        $tipo       = new TCombo('tipo');
        $tipo->addItems(['entrada' => 'Entrada', 'saida' => 'Saída']);

        $this->form->addFields([new TLabel('Usuário')], [$usuario_id]);
        $this->form->addFields([new TLabel('Tipo')], [$tipo]);

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');

        // === Datagrid ===
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        $col_id       = new TDataGridColumn('id', 'ID', 'center', 50);
        $col_usuario  = new TDataGridColumn('usuario->name', 'Usuário', 'left');
        $col_tipo     = new TDataGridColumn('tipo', 'Tipo', 'center');
        $col_entrada  = new TDataGridColumn('data_hora_entrada', 'Entrada', 'center');
        $col_saida    = new TDataGridColumn('data_hora_saida', 'Saída', 'center');
        $col_latlng   = new TDataGridColumn('latitude', 'Latitude', 'right');
        $col_lng      = new TDataGridColumn('longitude', 'Longitude', 'right');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_usuario);
        $this->datagrid->addColumn($col_tipo);
        $this->datagrid->addColumn($col_entrada);
        $this->datagrid->addColumn($col_saida);
        $this->datagrid->addColumn($col_latlng);
        $this->datagrid->addColumn($col_lng);

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        parent::add($vbox);
    }

    public function onReload($param = NULL)
    {
        try {
            TTransaction::open('permission');

            $repository = new TRepository('Ponto');
            $criteria = new TCriteria;
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 20);

            $objects = $repository->load($criteria);

            $this->datagrid->clear();

            if ($objects) {
                foreach ($objects as $obj) {
                    $obj->usuario = new SystemUsers($obj->usuario_id);
                    $this->datagrid->addItem($obj);
                }
            }

            $count = $repository->count($criteria);
            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($criteria->getProperty('limit'));

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSearch($param)
    {
        $this->onReload($param);
    }
}
