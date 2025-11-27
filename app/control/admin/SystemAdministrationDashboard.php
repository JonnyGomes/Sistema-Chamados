<?php
/**
 * Dashboard personalizado para o sistema de chamados
 */
class SystemAdministrationDashboard extends TPage
{
    public function __construct()
    {
        parent::__construct();

        try {
            $html = new THtmlRenderer('app/resources/system/admin/dashboard.html');

            // === TRANSAÇÃO ===
            TTransaction::open('permission');

            // ===== INDICADORES DO SISTEMA =====
            $indicator1 = new TNumericIndicator;
            $indicator1->setTitle('Usuários');
            $indicator1->setValue(SystemUser::count());
            $indicator1->setIcon('user');
            $indicator1->setColor('#ff851b');
            $indicator1->setNumericMask(0, ',', '.');

            $indicator2 = new TNumericIndicator;
            $indicator2->setTitle('Grupos');
            $indicator2->setValue(SystemGroup::count());
            $indicator2->setIcon('users');
            $indicator2->setColor('#0073b7');
            $indicator2->setNumericMask(0, ',', '.');

            $indicator3 = new TNumericIndicator;
            $indicator3->setTitle('Unidades');
            $indicator3->setValue(SystemUnit::count());
            $indicator3->setIcon('university');
            $indicator3->setColor('#605ca8');
            $indicator3->setNumericMask(0, ',', '.');

            $indicator4 = new TNumericIndicator;
            $indicator4->setTitle('Chamados');
            $indicator4->setValue(Chamado::count());
            $indicator4->setIcon('headset');
            $indicator4->setColor('#00a65a');
            $indicator4->setNumericMask(0, ',', '.');

            // ======== INDICADORES DE CHAMADOS ========
            $total_chamados     = Chamado::count();
            $abertos            = Chamado::where('status', '=', 'aberto')->count();
            $andamento          = Chamado::where('status', '=', 'andamento')->count();
            $resolvidos         = Chamado::where('status', '=', 'resolvido')->count();
            $fechados           = Chamado::where('status', '=', 'fechado')->count();

            $indicator5 = new TNumericIndicator;
            $indicator5->setTitle('Chamados Totais');
            $indicator5->setValue($total_chamados);
            $indicator5->setIcon('inbox');
            $indicator5->setColor('#3c8dbc');

            $indicator6 = new TNumericIndicator;
            $indicator6->setTitle('Abertos');
            $indicator6->setValue($abertos);
            $indicator6->setIcon('folder-open');
            $indicator6->setColor('#dd4b39');

            $indicator7 = new TNumericIndicator;
            $indicator7->setTitle('Em Andamento');
            $indicator7->setValue($andamento);
            $indicator7->setIcon('spinner');
            $indicator7->setColor('#f39c12');

            $indicator8 = new TNumericIndicator;
            $indicator8->setTitle('Resolvidos');
            $indicator8->setValue($resolvidos);
            $indicator8->setIcon('check');
            $indicator8->setColor('#00a65a');

            // ======== GRÁFICOS ========
            // Gráfico 1 - Chamados por Status
            $chartStatus = new TPieChart;
            $chartStatus->setTitle('Distribuição de Chamados por Status');
            $chartStatus->setHeight(400);
            $chartStatus->addSlice('Abertos', $abertos, '#dd4b39');
            $chartStatus->addSlice('Andamento', $andamento, '#f39c12');
            $chartStatus->addSlice('Resolvidos', $resolvidos, '#00a65a');
            $chartStatus->addSlice('Fechados', $fechados, '#777');

            // Gráfico 2 - Chamados por Responsável
            $chartResponsavel = new TBarChart;
            $chartResponsavel->setTitle('Chamados por Responsável');
            $chartResponsavel->setHeight(400);
            $stats = Chamado::groupBy('responsavel_id')->countBy('id', 'total');

            if ($stats) {
                foreach ($stats as $row) {
                    $usuario = SystemUser::find($row->responsavel_id);
                    if ($usuario) {
                        $chartResponsavel->addDataSet($usuario->name, [(int)$row->total]);
                    }
                }
            }

            // ======== RENDERIZAÇÃO ========
            $html->enableSection('main', [
                'indicator1' => $indicator1,
                'indicator2' => $indicator2,
                'indicator3' => $indicator3,
                'indicator4' => $indicator4,
                'indicator5' => $indicator5,
                'indicator6' => $indicator6,
                'indicator7' => $indicator7,
                'indicator8' => $indicator8,
                'chart1'     => TPanelGroup::pack('Chamados por Status', $chartStatus),
                'chart2'     => TPanelGroup::pack('Chamados por Responsável', $chartResponsavel)
            ]);

            // ======== CONTAINER ========
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $container->add($html);

            parent::add($container);

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
