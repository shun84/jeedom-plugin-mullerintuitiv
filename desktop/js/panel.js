if (jeeFrontEnd.jeedomVersion >= '4.4.0'){
    jeedomUtils.datePickerInit()
} else {
    $(".in_datepicker").datepicker()
}

document.getElementById('bt_validChangeDate').addEventListener('click', function () {
    jeedom.history.chart = []
    displayMullerintuitiv(
        object_id,
        document.getElementById('in_startDate').value,
        document.getElementById('in_endDate').value
    )
})

displayMullerintuitiv(object_id, '', '')

function displayMullerintuitiv(object_id,_dateStart,_dateEnd) {
    $.ajax({
        type: 'POST',
        url: 'plugins/mullerintuitiv/core/ajax/mullerintuitiv.ajax.php',
        data: {
            action: 'getMullerintuitiv',
            object_id: object_id,
            version: 'dashboard',
            dateStart : _dateStart,
            dateEnd : _dateEnd,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error)
        },
        success: function (data) {
            if (data.state !== 'ok') {
                $.fn.showAlert({message: data.result, level: 'danger'})
            }
            let icon = '';
            if (isset(data.result.object.display) && isset(data.result.object.display.icon)) {
                icon = data.result.object.display.icon
            }
            document.getElementById('mullerintuitivname').innerHTML = icon + ' ' + data.result.object.name

            let dayseries = []
            let weekseries = []
            let monthseries = []
            for (let i in data.result.eqLogics) {
                let logicalid = data.result.eqLogics[i].eqLogic.logicalId
                let regex = /home/
                document.getElementById('mullerintuitivequipement').innerHTML = data.result.eqLogics[i].html
                if (logicalid.match(regex)){
                    dayseries.push({
                        name: data.result.eqLogics[i].eqLogic.name,
                        data: data.result.eqLogics[i].gethomemeasure.day
                    });
                    weekseries.push({
                        name: data.result.eqLogics[i].eqLogic.name,
                        data: data.result.eqLogics[i].gethomemeasure.week
                    });
                    monthseries.push({
                        name: data.result.eqLogics[i].eqLogic.name,
                        data: data.result.eqLogics[i].gethomemeasure.month
                    });
                } else {
                    dayseries.push({
                        name: data.result.eqLogics[i].eqLogic.name,
                        data: data.result.eqLogics[i].getroommeasure.day
                    });
                    weekseries.push({
                        name: data.result.eqLogics[i].eqLogic.name,
                        data: data.result.eqLogics[i].getroommeasure.week
                    });
                    monthseries.push({
                        name: data.result.eqLogics[i].eqLogic.name,
                        data: data.result.eqLogics[i].getroommeasure.month
                    });
                }

                drawSimpleGraph('mullerintuitivday', dayseries, 'jours')
                drawSimpleGraph('mullerintuitivweek', weekseries, 'semaines')
                drawSimpleGraph('mullerintuitivmonth', monthseries, 'mois')
            }
        }
    });
}

function drawSimpleGraph(_el, _serie, _type) {
    new Highcharts.stockChart({
        chart: {
            renderTo: _el,
            type: 'column'
        },

        title: {
            text: 'Consommation par ' + _type
        },

        tooltip: {
            pointFormat: '{point.y} {{kwh}}',
            valueDecimals: 2
        },

        scrollbar: {
            enabled: false
        },

        navigator: {
            enabled: false
        },

        rangeSelector: {
            enabled: false
        },

        plotOptions: {
            series: {
                marker: {
                    states: {
                        hover: {
                            fillColor: '#da6100'
                        }
                    }
                },
                events: {
                    mouseOver: function() {
                        originalColor = this.color

                        this.update({
                            color: '#da6100'
                        })
                    },
                    mouseOut: function() {
                        this.update({
                            color: originalColor
                        })
                    }
                }
            }
        },

        series: _serie
    })
}