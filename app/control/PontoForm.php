<?php
/**
 * PontoForm
 * Registro de ponto (1 registro por dia) com geolocalização e timeline
 * Autor: Jonny (adaptado)
 */
class PontoForm extends TPage
{
    protected $form;
    protected $timeline; 

    public function __construct()
    {
        parent::__construct();

        // Formulário (usamos campos ocultos para latitude/longitude e para usuario)
        $this->form = new BootstrapFormBuilder('form_ponto_timeline');
        
        // Campos (visíveis/ocultos)
        $lblDate = new TLabel('Data');
        $lblDate->setProperty('style', 'font-weight:bold');

        $dateNow = new TLabel(date('d/m/Y'));
        $timeNow = new TLabel(date('H:i:s'));
        $timeNow->setId('current_time_label');

        $usuario_id = new THidden('usuario_id');
        $latitude   = new THidden('latitude');
        $longitude  = new THidden('longitude');

        // Campo visível que mostra localização (lat/lng)
        $locationDisplay = new TEntry('location_display');
        $locationDisplay->setEditable(FALSE);
        $locationDisplay->placeholder = 'Aguardando localização...';

        // Hidden for debugging if needed (not added to form)
        $this->form->addContent( [$lblDate, $dateNow, new TLabel(''), $timeNow] );
        $this->form->addFields([new TLabel('LOCALIZAÇÃO')], [$locationDisplay]);
        $this->form->addFields([$usuario_id]); // hidden
        $this->form->addFields([$latitude], [$longitude]); // hidden

        // Botão registrar
        $btn = $this->form->addAction('Registrar Ponto', new TAction([$this, 'onSave']), 'fa:thumbs-up white');
        $btn->class = 'btn btn-primary btn-lg';
        $btn->style = 'width:260px; margin: 12px auto; display:block;';

        // Timeline area (HTML)
        $this->timeline = new TElement('div');
        $this->timeline->id = 'timeline_container';
        $this->timeline->style = 'margin-top:20px;';

        // Painel agrupando form + timeline
        $panel = new TPanelGroup('Registro de Ponto');
        $panel->add($this->form);
        $panel->add($this->timeline);

        parent::add($panel);

        // JS - atualiza relógio a cada segundo e pega geolocalização
        TScript::create("
            // atualiza relógio na tela
            setInterval(function(){
                var now = new Date();
                var hh = ('0'+now.getHours()).slice(-2);
                var mm = ('0'+now.getMinutes()).slice(-2);
                var ss = ('0'+now.getSeconds()).slice(-2);
                var timeStr = hh+':'+mm+':'+ss;
                var lbl = document.getElementById('current_time_label');
                if (lbl) lbl.innerText = timeStr;
            }, 1000);

            // pega geolocalização e preenche campos hidden e visual
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    document.querySelector('input[name=latitude]').value = lat;
                    document.querySelector('input[name=longitude]').value = lng;
                    var display = document.querySelector('input[name=location_display]');
                    if (display) {
                        display.value = lat.toFixed(6) + ' , ' + lng.toFixed(6);
                    }
                }, function(err){
                    console.warn('geolocation error', err);
                    var display = document.querySelector('input[name=location_display]');
                    if (display) display.value = 'Localização indisponível';
                }, { enableHighAccuracy: true, timeout: 10000 });
            } else {
                var display = document.querySelector('input[name=location_display]');
                if (display) display.value = 'Navegador não suporta geolocalização';
            }
        ");

        // Carrega dados existentes para montar timeline
        $this->onReload();
    }

    /**
     * Monta timeline com os dados do dia do usuário logado
     */
    public function onReload($param = NULL)
    {
        try {
            // busca sessão
            $user = TSession::getValue('userid');
            if (!$user) {
                // limpa timeline e exibe mensagem
                $this->timeline->add("<div class='alert alert-warning'>Sessão expirada. Faça login novamente.</div>");
                return;
            }

            TTransaction::open('permission');

            // Busca registro do dia (uma linha por dia)
            $criteria = new TCriteria;
            $criteria->add(new TFilter('usuario_id', '=', $user));
            // verifica data em data_hora_entrada ou data_hora_saida (qualquer um marcado hoje)
            $criteria->add(new TFilter('DATE(data_hora_entrada)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_saida)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 1);

            $repository = new TRepository('Ponto');
            $ponto = $repository->load($criteria, FALSE);

            $html = '';

            if ($ponto && count($ponto) > 0) {
                $p = $ponto[0];

                // timeline markup (simples e responsivo)
                $html .= "<div style='display:flex; justify-content:center; margin-top:20px;'>";
                $html .= "<div style='width:800px; max-width:95%;'>";
                $html .= "<div style='position:relative; padding-left:60px;'>";
                // vertical line
                $html .= "<div style='position:absolute; left:30px; top:0; bottom:0; width:4px; background:#e9ecef; border-radius:4px;'></div>";

                // Entrada
                if (!empty($p->data_hora_entrada)) {
                    $dtEntrada = (new DateTime($p->data_hora_entrada))->format('H:i:s');
                    $html .= $this->timelineItem('Entrada', $dtEntrada, 'green', -60);
                } else {
                    $html .= $this->timelineItem('Entrada', '--:--:--', 'gray', -60);
                }

                // Saída
                if (!empty($p->data_hora_saida)) {
                    $dtSaida = (new DateTime($p->data_hora_saida))->format('H:i:s');
                    $html .= $this->timelineItem('Saída', $dtSaida, 'red', 40);
                } else {
                    $html .= $this->timelineItem('Saída', '--:--:--', 'gray', 40);
                }

                $html .= "</div>"; // relative
                $html .= "</div>"; // container
                $html .= "</div>";
            } else {
                $html .= "<div class='alert alert-info'>Nenhum ponto registrado para hoje.</div>";
            }

            $this->timeline->children = [];
            $this->timeline->add($html);

            TTransaction::close();
        } catch (Exception $e) {
            TTransaction::rollback();
            $this->timeline->add("<div class='alert alert-danger'>Erro ao carregar timeline: {$e->getMessage()}</div>");
        }
    }

    /**
     * Helper para montar um item da timeline
     */
    private function timelineItem($title, $time, $color='green', $offset=0)
    {
        $icon = ($color == 'green') ? 'fa-sign-in-alt' : (($color == 'red') ? 'fa-sign-out-alt' : 'fa-circle');
        $bg = ($color == 'green') ? '#28a745' : (($color == 'red') ? '#dc3545' : '#6c757d');

        $item  = "<div style='margin:32px 0; position:relative;'>";
        $item .= "<div style='position:absolute; left:0; top:0; width:60px; text-align:center;'>";
        $item .= "<div style='width:40px; height:40px; line-height:40px; border-radius:50%; background:{$bg}; color:#fff; display:inline-block;'>";
        $item .= "<i class='fas {$icon}'></i></div></div>";
        $item .= "<div style='margin-left:80px; background:#fff; border:1px solid #e9ecef; padding:14px; border-radius:6px;'>";
        $item .= "<div style='font-weight:700; color:#333;'>{$title}</div>";
        $item .= "<div style='margin-top:8px; font-size:18px; color:#555;'>{$time}</div>";
        $item .= "</div></div>";

        return $item;
    }

    /**
     * Ao clicar em Registrar Ponto
     */
    public function onSave($param)
    {
        try {
            // Abre conexão
            TTransaction::open('permission');

            // Sessão
            $user = TSession::getValue('userid');
            if (!$user) {
                throw new Exception('Sessão expirada. Faça login novamente.');
            }

            // pega valores do form (hidden latitude/longitude)
            $data = $this->form->getData();
            $lat = isset($data->latitude) ? $data->latitude : null;
            $lng = isset($data->longitude) ? $data->longitude : null;

            // hora atual do servidor
            $now = new DateTime('now');
            $hora = (int) $now->format('H');
            $timeFull = $now->format('Y-m-d H:i:s');

            // determina tipo conforme faixa horária
            $tipoAtual = null;
            if ($hora >= 5 && $hora <= 11) {
                $tipoAtual = 'entrada';
            } elseif ($hora >= 12 && $hora <= 18) {
                $tipoAtual = 'saida';
            } else {
                throw new Exception('Fora do horário permitido para registrar ponto (05:00–18:00).');
            }

            // busca registro do usuário para HOJE (uma linha por dia)
            $criteria = new TCriteria;
            $criteria->add(new TFilter('usuario_id', '=', $user));
            $criteria->add(new TFilter('DATE(data_hora_entrada)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_saida)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 1);

            $repo = new TRepository('Ponto');
            $pontos = $repo->load($criteria, FALSE);
            $ponto = ($pontos && count($pontos)>0) ? $pontos[0] : null;

            // regra: se não existe registro hoje e tipo atual é 'saida' -> bloquear (exigir entrada primeiro)
            if (!$ponto && $tipoAtual == 'saida') {
                throw new Exception('Não é possível registrar saída sem ter registrado a entrada hoje.');
            }

            // regra: impedir tipo duplicado consecutivo
            if ($ponto) {
                // determina último tipo registrado
                $ultimoTipo = null;
                if (!empty($ponto->data_hora_saida)) $ultimoTipo = 'saida';
                elseif (!empty($ponto->data_hora_entrada)) $ultimoTipo = 'entrada';

                if ($ultimoTipo == $tipoAtual) {
                    throw new Exception("Você já registrou um ponto de tipo '{$tipoAtual}' por último. Aguarde o próximo registro de outro tipo.");
                }
            }

            // grava/atualiza
            if (!$ponto) {
                // cria novo registro do dia com entrada preenchida
                $pontoObj = new Ponto;
                $pontoObj->usuario_id = $user;
                if ($tipoAtual == 'entrada') {
                    $pontoObj->data_hora_entrada = $timeFull;
                } else { // não deve ocorrer pq bloqueamos saida sem entrada
                    $pontoObj->data_hora_saida = $timeFull;
                }
                $pontoObj->latitude = $lat;
                $pontoObj->longitude = $lng;
                // para histórico simples, podemos guardar o tipo último
                $pontoObj->tipo = $tipoAtual;
                $pontoObj->store();
            } else {
                // atualiza registro do dia: se for saída e ainda não tem saída -> seta saída
                if ($tipoAtual == 'saida') {
                    $ponto->data_hora_saida = $timeFull;
                    $ponto->longitude = $lng;
                    $ponto->latitude = $lat;
                    $ponto->tipo = $tipoAtual;
                    $ponto->store();
                } else {
                  
                    if (empty($ponto->data_hora_entrada)) {
                        $ponto->data_hora_entrada = $timeFull;
                        $ponto->latitude = $lat;
                        $ponto->longitude = $lng;
                        $ponto->tipo = $tipoAtual;
                        $ponto->store();
                    } else {
                        throw new Exception('Registro de entrada já existe para hoje.');
                    }
                }
            }

            TTransaction::close();

            // recarrega timeline e exibe mensagem
            new TMessage('info', 'Ponto registrado com sucesso!');
            $this->onReload();

        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
    }
}
