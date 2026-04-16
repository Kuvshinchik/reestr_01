/*
 Template Name: Annex - Bootstrap 4 Admin Dashboard
 Author: Mannatthemes
 Website: www.mannatthemes.com
 File: Morris init js (modified for РДЖВ Bar Chart)
 */

!function($) {
    "use strict";

    var Dashboard = function() {};

    // Функция создания столбчатой диаграммы (Bar Chart)
    Dashboard.prototype.createBarChart = function(element, data, xkey, ykeys, labels, lineColors) {
        Morris.Bar({
            element: element,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            gridLineColor: '#eef0f2',
            barSizeRatio: 0.4, // Ширина столбцов
            resize: true,
            hideHover: 'auto',
            barColors: lineColors,
            xLabelAngle: 45 // Наклон подписей, так как названия РДЖВ могут быть длинными
        });
    },

    Dashboard.prototype.init = function() {

        // ID элемента из вашего blade-файла
        var chartElementId = 'multi-line-chartZimaByRegion'; 
        var $chartContainer = $('#' + chartElementId);

        if ($chartContainer.length > 0) {
            
            // Дата для фильтрации (можно менять динамически)
            //var reportDate = '2025-11-01'; 

            // Запрос к Laravel контроллеру
            //$.getJSON('/dashboard/itog-dzhv', { date: reportDate })
			
			$.getJSON('/api/zima/by-region')  //$.getJSON('/dashboard/itog-dzhv')

                .done(function(response) {
                    
                    // Если данных нет
                    if (!response || response.length === 0) {
                        $chartContainer.html('<div class="text-center p-3">Нет данных для отображения</div>');
                        return;
                    }

                    // Подготовка данных для Morris.js
                    // response приходит в виде: [{ rdzv: "МОСК", total_plan: 100, total_fact: 90 }, ...]
                    var chartData = response.map(function(item) {
                        return {
                            y: item.rdzv,      // Ось X: Название РДЖВ
                            a: parseInt(item.total_plan), // План
                            b: parseInt(item.total_fact)  // Факт
                        };
                    });

                    // Очищаем контейнер перед отрисовкой (на случай перезагрузки)
                    $chartContainer.empty();

                    // Инициализация графика
                    // Параметры: ID, Данные, X-ключ, Y-ключи, Подписи легенды, Цвета
                    $.Dashboard.createBarChart(
                        chartElementId,
                        chartData,
                        'y', 
                        ['a', 'b'], 
                        ['План', 'Факт'], 
                        ['#5b6be8', '#40a4f1'] // Цвета: Факт (синий), План (голубой)
                    );

                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Ошибка загрузки данных графика: " + textStatus);
                    $chartContainer.html('<div class="text-center text-danger p-3">Ошибка загрузки данных</div>');
                });
        }
    },
    
    // Инициализация плагина
    $.Dashboard = new Dashboard, $.Dashboard.Constructor = Dashboard

}(window.jQuery),

// Запуск при загрузке страницы
function($) {
    "use strict";
    $.Dashboard.init();
}(window.jQuery);