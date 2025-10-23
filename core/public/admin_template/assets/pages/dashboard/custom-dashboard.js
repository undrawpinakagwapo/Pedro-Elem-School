// 'use strict';
// $(document).ready(function() {
//     // sale analytics start
   
//     var ctx = document.getElementById('this-month').getContext("2d");
//     var myChart = new Chart(ctx, {
//         type: 'bar',
//         data: avgvalchart('#11c15b', [30, 15, 25, 35, 30, 20, 25, 30, 15, 1], '#11c15b'),
//         options: buildchartoption(),
//     });
//     function avgvalchart(a, b, f) {
//         if (f == null) {
//             f = "rgba(0,0,0,0)";
//         }
//         return {
//             labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October"],
//             datasets: [{
//                 label: "",
//                 borderColor: a,
//                 borderWidth: 2,
//                 hitRadius: 30,
//                 pointHoverRadius: 4,
//                 pointBorderWidth: 50,
//                 pointHoverBorderWidth: 12,
//                 pointBackgroundColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
//                 pointBorderColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
//                 pointHoverBackgroundColor: a,
//                 pointHoverBorderColor: Chart.helpers.color("#000000").alpha(.1).rgbString(),
//                 fill: true,
//                 backgroundColor: f,
//                 data: b,
//             }]
//         };
//     }
//     function buildchartoption() {
//         return {
//             title: {
//                 display: !1
//             },
//             tooltips: {
//                 position: 'nearest',
//                 mode: 'index',
//                 intersect: false,
//                 yPadding: 10,
//                 xPadding: 10,
//             },
//             legend: {
//                 display: !1,
//                 labels: {
//                     usePointStyle: !1
//                 }
//             },
//             responsive: !0,
//             maintainAspectRatio: !0,
//             hover: {
//                 mode: "index"
//             },
//             scales: {
//                 xAxes: [{
//                     display: !1,
//                     gridLines: !1,
//                     scaleLabel: {
//                         display: !0,
//                         labelString: "Month"
//                     }
//                 }],
//                 yAxes: [{
//                     display: !1,
//                     gridLines: !1,
//                     scaleLabel: {
//                         display: !0,
//                         labelString: "Value"
//                     },
//                     ticks: {
//                         beginAtZero: !0
//                     }
//                 }]
//             },
//             elements: {
//                 point: {
//                     radius: 4,
//                     borderWidth: 12
//                 }
//             },
//             layout: {
//                 padding: {
//                     left: 0,
//                     right: 0,
//                     top: 0,
//                     bottom: 0
//                 }
//             }
//         };
//     }
//     // sale analytics end


// });
 