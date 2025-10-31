<?php
/**
 * ChamadoList
 * Lista e gerencia os chamados
 * @author Jonny
 */
class ChamadoList extends TPage
{
    private $form;      // formulário de filtros
    private $datagrid;  // tabela de dados
    private $pageNavigation;

    public function __construct()
    {
        parent::__construct();

        // Cria formulário
        $this->form = new BootstrapFormBuilder('form_search_chamado');
        $this->form->setFormTitle('📋 Lista de Chamados');

        $titulo  = new TEntry('titulo');
        $status  = new TCombo('status');
        $status->addItems(['Aberto'=>'Aberto','Em andamento'=>'Em andamento','Fechado'=>'Fechado']);

        $this->form->addFields([new TLabel('Título')], [$titulo]);
        $this->form->addFields([new TLabel('Status')], [$status]);

        $titulo->setSize('70%');
        $status->setSize('70%');

        // Botão de busca
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addAction('Novo', new TAction(['ChamadoForm', 'onEdit']), 'fa:plus green');

        // Cria DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        // Colunas
        $col_id          = new TDataGridColumn('id', 'ID', 'center', 50);
        $col_titulo      = new TDataGridColumn('titulo', 'Título', 'left');
        $col_tipo        = new TDataGridColumn('tipo', 'Tipo', 'center');
        $col_status      = new TDataGridColumn('status', 'Status', 'center');
        $col_data_abertura = new TDataGridColumn('data_abertura', 'Abertura', 'center');
        $col_tecnico     = new TDataGridColumn('responsavel->name', 'Responsável', 'left');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_titulo);
        $this->datagrid->addColumn($col_tipo);
        $this->datagrid->addColumn($col_status);
        $this->datagrid->addColumn($col_data_abertura);

        // Ações
        $action_edit = new TDataGridAction(['ChamadoForm', 'onEdit'], ['id'=>'{id}']);
        $action_edit->setLabel('Editar');
        $action_edit->setImage('fa:edit blue');

        $action_del = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action_del->setLabel('Excluir');
        $action_del->setImage('fa:trash red');

        $this->datagrid->addAction($action_edit);
        $this->datagrid->addAction($action_del);

        $this->datagrid->createModel();

        // Navegação
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        // Layout
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

            $repository = new TRepository('Chamado');
            $criteria = new TCriteria;
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 10);

            $objects = $repository->load($criteria, FALSE);

            $this->datagrid->clear();
            if ($objects)
            {
                foreach ($objects as $obj)
                {
                    $this->datagrid->addItem($obj);
                }
            }

            $count = $repository->count($criteria);
            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($criteria->getProperty('limit'));

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSearch($param)
    {
        $data = $this->form->getData();
        $this->form->setData($data);
        $this->onReload($param);
    }

    public function onDelete($param)
    {
        try {
            $key = $param['id'];
            TTransaction::open('permission');
            $object = new Chamado($key);
            $object->delete();
            TTransaction::close();
            new TMessage('info', 'Chamado excluído com sucesso!');
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
