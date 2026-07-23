/**
 * Liquid Glass Theme Charts
 * Uses ApexCharts
 */

const glassThemeColors = ['#8b5cf6', '#c4b5fd', '#6d28d9', '#4a148c', '#a78bfa'];

const commonChartOptions = {
    chart: {
        background: 'transparent',
        toolbar: {
            show: false
        }
    },
    theme: {
        mode: 'dark',
        palette: 'palette1'
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    dataLabels: {
        enabled: false
    },
    grid: {
        borderColor: 'rgba(255, 255, 255, 0.1)',
        strokeDashArray: 4,
    },
    xaxis: {
        labels: {
            style: {
                colors: '#ffffff',
                fontSize: '12px'
            }
        },
        axisBorder: {
            show: false
        },
        axisTicks: {
            show: false
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: '#ffffff',
                fontSize: '12px'
            }
        }
    },
    tooltip: {
        theme: 'dark',
        style: {
            fontSize: '12px'
        },
        x: {
            show: true
        },
        marker: {
            show: true
        }
    },
    colors: glassThemeColors
};

// Initialize Charts when DOM is ready
document.addEventListener('DOMContentLoaded', function () {

    // 1. Monthly Sales Bar Chart
    const salesChartEl = document.querySelector('#glass_sales_chart');
    if (salesChartEl && typeof ApexCharts !== 'undefined') {
        const salesOptions = {
            ...commonChartOptions,
            series: [{
                name: 'Sales',
                data: [44, 55, 57, 56, 61, 58, 63, 60, 66, 75, 78, 85]
            }],
            chart: {
                type: 'bar',
                height: 350,
                parentHeightOffset: 0,
                background: 'transparent',
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded',
                    borderRadius: 4
                },
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                ...commonChartOptions.xaxis,
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: "vertical",
                    shadeIntensity: 0.5,
                    gradientToColors: ['#c4b5fd'], // optional, if not defined - uses the shades of same color in series
                    inverseColors: true,
                    opacityFrom: 0.8,
                    opacityTo: 1,
                    stops: [0, 100],
                    colorStops: []
                }
            }
        };
        const salesChart = new ApexCharts(salesChartEl, salesOptions);
        salesChart.render();
    }

    // 2. User Distribution Pie Chart
    const userChartEl = document.querySelector('#glass_user_chart');
    if (userChartEl && typeof ApexCharts !== 'undefined') {
        const userOptions = {
            series: [44, 55, 13, 43],
            chart: {
                width: 380,
                type: 'pie',
                background: 'transparent'
            },
            labels: ['Active', 'Inactive', 'Pending', 'Banned'],
            colors: glassThemeColors,
            legend: {
                position: 'bottom',
                labels: {
                    colors: '#ffffff'
                }
            },
            stroke: {
                colors: ['rgba(255, 255, 255, 0.1)']
            },
            dataLabels: {
                style: {
                    colors: ['#ffffff']
                }
            },
            tooltip: {
                theme: 'dark'
            }
        };
        const userChart = new ApexCharts(userChartEl, userOptions);
        userChart.render();
    }
});
