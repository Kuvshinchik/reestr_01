!function($) {
    "use strict";

    var Dashboard = function() {};

    Dashboard.prototype.createBarChart = function(element, data, xkey, ykeys, labels, lineColors) {
        if (typeof Morris !== 'undefined' && Morris.charts && Morris.charts[element]) {
            Morris.charts[element].destroy();
        }

        Morris.Bar({
            element: element,
             data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            gridLineColor: '#eef0f2',
            barSizeRatio: 0.4,
            resize: true,
            hideHover: 'auto',
            barColors: lineColors,
            xLabelAngle: 45
        });
    };

    // Получить текущие значения фильтров
    Dashboard.prototype.getFilters = function() {
        return {
            rdzv: $('#rdzv-filter').val() || null,
            vokzal: $('#vokzal-filter').val() || null
        };
    };

    Dashboard.prototype.loadRegionChart = function() {
        var self = this;
        var $container = $('#multi-line-chartZimaByRegion');
        if ($container.length === 0) return;

        var params = {};
        // Для диаграммы по регионам фильтр по вокзалу НЕ применим
        // Но можно фильтровать по rdzv, если нужно (редко)
        // Обычно оставляем без фильтра — показываем все регионы

        $container.html('<div class="text-center p-3">Загрузка...</div>');

        $.getJSON('/api/zima/by-region', params)
            .done(function(response) {
                if (!response || response.length === 0) {
                    $container.html('<div class="text-center p-3">Нет данных</div>');
                    return;
                }

                var chartData = response.map(function(item) {
                    return {
                        y: item.rdzv,
                        a: parseInt(item.total_plan) || 0,
                        b: parseInt(item.total_fact) || 0
                    };
                });

                $container.empty();
                self.createBarChart(
                    'multi-line-chartZimaByRegion',
                    chartData,
                    'y',
                    ['a', 'b'],
                    ['План', 'Факт'],
                    ['#5b6be8', '#40a4f1']
                );
            })
            .fail(function() {
                $container.html('<div class="text-center text-danger p-3">Ошибка</div>');
            });
    };

    Dashboard.prototype.loadWorkChart = function() {
        var self = this;
        var $container = $('#multi-line-chartZimaByWork');
        if ($container.length === 0) return;

        var filters = self.getFilters();
        var params = {};

        if (filters.rdzv) params.rdzv = filters.rdzv;
        if (filters.vokzal) params.vokzal = filters.vokzal;

        $container.html('<div class="text-center p-3">Загрузка...</div>');

        $.getJSON('/api/zima/by-work', params)
            .done(function(response) {
                if (!response || response.length === 0) {
                    $container.html('<div class="text-center p-3">Нет данных</div>');
                    return;
                }

                var chartData = response.map(function(item) {
                    return {
                        y: item.name_work,
                        a: parseInt(item.total_plan) || 0,
                        b: parseInt(item.total_fact) || 0
                    };
                });

                $container.empty();
                self.createBarChart(
                    'multi-line-chartZimaByWork',
                    chartData,
                    'y',
                    ['a', 'b'],
                    ['План', 'Факт'],
                    ['#5b6be8', '#40a4f1']
                );
            })
            .fail(function() {
                $container.html('<div class="text-center text-danger p-3">Ошибка</div>');
            });
    };

    // Загрузка списка вокзалов по выбранному РДЖВ
    Dashboard.prototype.loadVokzals = function(rdzv) {
        var $vokzalSelect = $('#vokzal-filter');
        $vokzalSelect.empty().prop('disabled', true);

        if (!rdzv || rdzv === '') {
            $vokzalSelect.append('<option value="">Все вокзалы</option>');
            return;
        }

        $vokzalSelect.append('<option value="">Загрузка...</option>');

        $.getJSON('/api/vokzals/by-rdzv', { rdzv: rdzv })
            .done(function(vokzals) {
                $vokzalSelect.empty().prop('disabled', false);
                $vokzalSelect.append('<option value="">Все вокзалы</option>');
                vokzals.forEach(function(vokzal) {
                    $vokzalSelect.append('<option value="' + vokzal + '">' + vokzal + '</option>');
                });
            })
            .fail(function() {
                $vokzalSelect.empty().prop('disabled', true);
                $vokzalSelect.append('<option value="">Ошибка загрузки</option>');
            });
    };

    Dashboard.prototype.reloadCharts = function() {
        this.loadRegionChart();
        this.loadWorkChart();
    };

    Dashboard.prototype.init = function() {
        var self = this;

        // Инициализация при загрузке
        self.loadRegionChart();
        self.loadWorkChart();

        // Слушатель изменения РДЖВ
        $(document).on('change', '#rdzv-filter', function() {
            var rdzv = $(this).val();
            self.loadVokzals(rdzv);         // Загружаем вокзалы
            self.reloadCharts();            // Перезагружаем диаграммы с новым фильтром
        });

        // Слушатель изменения вокзала
        $(document).on('change', '#vokzal-filter', function() {
            self.reloadCharts();            // Перезагружаем диаграммы с фильтром по вокзалу
        });
    };

    $.Dashboard = new Dashboard;
    $.Dashboard.Constructor = Dashboard;

}(window.jQuery);

// Запуск
(function($) {
    "use strict";
    $.Dashboard.init();
})(window.jQuery);