define(['orochart/js/app/components/multiline-chart-component'], function (MultiLineChartComponent) {
    'use strict';

    var graph;
    var Flotr = require('flotr2');
    var dataFormatter = require('orochart/js/data_formatter');

    Flotr.legend2 = null;
    Flotr.addPlugin('legend2', {
        options: {
            show: true,            // => setting to true will show the legend, hide otherwise
            noColumns: 1,          // => number of colums in legend table // @todo: doesn't work for HtmlText = false
            labelFormatter: function(v){return v;}, // => fn: string -> string
            onClickHandler: function (i) {},
            labelBoxBorderColor: '#CCCCCC', // => border color for the little label boxes
            labelBoxWidth: 14,
            labelBoxHeight: 10,
            labelBoxMargin: 5,
            container: null,       // => container (as jQuery object) to put legend in, null means default on top of graph
            position: 'nw',        // => position of default legend container within plot
            margin: 5,             // => distance from grid edge to default legend container within plot
            backgroundColor: '#F0F0F0', // => Legend background color.
            backgroundOpacity: 0.85,// => set to 0 to avoid background, set to 1 for a solid background
            rects: [],
        },
        callbacks: {
            'flotr:afterinit': function() {
                this.legend2.insertLegend();
            }
        },
        /**
         * Adds a legend div to the canvas container or draws it on the canvas.
         */
        insertLegend: function() {
            if(!this.options.legend2.show)
                return;

            var series      = this.series,
                plotOffset    = this.plotOffset,
                options       = this.options,
                legend        = options.legend2,
                fragments     = [],
                rowStarted    = false,
                ctx           = this.ctx,
                itemCount     = _.filter(series, function(s) {return (s.label);}).length,
                p             = legend.position,
                m             = legend.margin,
                opacity       = legend.backgroundOpacity,
                i, label, color;

            if (itemCount) {
                var lbw = legend.labelBoxWidth,
                    lbh = legend.labelBoxHeight,
                    lbm = legend.labelBoxMargin,
                    offsetX = plotOffset.left + m,
                    offsetY = plotOffset.top + m,
                    labelMaxWidth = 0,
                    style = {
                        size: options.fontSize*1.1
                    };

                // We calculate the labels' max width
                for(i = series.length - 1; i > -1; --i){
                    if(!series[i].label) continue;
                    label = legend.labelFormatter(series[i].label);
                    labelMaxWidth = Math.max(labelMaxWidth, this._text.measureText(label, style).width);
                }

                var legendWidth  = Math.round(lbw + lbm*3 + labelMaxWidth),
                    legendHeight = Math.round(itemCount*(lbm+lbh) + lbm);

                // Default Opacity
                if (!opacity && opacity !== 0) {
                    opacity = 0.1;
                }

                if (!options.HtmlText && this.textEnabled && !legend.container) {
                    ctx.canvas.style.zIndex = 1;
                    var func = (e) => { this.legend2.onClick(e, func)};
                    ctx.canvas.addEventListener('click', func, false);

                    if(p.charAt(0) === 's') offsetY = plotOffset.top + this.plotHeight - (m + legendHeight);
                    if(p.charAt(0) === 'c') offsetY = plotOffset.top + (this.plotHeight/2) - (m + (legendHeight/2));
                    if(p.charAt(1) === 'e') offsetX = plotOffset.left + this.plotWidth - (m + legendWidth);

                    // Legend box
                    color = this.processColor(legend.backgroundColor, { opacity : opacity });

                    ctx.fillStyle = color;
                    ctx.fillRect(offsetX, offsetY, legendWidth, legendHeight);
                    ctx.strokeStyle = legend.labelBoxBorderColor;
                    ctx.strokeRect(Flotr.toPixel(offsetX), Flotr.toPixel(offsetY), legendWidth, legendHeight);

                    // Legend labels
                    var x = offsetX + lbm;
                    var y = offsetY + lbm;
                    var rects = [];
                    for(i = 0; i < series.length; i++){
                        if(!series[i].label) continue;
                        label = legend.labelFormatter(series[i].label);

                        ctx.fillStyle = series[i].color;
                        ctx.fillRect(x, y, lbw-1, lbh-1);
                        rects[i] = {x: x, y: y, w: legendWidth - 2 * (lbm + 1), h: lbh};

                        ctx.strokeStyle = legend.labelBoxBorderColor;
                        ctx.lineWidth = 1;
                        ctx.strokeRect(Math.ceil(x)-1.5, Math.ceil(y)-1.5, lbw+2, lbh+2);

                        // Legend text
                        style.color = series[i].hide ? '#a8aaac' : options.grid.color;
                        Flotr.drawText(ctx, label, x + lbw + lbm, y + lbh, style);

                        y += lbh + lbm;
                    }

                    this.options.legend2.rects = rects;
                }
                else {
                   //TODO: doesn't work for HtmlText = true
                }
            }
        },

        collides: function (rects, x, y) {
            var isCollision = false;
            for (var i = 0, len = rects.length; i < len; i++) {
                if (typeof rects[i] === 'undefined') {
                    continue;
                }

                var left = rects[i].x, right = rects[i].x+rects[i].w;
                var top = rects[i].y, bottom = rects[i].y+rects[i].h;
                if (right >= x && left <= x && bottom >= y && top <= y) {
                    isCollision = i;
                }
            }
            return isCollision;
        },

        onClick: function (e, func) {
            var legend = this.options.legend2;
            var isCollision = this.legend2.collides(legend.rects, e.offsetX, e.offsetY);
            if (isCollision !== false) {
                legend.onClickHandler(isCollision, func, this);
            }
        }
    });

    graph = MultiLineChartComponent.extend({
        chart2Config: {
            el: null,
            data: [],
            options: {}
        },

        /**
         * Draw chart
         *
         * @overrides
         */
        draw: function () {
            var options = this.options;
            var $chart = this.$chart;
            var xFormat = options.data_schema.label.type;
            var yFormat = options.data_schema.value.type;
            var rawData = this.data;

            if (!$chart.get(0).clientWidth) {
                return;
            }

            if (dataFormatter.isValueNumerical(xFormat)) {
                var sort = function (rawData) {
                    rawData.sort(function (first, second) {
                        if (first.label === null || first.label === undefined) {
                            return -1;
                        }
                        if (second.label === null || second.label === undefined) {
                            return 1;
                        }
                        var firstLabel = dataFormatter.parseValue(first.label, xFormat);
                        var secondLabel = dataFormatter.parseValue(second.label, xFormat);
                        return firstLabel - secondLabel;
                    });
                };

                _.each(rawData, sort);
            }

            var connectDots = options.settings.connect_dots_with_line;
            var colors = this.config.default_settings.chartColors;

            var count = 0;
            var charts = [];

            var getXLabel = function (data) {
                var label = dataFormatter.formatValue(data, xFormat);
                if (label === null) {
                    var number = parseInt(data);
                    if (rawData.length > number) {
                        label = rawData[number].label === null ?
                            'N/A'
                            : rawData[number].label;
                    } else {
                        label = '';
                    }
                }
                return label;
            };
            var getYLabel = function (data) {
                var label = dataFormatter.formatValue(data, yFormat);
                if (label === null) {
                    var number = parseInt(data);
                    if (rawData.length > number) {
                        label = rawData[data].value === null ?
                            'N/A'
                            : rawData[data].value;
                    } else {
                        label = '';
                    }
                }
                return label;
            };

            var makeChart = function (rawData, count, key) {
                var chartData = [];

                for (var i in rawData) {
                    if (!rawData.hasOwnProperty(i)) {
                        continue;
                    }
                    var yValue = dataFormatter.parseValue(rawData[i].value, yFormat);
                    yValue = yValue === null ? parseInt(i) : yValue;
                    var xValue = dataFormatter.parseValue(rawData[i].label, xFormat);
                    xValue = xValue === null ? parseInt(i) : xValue;

                    var item = [xValue, yValue];
                    chartData.push(item);
                }

                return {
                    label: key,
                    data: chartData,
                    color: colors[count % colors.length],
                    markers: {
                        show: false
                    },
                    points: {
                        show: true
                    }
                };
            };

            _.each(rawData, function (rawData, key) {
                var result = makeChart(rawData, count, key);
                count++;

                charts.push(result);
            });

            this.chart2Config.data = charts;
            this.chart2Config.el = $chart.get(0);
            this.chart2Config.options = {
                colors: colors,
                title: ' ',
                fontColor: options.settings.chartFontColor,
                fontSize: options.settings.chartFontSize * (this.narrowScreen ? 0.8 : 1),
                lines: {
                    show: connectDots
                },
                mouse: {
                    track: true,
                    relative: true,
                    trackFormatter: function (pointData) {
                        return pointData.series.label +
                            ', ' + getXLabel(pointData.x) +
                            ': ' + getYLabel(pointData.y);
                    }
                },
                yaxis: {
                    autoscale: true,
                    autoscaleMargin: 1,
                    tickFormatter: function (y) {
                        return getYLabel(y);
                    },
                    title: options.data_schema.value.label + '  '
                },
                xaxis: {
                    autoscale: true,
                    autoscaleMargin: 0,
                    tickFormatter: function (x) {
                        return getXLabel(x);
                    },
                    title: this.narrowScreen ? ' ' : options.data_schema.label.label,
                    mode: options.xaxis.mode,
                    noTicks: options.xaxis.noTicks,
                    labelsAngle: this.narrowScreen ? 90 : 0,
                    margin: true
                },
                HtmlText: false,
                grid: {
                    verticalLines: false
                },
                legend2: {
                    show: true,
                    noColumns: 0,
                    position: 'sw',
                    onClickHandler: function (index, func, plot) {
                        var data = plot.data;
                        plot.ctx.canvas.removeEventListener('click', func, false);
                        data[index].hide = !data[index].hide;
                        Flotr.draw(plot.el, data, plot.options);
                    }
                },
                legend: {
                    show: false
                }
            };

            Flotr.draw(this.chart2Config.el, this.chart2Config.data, this.chart2Config.options);
        },
    });

    return graph;
});
